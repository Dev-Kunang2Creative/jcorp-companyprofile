<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Undangan admin baru lewat tautan.
 *
 * Yang dijaga: tautan hanya bisa dipakai sekali, kedaluwarsa setelah 7 hari,
 * dan token yang tidak dikenal tidak membocorkan apa pun.
 *
 * Ditulis dari sisi penyerang untuk bagian tokennya — sama seperti test
 * kepemilikan di Fase 1.
 */
class UserInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    // ------------------------------------------------------- membuat undangan

    public function test_a_super_admin_can_invite_a_business_admin(): void
    {
        $business = Business::factory()->create(['slug' => 'sweetness-things']);

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Admin Sweetness',
                'email' => 'sweetness@jcorp.test',
                'role' => 'business_admin',
                'business' => 'sweetness-things',
            ])
            ->assertSessionHasNoErrors()
            ->assertInertiaFlash('invitation.name', 'Admin Sweetness')
            ->assertInertiaFlash('invitation.url')
            ->assertInertiaFlash('invitation.expires_in_days', User::INVITATION_VALID_DAYS);

        $user = User::where('email', 'sweetness@jcorp.test')->firstOrFail();

        // Akun dibuat TANPA password — itu inti mekanismenya.
        $this->assertNull($user->password);
        $this->assertTrue($user->isPendingInvitation());
        $this->assertSame($business->id, $user->business_id);
        $this->assertNotNull($user->invitation_token);
    }

    public function test_the_invitation_token_is_stored_hashed(): void
    {
        // Kalau database bocor, token yang bisa dipakai tidak boleh ikut.
        $business = Business::factory()->create(['slug' => 'sweetness-things']);

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Admin Sweetness',
                'email' => 'sweetness@jcorp.test',
                'role' => 'business_admin',
                'business' => 'sweetness-things',
            ]);

        $tersimpan = User::where('email', 'sweetness@jcorp.test')->value('invitation_token');

        // Panjang hash sha256 selalu 64 karakter heksadesimal.
        $this->assertSame(64, strlen($tersimpan));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $tersimpan);
    }

    public function test_a_business_cannot_have_two_active_admins(): void
    {
        $business = Business::factory()->create(['slug' => 'sweetness-things']);
        User::factory()->forBusiness($business)->create();

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Admin Kedua',
                'email' => 'kedua@jcorp.test',
                'role' => 'business_admin',
                'business' => 'sweetness-things',
            ])
            ->assertSessionHasErrors('business');

        $this->assertDatabaseMissing('users', ['email' => 'kedua@jcorp.test']);
    }

    public function test_a_business_whose_admin_is_disabled_can_be_invited_again(): void
    {
        // Inilah yang membuat penggantian admin mungkin tanpa menghapus akun.
        $business = Business::factory()->create(['slug' => 'sweetness-things']);
        User::factory()->forBusiness($business)->inactive()->create();

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Admin Pengganti',
                'email' => 'pengganti@jcorp.test',
                'role' => 'business_admin',
                'business' => 'sweetness-things',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'pengganti@jcorp.test']);
    }

    public function test_a_super_admin_invitation_may_not_carry_a_business(): void
    {
        Business::factory()->create(['slug' => 'sweetness-things']);

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Salah Konfigurasi',
                'email' => 'salah@jcorp.test',
                'role' => 'super_admin',
                'business' => 'sweetness-things',
            ])
            ->assertSessionHasErrors('business');
    }

    public function test_a_business_admin_invitation_requires_a_business(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.store'), [
                'name' => 'Tanpa Anak Usaha',
                'email' => 'tanpa@jcorp.test',
                'role' => 'business_admin',
            ])
            ->assertSessionHasErrors('business');
    }

    // ------------------------------------------------------ membuka undangan

    public function test_a_valid_token_opens_the_activation_page(): void
    {
        $business = Business::factory()->create(['name' => 'Sweetness Things']);
        User::factory()->forBusiness($business)->invited('token-benar')->create([
            'name' => 'Admin Sweetness',
        ]);

        $this->get(route('panel.invitation.show', ['token' => 'token-benar']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/invitation')
                ->where('name', 'Admin Sweetness')
                ->where('businessName', 'Sweetness Things')
            );
    }

    public function test_an_unknown_token_returns_404(): void
    {
        $this->get(route('panel.invitation.show', ['token' => 'token-karangan']))
            ->assertNotFound();
    }

    public function test_an_expired_token_returns_404(): void
    {
        User::factory()->invitationExpired('token-basi')->create();

        $this->get(route('panel.invitation.show', ['token' => 'token-basi']))
            ->assertNotFound();
    }

    public function test_setting_a_password_activates_the_account(): void
    {
        $user = User::factory()->invited('token-benar')->create();

        $this->post(route('panel.invitation.store', ['token' => 'token-benar']), [
            'password' => 'PasswordKuat#2026',
            'password_confirmation' => 'PasswordKuat#2026',
        ])->assertRedirect(route('panel.dashboard'));

        $user->refresh();

        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check('PasswordKuat#2026', $user->password));
        $this->assertFalse($user->isPendingInvitation());
        $this->assertNull($user->invitation_token);

        // Langsung masuk — orangnya baru saja membuktikan menguasai tautan
        // DAN menetapkan passwordnya sendiri.
        $this->assertAuthenticatedAs($user);
    }

    public function test_the_new_password_actually_works_for_logging_in(): void
    {
        $user = User::factory()->invited('token-benar')->create();

        $this->post(route('panel.invitation.store', ['token' => 'token-benar']), [
            'password' => 'PasswordKuat#2026',
            'password_confirmation' => 'PasswordKuat#2026',
        ]);

        $this->post(route('logout'));
        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'PasswordKuat#2026',
        ]);

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_a_token_can_only_be_used_once(): void
    {
        // Tautan yang tercecer di riwayat WhatsApp tidak boleh berguna lagi.
        User::factory()->invited('token-benar')->create();

        $this->post(route('panel.invitation.store', ['token' => 'token-benar']), [
            'password' => 'PasswordKuat#2026',
            'password_confirmation' => 'PasswordKuat#2026',
        ]);

        $this->post(route('logout'));

        $this->get(route('panel.invitation.show', ['token' => 'token-benar']))
            ->assertNotFound();
    }

    public function test_the_password_must_be_confirmed(): void
    {
        User::factory()->invited('token-benar')->create();

        $this->from(route('panel.invitation.show', ['token' => 'token-benar']))
            ->post(route('panel.invitation.store', ['token' => 'token-benar']), [
                'password' => 'PasswordKuat#2026',
                'password_confirmation' => 'PasswordLain#2026',
            ])
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    // ------------------------------------------------------- kirim ulang

    public function test_a_super_admin_can_reissue_an_expired_invitation(): void
    {
        $user = User::factory()->invitationExpired('token-basi')->create();
        $tokenLama = $user->invitation_token;

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.resend', $user))
            ->assertSessionHasNoErrors()
            ->assertInertiaFlash('invitation.name', $user->name)
            ->assertInertiaFlash('invitation.url')
            ->assertInertiaFlash('invitation.expires_in_days', User::INVITATION_VALID_DAYS);

        $user->refresh();

        $this->assertNotSame($tokenLama, $user->invitation_token);
        $this->assertFalse($user->invitationIsExpired());

        // Tautan lama harus mati.
        $this->get(route('panel.invitation.show', ['token' => 'token-basi']))
            ->assertNotFound();
    }

    public function test_an_active_account_cannot_be_reissued_an_invitation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->superAdmin())
            ->post(route('panel.users.resend', $user))
            ->assertStatus(422);
    }
}
