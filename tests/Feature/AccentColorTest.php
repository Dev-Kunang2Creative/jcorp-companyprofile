<?php

namespace Tests\Feature;

use App\Models\Business;
use Database\Seeders\BusinessSeeder;
use Database\Seeders\ClientContent;
use Database\Seeders\ClientContentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Warna aksen per anak usaha (DESIGN_SYSTEM §2.3).
 *
 * Nilainya berasal dari database dan berakhir di HTML sebagai isi atribut
 * `style`. Itu membuatnya berbeda dari kolom teks lain: yang lain paling
 * banter tampil salah, yang ini bisa menyuntikkan CSS.
 *
 * Karena itu test pertama di berkas ini bukan soal tampilan, melainkan
 * soal keamanan.
 */
class AccentColorTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------- keamanan

    /**
     * Nilai yang bukan hex TIDAK BOLEH sampai ke halaman.
     *
     * Penjaga terpenting di berkas ini. Kolom `accent_color` dipasang di
     * atribut `style`, jadi nilai seperti `red;background:url(x)` akan
     * lolos begitu saja kalau bentuknya tidak diperiksa.
     *
     * Yang salah bentuk tidak "diperbaiki" — diganti kuningan induk.
     * Lebih baik warnanya keliru daripada halamannya jadi celah.
     *
     * CATATAN: kolomnya dibatasi 7 karakter di migrasi, jadi muatan
     * panjang tertahan lebih dulu di lapisan database (MySQL menolaknya
     * dengan galat 1406). Daftar di bawah karena itu berisi yang MUAT
     * dalam 7 karakter — justru itu yang berbahaya, karena hanya
     * pemeriksaan bentuk di aplikasi yang bisa menahannya.
     */
    public function test_a_value_that_is_not_a_hex_colour_never_reaches_the_page(): void
    {
        $ditolak = [
            'red;--x',   // memutus atribut style, muat 7 karakter
            '#fff',      // tiga digit — tidak diterima
            '#GGGGGG',   // bukan heksa
            'red',
            '',
            '#7C5F3',    // lima digit
            '#7C5F334',  // delapan digit — tertahan panjang kolom juga
        ];

        foreach ($ditolak as $nilai) {
            $business = Business::factory()->create([
                'slug' => 'uji-aksen',
                'accent_color' => mb_substr($nilai, 0, 7),
            ]);

            $this->assertSame(
                Business::DEFAULT_ACCENT,
                $business->safeAccentColor(),
                "Nilai [{$nilai}] seharusnya ditolak.",
            );

            $business->forceDelete();
        }
    }

    /**
     * Lapisan kedua: panjang kolom.
     *
     * Muatan panjang seperti `red; background: url(https://...)` tidak
     * pernah sampai ke pemeriksaan bentuk — MySQL menolaknya lebih dulu.
     * Test ini yang menjaga batas itu tetap ada di migrasi; kalau suatu
     * saat kolomnya dilebarkan, di sinilah ketahuannya.
     */
    public function test_the_column_is_too_short_to_hold_an_injection_payload(): void
    {
        $this->expectException(QueryException::class);

        Business::factory()->create([
            'accent_color' => 'red; background: url(https://jahat.example/x)',
        ]);
    }

    public function test_a_proper_hex_colour_passes_through_unchanged(): void
    {
        $business = Business::factory()->create(['accent_color' => '#9E1B21']);

        $this->assertSame('#9E1B21', $business->safeAccentColor());
    }

    public function test_lowercase_hex_is_accepted(): void
    {
        // Admin bisa saja mengetiknya huruf kecil; itu tetap warna yang sah.
        $business = Business::factory()->create(['accent_color' => '#9e1b21']);

        $this->assertSame('#9e1b21', $business->safeAccentColor());
    }

    public function test_a_business_without_an_accent_falls_back_to_the_parent_brass(): void
    {
        // Anak usaha baru yang belum disetel warnanya tetap harus tampil
        // wajar, bukan kehilangan seluruh warna aksennya.
        $business = Business::factory()->create(['accent_color' => null]);

        $this->assertSame(
            Business::DEFAULT_ACCENT,
            $business->safeAccentColor(),
        );
    }

    // ------------------------------------------------- sampai ke halaman

    public function test_the_accent_reaches_the_subsidiary_page(): void
    {
        Business::factory()->create([
            'slug' => 'sweetness-things',
            'accent_color' => '#6B4F0F',
        ]);

        $this->get('/sweetness-things')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('business.accent_color', '#6B4F0F')
            );
    }

    public function test_the_accent_reaches_the_home_page_and_each_card(): void
    {
        Business::factory()->parent()->create([
            'slug' => 'jcorp',
            'accent_color' => '#7C5F33',
        ]);
        Business::factory()->create([
            'name' => 'Anak Usaha',
            'accent_color' => '#14488C',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('parent.accent_color', '#7C5F33')
                ->where('subsidiaries.0.accent_color', '#14488C')
            );
    }

    public function test_a_broken_accent_in_the_database_still_renders_a_usable_page(): void
    {
        // Bukan sekadar nilainya diganti — halamannya harus tetap tampil.
        Business::factory()->create([
            'slug' => 'sweetness-things',
            'accent_color' => 'red;--x',
        ]);

        $this->get('/sweetness-things')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('business.accent_color', Business::DEFAULT_ACCENT)
            );
    }

    // ------------------------------------------------------------ seeder

    public function test_every_business_gets_an_accent_from_the_seeder(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);

        $tanpaAksen = Business::query()
            ->whereNull('accent_color')
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $tanpaAksen,
            'Belum punya warna aksen: '.implode(', ', $tanpaAksen),
        );
    }

    /**
     * Setiap warna di ClientContent harus berbentuk hex yang sah.
     *
     * Kalau ada yang salah ketik saat menambah anak usaha baru, halamannya
     * diam-diam jatuh ke kuningan induk — tanpa satu pun error, dan warna
     * yang dimaksud tidak pernah muncul.
     */
    public function test_every_accent_in_client_content_is_a_valid_hex(): void
    {
        foreach (ClientContent::accentColors() as $slug => $warna) {
            $this->assertMatchesRegularExpression(
                '/^#[0-9A-Fa-f]{6}$/',
                $warna,
                "Warna aksen [{$slug}] bukan hex yang sah: {$warna}",
            );
        }
    }

    public function test_the_seeder_never_overwrites_a_colour_the_admin_chose(): void
    {
        $this->seed(BusinessSeeder::class);

        $business = Business::where('slug', 'sweetness-things')->firstOrFail();
        $business->accent_color = '#123456';
        $business->save();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame('#123456', $business->refresh()->accent_color);
    }
}
