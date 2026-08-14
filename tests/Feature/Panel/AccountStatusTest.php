<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Akun nonaktif dan akun yang undangannya belum dibuka.
 *
 * INI BAGIAN KEAMANAN INTINYA.
 *
 * Kalau status hanya diperiksa saat login, admin yang aksesnya baru dicabut
 * masih bisa mengubah dan menghapus data sampai sesinya kedaluwarsa — dua jam
 * berikutnya. Padahal alasan mencabut akses biasanya justru mendesak.
 *
 * Test di sini ditulis dari sisi penyerang: akun dinonaktifkan SAAT sedang
 * login, lalu dicoba tetap bekerja.
 */
class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------- nonaktif saat login

    public function test_a_disabled_account_is_kicked_out_immediately(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        // Sudah masuk dan sedang bekerja.
        $this->actingAs($admin)->get(route('panel.dashboard'))->assertOk();

        // Super-admin mencabut aksesnya.
        $admin->is_active = false;
        $admin->save();

        // Permintaan BERIKUTNYA langsung ditolak — bukan menunggu sesi habis.
        $this->actingAs($admin)
            ->get(route('panel.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_disabled_account_cannot_change_data_anymore(): void
    {
        // Yang paling berbahaya: mengubah atau menghapus setelah dicabut.
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();
        $item = CatalogItem::factory()->for($business)->create(['name' => 'Asli']);

        $admin->is_active = false;
        $admin->save();

        $this->actingAs($admin)
            ->put(route('panel.catalog.update', $item), [
                'name' => 'Diubah setelah dicabut',
                'is_available' => true,
                'sort_order' => 0,
            ])
            ->assertRedirect(route('login'));

        $this->assertSame('Asli', $item->refresh()->name);
    }

    public function test_a_disabled_account_cannot_delete_data(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();
        $item = CatalogItem::factory()->for($business)->create();

        $admin->is_active = false;
        $admin->save();

        $this->actingAs($admin)
            ->delete(route('panel.catalog.destroy', $item))
            ->assertRedirect(route('login'));

        $this->assertNotSoftDeleted($item);
    }

    public function test_a_disabled_account_cannot_log_in(): void
    {
        $admin = User::factory()->inactive()->create();

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_a_reactivated_account_can_log_in_again(): void
    {
        // Nonaktif tidak menghapus apa pun — passwordnya masih yang lama.
        $admin = User::factory()->inactive()->create();

        $admin->is_active = true;
        $admin->save();

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin->fresh());
    }

    // --------------------------------------------- undangan belum dibuka

    public function test_a_pending_invitation_cannot_log_in(): void
    {
        // Akun ada, tapi passwordnya belum dibuat.
        $user = User::factory()->invited()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_a_pending_invitation_cannot_reach_the_panel(): void
    {
        // Lapisan kedua: seandainya sesi terbentuk lewat jalan lain,
        // middleware tetap menolaknya.
        $user = User::factory()->invited()->create();

        $this->actingAs($user)
            ->get(route('panel.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_an_active_account_is_not_disturbed(): void
    {
        // Penjagaan di atas tidak boleh mengganggu pemakaian normal.
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)->get(route('panel.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('panel.catalog.index'))->assertOk();
        $this->actingAs($admin)->get(route('panel.profile.edit'))->assertOk();
    }
}
