<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pembatasan akses panel (spec §6 dan §11).
 */
class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function panelRoutes(): array
    {
        return [
            'dashboard' => ['panel.dashboard'],
            'katalog' => ['panel.catalog.index'],
            'portfolio' => ['panel.portfolio.index'],
            'info kontak' => ['panel.profile.edit'],
            'kelola akun' => ['panel.users.index'],
            'kelola anak usaha' => ['panel.businesses.index'],
        ];
    }

    #[DataProvider('panelRoutes')]
    public function test_guests_are_redirected_to_login(string $routeName): void
    {
        $this->get(route($routeName))->assertRedirect(route('login'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function superAdminOnlyRoutes(): array
    {
        return [
            'kelola akun' => ['panel.users.index'],
            'kelola anak usaha' => ['panel.businesses.index'],
        ];
    }

    #[DataProvider('superAdminOnlyRoutes')]
    public function test_business_admin_is_refused_on_super_admin_routes(string $routeName): void
    {
        $admin = User::factory()->forBusiness(Business::factory()->create())->create();

        $this->actingAs($admin)->get(route($routeName))->assertForbidden();
    }

    #[DataProvider('superAdminOnlyRoutes')]
    public function test_super_admin_can_reach_super_admin_routes(string $routeName): void
    {
        Business::factory()->parent()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get(route($routeName))->assertOk();
    }

    public function test_the_portfolio_page_is_refused_when_the_switch_is_off(): void
    {
        // Menyembunyikan menu di sidebar TIDAK dianggap pengamanan — alamatnya
        // masih bisa diketik langsung, jadi penolakannya di sisi server.
        $business = Business::factory()->create(['has_portfolio' => false]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->get(route('panel.portfolio.index'))
            ->assertNotFound();
    }

    public function test_uploading_a_photo_is_refused_when_the_switch_is_off(): void
    {
        Storage::fake('public');

        $business = Business::factory()->create(['has_portfolio' => false]);
        $admin = User::factory()->forBusiness($business)->create();

        // Fotonya sengaja disertakan supaya lolos validasi — kalau tidak,
        // yang terbukti cuma "form ditolak validasi", bukan "sakelar mati
        // menolak unggahan".
        $this->actingAs($admin)
            ->post(route('panel.portfolio.store'), [
                'caption' => 'Selundupan',
                'sort_order' => 0,
                'image' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertNotFound();

        $this->assertSame(0, $business->portfolioItems()->count());
    }

    public function test_business_admin_cannot_toggle_the_portfolio_switch(): void
    {
        $business = Business::factory()->create(['has_portfolio' => false]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.businesses.portfolio', $business), ['has_portfolio' => true])
            ->assertForbidden();

        $this->assertFalse($business->refresh()->has_portfolio);
    }

    public function test_super_admin_can_toggle_the_portfolio_switch(): void
    {
        Business::factory()->parent()->create();
        $business = Business::factory()->create(['has_portfolio' => false]);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->put(route('panel.businesses.portfolio', $business), ['has_portfolio' => true])
            ->assertSessionHasNoErrors();

        $this->assertTrue($business->refresh()->has_portfolio);
    }

    public function test_business_admin_cannot_toggle_the_publish_switch(): void
    {
        $business = Business::factory()->unpublished()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.businesses.published', $business), ['is_published' => true])
            ->assertForbidden();

        $this->assertFalse($business->refresh()->is_published);
    }

    public function test_super_admin_can_toggle_the_publish_switch(): void
    {
        Business::factory()->parent()->create();
        $business = Business::factory()->unpublished()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->put(route('panel.businesses.published', $business), ['is_published' => true])
            ->assertSessionHasNoErrors();

        $this->assertTrue($business->refresh()->is_published);
    }

    #[DataProvider('panelRoutes')]
    public function test_panel_pages_are_kept_out_of_search_engines(string $routeName): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Business::factory()->parent()->create();

        $this->actingAs($superAdmin)
            ->get(route($routeName))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_the_login_page_is_kept_out_of_search_engines(): void
    {
        // Halaman yang paling mungkin ditemukan crawler justru halaman login,
        // dan route-nya milik Fortify — di luar grup route panel. Sempat
        // tidak terlindungi sampai ketahuan saat diuji lewat HTTP sungguhan.
        $this->get(route('login'))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    #[DataProvider('panelRoutes')]
    public function test_redirects_to_login_also_carry_the_noindex_header(string $routeName): void
    {
        // Respons redirect dari middleware `auth` dihasilkan lewat exception,
        // jadi middleware yang dipasang per-grup tidak sempat menyentuhnya.
        $this->get(route($routeName))
            ->assertRedirect(route('login'))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_public_pages_are_not_marked_noindex(): void
    {
        // Kebalikannya juga harus benar: halaman publik justru perlu terindeks
        // (spec §3 — SEO induk mengalir ke anak usaha).
        $this->get('/')->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_panel_root_redirects_to_the_dashboard(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->get('/jcorp-panel')
            ->assertRedirect(route('panel.dashboard'));
    }
}
