<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Pembatasan menu Kelola Akun (spec §6 — super-admin saja).
 *
 * Menyembunyikan menu di sidebar tidak dianggap pengamanan; alamatnya masih
 * bisa dikirimi permintaan langsung. Test di sini menembak route-nya.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function businessAdmin(): User
    {
        return User::factory()->forBusiness(Business::factory()->create())->create();
    }

    // -------------------------------------------------- pembatasan peran

    public function test_a_business_admin_cannot_view_the_account_list(): void
    {
        $this->actingAs($this->businessAdmin())
            ->get(route('panel.users.index'))
            ->assertForbidden();
    }

    public function test_a_business_admin_cannot_invite_anyone(): void
    {
        Business::factory()->create(['slug' => 'target']);

        $this->actingAs($this->businessAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Selundupan',
                'email' => 'selundupan@jcorp.test',
                'role' => 'super_admin',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'selundupan@jcorp.test']);
    }

    public function test_a_business_admin_cannot_disable_other_accounts(): void
    {
        $korban = User::factory()->create();

        $this->actingAs($this->businessAdmin())
            ->put(route('panel.users.active', $korban), ['is_active' => false])
            ->assertForbidden();

        $this->assertTrue($korban->refresh()->is_active);
    }

    public function test_a_business_admin_cannot_promote_themselves(): void
    {
        // Kalau ini lolos, seorang admin bisa menaikkan dirinya jadi
        // super-admin — kegagalan paling parah yang mungkin terjadi.
        $admin = $this->businessAdmin();

        $this->actingAs($admin)
            ->post(route('panel.users.store'), [
                'name' => $admin->name,
                'email' => 'naik-pangkat@jcorp.test',
                'role' => 'super_admin',
            ])
            ->assertForbidden();

        $this->assertFalse($admin->refresh()->isSuperAdmin());
    }

    // ------------------------------------------------------- super-admin

    public function test_a_super_admin_can_view_the_account_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->create();

        $this->actingAs($superAdmin)
            ->get(route('panel.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('panel/users/index')
                ->has('users', 2)
                ->has('availableBusinesses')
            );
    }

    public function test_a_super_admin_can_disable_an_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target = User::factory()->create();

        $this->actingAs($superAdmin)
            ->put(route('panel.users.active', $target), ['is_active' => false])
            ->assertSessionHasNoErrors();

        $this->assertFalse($target->refresh()->is_active);
    }

    public function test_a_super_admin_cannot_disable_their_own_account(): void
    {
        // Kalau ini lolos, satu-satunya super-admin bisa mengunci diri
        // keluar dan tidak ada yang bisa mengaktifkannya lagi selain lewat
        // SSH — sistem praktis terkunci.
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->put(route('panel.users.active', $superAdmin), ['is_active' => false])
            ->assertStatus(422);

        $this->assertTrue($superAdmin->refresh()->is_active);
    }

    public function test_a_super_admin_can_re_enable_an_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target = User::factory()->inactive()->create();

        $this->actingAs($superAdmin)
            ->put(route('panel.users.active', $target), ['is_active' => true])
            ->assertSessionHasNoErrors();

        $this->assertTrue($target->refresh()->is_active);
    }

    // ---------------------------------------------------- daftar pilihan

    public function test_only_businesses_without_an_active_admin_are_offered(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $sudahAda = Business::factory()->create(['name' => 'Sudah Ada Admin']);
        User::factory()->forBusiness($sudahAda)->create();

        Business::factory()->create(['name' => 'Belum Ada Admin']);

        $adminNonaktif = Business::factory()->create(['name' => 'Adminnya Nonaktif']);
        User::factory()->forBusiness($adminNonaktif)->inactive()->create();

        $this->actingAs($superAdmin)
            ->get(route('panel.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                // Yang adminnya nonaktif ikut ditawarkan — itu yang membuat
                // penggantian admin mungkin.
                ->has('availableBusinesses', 2)
            );
    }

    public function test_the_account_list_marks_the_current_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'Saya']);

        $this->actingAs($superAdmin)
            ->get(route('panel.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.0.is_self', true)
                ->where('users.0.status', 'aktif')
            );
    }

    public function test_the_account_list_shows_invitation_status(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'AAA Super']);
        User::factory()->invited()->create(['name' => 'BBB Diundang']);
        User::factory()->invitationExpired()->create(['name' => 'CCC Kedaluwarsa']);
        User::factory()->inactive()->create(['name' => 'DDD Nonaktif']);

        $this->actingAs($superAdmin)
            ->get(route('panel.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.0.status', 'aktif')
                ->where('users.1.status', 'menunggu_aktivasi')
                ->where('users.2.status', 'undangan_kedaluwarsa')
                ->where('users.3.status', 'nonaktif')
            );
    }

    public function test_the_invitation_token_is_never_sent_to_the_browser(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'AAA']);
        User::factory()->invited()->create(['name' => 'BBB']);

        $this->actingAs($superAdmin)
            ->get(route('panel.users.index'))
            ->assertInertia(fn (Assert $page) => $page->missing('users.1.invitation_token'));
    }
}
