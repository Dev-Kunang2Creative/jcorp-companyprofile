<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Asap: seluruh halaman panel tetap terbuka setelah pindah tema.
 *
 * Ditulis saat panel dipindahkan ke arah A+B (23 Agustus 2026). Test
 * panel yang sudah ada menguji PERILAKU — siapa boleh mengubah apa —
 * dan tidak satu pun akan gagal kalau sebuah halaman rusak karena
 * kesalahan tampilan.
 *
 * Yang dijaga di sini sekadar: halamannya masih bisa dibuka.
 */
class TemaPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_panel_page_still_opens(): void
    {
        $business = Business::factory()->withPortfolio()->create();

        // Admin anak usaha untuk halaman yang butuh konteks satu unit;
        // super-admin tidak terikat ke unit mana pun, jadi halaman
        // katalog/portfolio/profil mengembalikan 404 untuknya.
        $admin = User::factory()->forBusiness($business)->create();

        foreach ([
            'panel.dashboard',
            'panel.catalog.index',
            'panel.portfolio.index',
            'panel.profile.edit',
        ] as $nama) {
            $this->actingAs($admin)
                ->get(route($nama))
                ->assertOk();
        }

        // Dua halaman ini khusus super-admin.
        $super = User::factory()->superAdmin()->create();

        foreach (['panel.users.index', 'panel.businesses.index'] as $nama) {
            $this->actingAs($super)
                ->get(route($nama))
                ->assertOk();
        }
    }

    public function test_every_auth_page_still_opens(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('password.request'))->assertOk();
    }
}
