<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Database\Seeders\BusinessSeeder;
use Database\Seeders\ClientContent;
use Database\Seeders\ClientContentSeeder;
use Database\Seeders\SampleContent;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Setelah seluruh seeder jalan, TIDAK BOLEH ada anak usaha yang tertinggal
 * kosong.
 *
 * Latar belakangnya: Sweetness Things sempat terlewat dari
 * SampleContent::businesses() — sisa dari penggabungan dua seeder lama, saat
 * Sweetness masih punya seedernya sendiri. Akibatnya di database yang
 * benar-benar bersih, Sweetness tidak terbit dan tanpa tentang maupun kontak.
 *
 * Tidak ketahuan di localhost karena datanya sudah terisi seeder lama sebelum
 * digabung. Baru terlihat saat mencoba mengisi server yang kosong.
 *
 * Sejak materi asli client masuk, pengisinya ada DUA: ClientContentSeeder
 * untuk yang materinya sudah datang, SampleContentSeeder untuk sisanya —
 * yang pertama dilewati seeder contoh supaya produk karangan tidak
 * berdampingan dengan produk asli.
 *
 * Karena itu test di sini menjalankan keduanya. Yang dijaga tetap sama:
 * tidak ada yang tertinggal kosong, siapa pun yang mengisinya. Menguji satu
 * seeder saja akan meloloskan anak usaha yang jatuh di antara keduanya.
 */
class SampleContentSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Meniru server baru: database kosong, lalu diisi dari nol. */
    private function seedAll(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);
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

    /**
     * Setiap ANAK USAHA harus punya nomor yang bisa dihubungi.
     *
     * Induk dikecualikan sejak 21 Agustus: perannya etalase, dan nomor yang
     * diberikan client masih diragukan (sembilan digit — lihat catatan di
     * ClientContent), jadi bagian kontaknya sengaja dibiarkan kosong sampai
     * dipastikan. Halaman induk tetap layak tayang tanpa itu; pengunjung
     * menghubungi unit usaha yang dituju, bukan induknya.
     */
    public function test_every_subsidiary_gets_contact_details(): void
    {
        $this->seedAll();

        $tanpaKontak = Business::query()
            ->subsidiaries()
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

    /**
     * Anak usaha yang materinya BELUM masuk harus terisi produk contoh.
     *
     * Yang materinya sudah masuk dikecualikan: daftar produknya bergantung
     * pada apa yang client kirim. ngelash sudah mengirim profil lengkap tapi
     * belum daftar harga, jadi section "Layanan & Harga"-nya memang kosong
     * — dan itu disengaja, bukan terlewat (lihat ClientContentSeederTest).
     */
    public function test_every_subsidiary_without_client_material_gets_catalog_items(): void
    {
        $this->seedAll();

        $tanpaProduk = Business::query()
            ->subsidiaries()
            ->whereNotIn('slug', array_keys(ClientContent::businesses()))
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
     * kalau tidak, produknya masuk tapi halamannya kosong. Diperiksa di
     * kedua berkas, karena keduanya sekarang jadi sumber isi.
     */
    public function test_catalog_and_profile_lists_cover_the_same_businesses(): void
    {
        foreach ([SampleContent::class, ClientContent::class] as $source) {
            $profil = array_keys($source::businesses());
            $katalog = array_keys($source::catalogItems());

            $adaProdukTanpaProfil = array_diff($katalog, $profil);

            $this->assertSame(
                [],
                $adaProdukTanpaProfil,
                "{$source}: punya produk tapi tidak punya profil: ".implode(', ', $adaProdukTanpaProfil),
            );
        }
    }

    /**
     * Anak usaha yang materi aslinya sudah masuk TIDAK BOLEH ikut terisi
     * teks contoh.
     *
     * Kalau keduanya mengisi anak usaha yang sama, produk karangan berdiri
     * berdampingan dengan produk asli di halaman yang sama — dan pengunjung
     * tidak punya cara membedakannya.
     */
    public function test_the_two_content_sources_never_overlap(): void
    {
        $bertabrakan = array_intersect(
            array_keys(ClientContent::businesses()),
            array_keys(SampleContent::catalogItems()),
        );

        $this->seedAll();

        foreach ($bertabrakan as $slug) {
            $business = Business::where('slug', $slug)->firstOrFail();

            $this->assertSame(
                0,
                $business->catalogItems()
                    ->where('category', CatalogItem::SAMPLE_MARKER)
                    ->count(),
                "[{$slug}] punya materi asli tapi kemasukan produk contoh.",
            );
        }
    }

    /**
     * Foto tidak boleh nyasar ke anak usaha yang sakelarnya mati.
     *
     * Pasangan sebaliknya — sakelar menyala tapi belum ada foto — TIDAK
     * lagi dianggap salah: ngelash sakelarnya menyala sementara foto hasil
     * kerjanya belum dikirim client. Section-nya hilang sendiri selama
     * kosong, dan sakelar yang sudah menyala membuat admin bisa langsung
     * mengunggah tanpa menyalakan apa pun lebih dulu.
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

        // Yang materinya belum masuk tetap harus punya foto contoh kalau
        // sakelarnya menyala — kalau tidak, sakelarnya menyala percuma dan
        // itu tanda ada yang terlewat di daftar foto contoh.
        $menyalaTapiKosong = Business::query()
            ->where('has_portfolio', true)
            ->whereNotIn('slug', array_keys(ClientContent::businesses()))
            ->whereDoesntHave('portfolioItems')
            ->pluck('slug')
            ->all();

        $this->assertSame([], $menyalaTapiKosong);
    }

    /**
     * Logonya sekarang dipasang ClientContentSeeder — berkas itu materi asli
     * dari client, bukan data contoh. Yang dijaga tetap sama: setelah semua
     * seeder jalan, logonya terpasang.
     */
    public function test_the_sweetness_logo_is_attached(): void
    {
        $this->seedAll();

        $this->assertSame(
            ClientContent::SWEETNESS_LOGO,
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
