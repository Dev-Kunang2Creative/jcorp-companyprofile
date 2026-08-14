<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Database\Seeders\BusinessSeeder;
use Database\Seeders\SampleContent;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seeder data contoh harus mengisi SELURUH anak usaha.
 *
 * Latar belakangnya: Sweetness Things sempat terlewat dari
 * SampleContent::businesses() — sisa dari penggabungan dua seeder lama, saat
 * Sweetness masih punya seedernya sendiri. Akibatnya di database yang
 * benar-benar bersih, Sweetness tidak terbit dan tanpa tentang maupun kontak.
 *
 * Tidak ketahuan di localhost karena datanya sudah terisi seeder lama sebelum
 * digabung. Baru terlihat saat mencoba mengisi server yang kosong.
 *
 * Test di sini menjalankan seeder pada database kosong — kondisi yang sama
 * dengan server baru.
 */
class SampleContentSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Meniru server baru: database kosong, lalu diisi dari nol. */
    private function seedAll(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(SampleContentSeeder::class);
    }

    public function test_every_business_gets_a_profile(): void
    {
        $this->seedAll();

        // Kalau ada satu saja yang terlewat, halamannya tampil kosong
        // padahal seharusnya berisi.
        $tanpaProfil = Business::query()
            ->whereNull('description')
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $tanpaProfil,
            'Anak usaha ini tidak punya cerita: '.implode(', ', $tanpaProfil),
        );
    }

    public function test_every_business_gets_contact_details(): void
    {
        $this->seedAll();

        $tanpaKontak = Business::query()
            ->whereNull('whatsapp')
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $tanpaKontak,
            'Anak usaha ini tidak punya kontak: '.implode(', ', $tanpaKontak),
        );
    }

    public function test_every_business_is_published(): void
    {
        $this->seedAll();

        $belumTerbit = Business::query()
            ->where('is_published', false)
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $belumTerbit,
            'Anak usaha ini belum terbit: '.implode(', ', $belumTerbit),
        );
    }

    public function test_every_subsidiary_gets_catalog_items(): void
    {
        $this->seedAll();

        $tanpaProduk = Business::query()
            ->subsidiaries()
            ->whereDoesntHave('catalogItems')
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $tanpaProduk,
            'Anak usaha ini tidak punya produk: '.implode(', ', $tanpaProduk),
        );
    }

    /**
     * Penjaga terhadap penyebab bug aslinya.
     *
     * Setiap slug yang punya daftar produk harus punya profil juga —
     * kalau tidak, produknya masuk tapi halamannya kosong.
     */
    public function test_catalog_and_profile_lists_cover_the_same_businesses(): void
    {
        $profil = array_keys(SampleContent::businesses());
        $katalog = array_keys(SampleContent::catalogItems());

        $adaProdukTanpaProfil = array_diff($katalog, $profil);

        $this->assertSame(
            [],
            $adaProdukTanpaProfil,
            'Punya produk tapi tidak punya profil: '.implode(', ', $adaProdukTanpaProfil),
        );
    }

    /**
     * Portfolio hanya untuk yang sakelarnya menyala, dan sebaliknya.
     */
    public function test_portfolio_photos_only_go_to_businesses_that_use_them(): void
    {
        $this->seedAll();

        $punyaFotoTapiMati = Business::query()
            ->where('has_portfolio', false)
            ->whereHas('portfolioItems')
            ->pluck('slug')
            ->all();

        $this->assertSame([], $punyaFotoTapiMati);

        $menyalaTapiKosong = Business::query()
            ->where('has_portfolio', true)
            ->whereDoesntHave('portfolioItems')
            ->pluck('slug')
            ->all();

        $this->assertSame([], $menyalaTapiKosong);
    }

    public function test_the_sweetness_logo_is_attached(): void
    {
        $this->seedAll();

        $this->assertSame(
            SampleContent::SWEETNESS_LOGO,
            Business::where('slug', 'sweetness-things')->value('logo_path'),
        );
    }

    public function test_running_it_twice_does_not_duplicate_anything(): void
    {
        $this->seedAll();

        $produkAwal = CatalogItem::count();
        $fotoAwal = PortfolioItem::count();

        $this->seed(SampleContentSeeder::class);

        $this->assertSame($produkAwal, CatalogItem::count());
        $this->assertSame($fotoAwal, PortfolioItem::count());
    }

    public function test_it_never_overwrites_what_an_admin_has_written(): void
    {
        $this->seed(BusinessSeeder::class);

        $business = Business::where('slug', 'ngelash')->firstOrFail();
        $business->tagline = 'Tagline asli yang ditulis admin';
        $business->save();

        $this->seed(SampleContentSeeder::class);

        $this->assertSame(
            'Tagline asli yang ditulis admin',
            $business->refresh()->tagline,
        );
    }
}
