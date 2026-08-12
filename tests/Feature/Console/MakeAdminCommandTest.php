<?php

namespace Tests\Feature\Console;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Spec §4: satu admin memegang tepat satu anak usaha.
 */
class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_business_admin(): void
    {
        $business = Business::factory()->create(['slug' => 'sweetness-things']);

        $this->artisan('jcorp:make-admin', [
            '--name' => 'Admin Sweetness',
            '--email' => 'sweetness@jcorp.test',
            '--role' => 'business_admin',
            '--business' => 'sweetness-things',
        ])
            ->expectsQuestion('Password', 'rahasia-sekali-123')
            ->assertSuccessful();

        $user = User::where('email', 'sweetness@jcorp.test')->firstOrFail();

        $this->assertSame(UserRole::BusinessAdmin, $user->role);
        $this->assertSame($business->id, $user->business_id);
        $this->assertTrue(Hash::check('rahasia-sekali-123', $user->password));
    }

    public function test_it_creates_a_super_admin_without_a_business(): void
    {
        $this->artisan('jcorp:make-admin', [
            '--name' => 'Pemilik',
            '--email' => 'owner@jcorp.test',
            '--role' => 'super_admin',
        ])
            ->expectsQuestion('Password', 'rahasia-sekali-123')
            ->assertSuccessful();

        $user = User::where('email', 'owner@jcorp.test')->firstOrFail();

        $this->assertSame(UserRole::SuperAdmin, $user->role);
        $this->assertNull($user->business_id);
    }

    public function test_a_super_admin_may_not_be_tied_to_a_business(): void
    {
        Business::factory()->create(['slug' => 'sweetness-things']);

        $this->artisan('jcorp:make-admin', [
            '--name' => 'Salah Konfigurasi',
            '--email' => 'salah@jcorp.test',
            '--role' => 'super_admin',
            '--business' => 'sweetness-things',
        ])
            ->expectsQuestion('Password', 'rahasia-sekali-123')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'salah@jcorp.test']);
    }

    public function test_one_business_may_only_have_one_admin(): void
    {
        $business = Business::factory()->create(['slug' => 'sweetness-things']);
        User::factory()->forBusiness($business)->create();

        $this->artisan('jcorp:make-admin', [
            '--name' => 'Admin Kedua',
            '--email' => 'kedua@jcorp.test',
            '--role' => 'business_admin',
            '--business' => 'sweetness-things',
        ])
            ->expectsQuestion('Password', 'rahasia-sekali-123')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'kedua@jcorp.test']);
    }

    public function test_it_rejects_an_unknown_business_slug(): void
    {
        $this->artisan('jcorp:make-admin', [
            '--name' => 'Admin Hantu',
            '--email' => 'hantu@jcorp.test',
            '--role' => 'business_admin',
            '--business' => 'tidak-ada',
        ])
            ->expectsQuestion('Password', 'rahasia-sekali-123')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'hantu@jcorp.test']);
    }

    public function test_it_rejects_a_duplicate_email(): void
    {
        $business = Business::factory()->create(['slug' => 'sweetness-things']);
        User::factory()->create(['email' => 'kembar@jcorp.test']);

        $this->artisan('jcorp:make-admin', [
            '--name' => 'Email Kembar',
            '--email' => 'kembar@jcorp.test',
            '--role' => 'business_admin',
            '--business' => $business->slug,
        ])
            ->expectsQuestion('Password', 'rahasia-sekali-123')
            ->assertFailed();

        $this->assertSame(1, User::where('email', 'kembar@jcorp.test')->count());
    }

    public function test_the_password_is_never_accepted_as_a_command_line_argument(): void
    {
        // Argumen perintah tersimpan di riwayat shell dan terlihat di daftar
        // proses — password hanya boleh lewat prompt tersembunyi.
        $definition = $this->app->make(Kernel::class)
            ->all()['jcorp:make-admin']
            ->getDefinition();

        $this->assertFalse($definition->hasOption('password'));
    }
}
