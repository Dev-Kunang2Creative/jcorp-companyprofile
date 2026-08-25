<?php

namespace Tests\Feature;

use App\Models\Business;
use Database\Seeders\BusinessSeeder;
use Database\Seeders\ClientContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Ikon tab browser per anak usaha.
 *
 * Kartu anak usaha di etalase induk membuka TAB BARU. Pengunjung yang
 * melihat beberapa unit sekaligus punya empat-lima tab terbuka, dan
 * kalau ikonnya sama semua, satu-satunya pembeda adalah judul tab yang
 * terpotong jadi beberapa huruf.
 */
class FaviconTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------- penurunan jalur

    /**
     * Jalur lencana DITURUNKAN dari jalur logo, bukan disimpan terpisah.
     *
     * Kolom kedua berarti dua daftar yang harus dijaga tetap seiring —
     * dan cepat atau lambat keduanya berbeda.
     */
    public function test_the_badge_path_is_derived_from_the_logo_path(): void
    {
        $business = Business::factory()->create([
            'logo_path' => 'images/brand/ngelash-logo-600.webp',
        ]);

        $this->assertSame(
            'images/brand/ngelash-badge-48.png',
            $business->faviconPath(),
        );
    }

    public function test_a_business_without_a_logo_has_no_badge(): void
    {
        $business = Business::factory()->create(['logo_path' => null]);

        $this->assertNull($business->faviconPath());
    }

    /**
     * Logo yang diunggah admin lewat panel TIDAK punya lencana.
     *
     * Berkas lencana dibuat skrip di docs/scripts/ bersamaan dengan
     * logonya; unggahan panel tersimpan di storage dengan nama acak dan
     * tidak punya padanan lencana.
     */
    public function test_an_uploaded_logo_has_no_badge(): void
    {
        $business = Business::factory()->create([
            'logo_path' => 'sweetness-things/a1b2c3d4e5.webp',
        ]);

        $this->assertNull($business->faviconPath());
    }

    /**
     * Nama yang cocok pola tapi berkasnya tidak ada tetap null.
     *
     * Favicon yang menunjuk ke berkas hilang membuat browser menampilkan
     * ikon kosong — lebih buruk daripada memakai ikon induk.
     */
    public function test_a_missing_badge_file_falls_back_to_null(): void
    {
        $business = Business::factory()->create([
            'logo_path' => 'images/brand/belum-ada-logo-600.webp',
        ]);

        $this->assertFalse(
            File::exists(public_path('images/brand/belum-ada-badge-48.png')),
        );
        $this->assertNull($business->faviconPath());
    }

    // ------------------------------------------------ sampai ke halaman

    public function test_each_subsidiary_page_carries_its_own_badge(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);

        $harapan = [
            '/sweetness-things' => 'sweetness-badge-48.png',
            '/ngelash' => 'ngelash-badge-48.png',
            '/nails-by-me' => 'nails-badge-48.png',
            '/ayodya-logistic' => 'ayodya-badge-48.png',
            '/j-land-property' => 'jland-badge-48.png',
        ];

        foreach ($harapan as $alamat => $berkas) {
            $this->get($alamat)
                ->assertOk()
                ->assertSee($berkas, false);
        }
    }

    public function test_the_home_page_carries_the_parent_badge(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('jcorp-badge-48.png', false);
    }

    /**
     * PENJAGA TERPENTING: hanya SATU <link rel="icon"> per halaman.
     *
     * Percobaan pertama memasangnya lewat <Head> Inertia menghasilkan
     * DUA — satu bawaan dari Blade, satu dari React. Inertia memang
     * menggantinya setelah JavaScript jalan, tapi browser sudah membaca
     * yang pertama lebih dulu, dan ikonnya sempat berkedip berganti.
     */
    public function test_a_page_never_carries_two_icon_links(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);

        foreach (['/', '/ngelash', '/ayodya-logistic'] as $alamat) {
            $html = $this->get($alamat)->getContent();

            $this->assertSame(
                1,
                substr_count((string) $html, '<link rel="icon"'),
                "Halaman [{$alamat}] punya lebih dari satu ikon.",
            );
        }
    }

    public function test_the_admin_panel_keeps_the_default_icon(): void
    {
        // Panel bukan halaman merek — ikonnya tidak perlu berganti-ganti
        // mengikuti anak usaha yang sedang dikelola.
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('favicon.ico', false);
    }

    public function test_a_business_without_a_badge_falls_back_to_the_default_icon(): void
    {
        Business::factory()->create([
            'slug' => 'tanpa-lencana',
            'logo_path' => null,
        ]);

        $this->get('/tanpa-lencana')
            ->assertOk()
            ->assertSee('favicon.ico', false);
    }
}
