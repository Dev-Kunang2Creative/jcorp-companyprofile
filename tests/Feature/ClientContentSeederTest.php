<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CatalogItem;
use Database\Seeders\BusinessSeeder;
use Database\Seeders\ClientContent;
use Database\Seeders\ClientContentSeeder;
use Database\Seeders\SampleContent;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Seeder materi ASLI dari client.
 *
 * Yang paling penting dijaga di sini: materi asli benar-benar SAMPAI KE
 * HALAMAN. Bahayanya halus — kalau seeder memakai pola `??=` seperti
 * SampleContentSeeder, materi client masuk ke database tanpa menggantikan
 * teks contoh yang sudah lebih dulu ada di sana. Tidak ada yang gagal, tidak
 * ada error, dan nomor telepon karangan tetap tayang.
 *
 * Karena itu sebagian besar test di sini menjalankan SampleContentSeeder
 * LEBIH DULU — meniru keadaan database yang sebenarnya, bukan database
 * kosong yang tidak pernah ada di lapangan.
 */
class ClientContentSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Server yang belum pernah diisi apa pun. */
    private function seedFresh(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);
    }

    /** Keadaan yang sebenarnya: teks contoh sudah lebih dulu ada. */
    private function seedOverSamples(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(SampleContentSeeder::class);
        $this->seed(ClientContentSeeder::class);
    }

    private function sweetness(): Business
    {
        return Business::where('slug', 'sweetness-things')->firstOrFail();
    }

    // ------------------------------------------------ materi masuk & tampil

    public function test_the_profile_is_filled_from_client_material(): void
    {
        $this->seedFresh();

        $business = $this->sweetness();
        $expected = ClientContent::businesses()['sweetness-things'];

        $this->assertSame($expected['description'], $business->description);
        $this->assertSame($expected['vision'], $business->vision);
        $this->assertSame($expected['mission'], $business->mission);
        $this->assertSame($expected['services'], $business->services);
        $this->assertSame($expected['highlights'], $business->highlights);
        $this->assertSame($expected['contact_note'], $business->contact_note);
    }

    public function test_the_business_is_published(): void
    {
        $this->seedFresh();

        $this->assertTrue($this->sweetness()->is_published);
    }

    public function test_the_logo_is_attached(): void
    {
        $this->seedFresh();

        $this->assertSame(
            ClientContent::SWEETNESS_LOGO,
            $this->sweetness()->logo_path,
        );
    }

    public function test_the_catalog_carries_the_real_products(): void
    {
        $this->seedFresh();

        $items = $this->sweetness()->catalogItems()->orderBy('sort_order')->get();

        $this->assertCount(2, $items);
        $this->assertSame('Dessert Box Series', $items[0]->name);
        $this->assertSame('Soft Cookies', $items[1]->name);
    }

    public function test_products_without_a_price_carry_a_note_instead(): void
    {
        // Client belum mengirim daftar harga. Kartunya harus menampilkan
        // "hubungi kami" tanpa angka — bukan Rp 0, dan bukan harga karangan.
        $this->seedFresh();

        foreach ($this->sweetness()->catalogItems as $item) {
            $this->assertNull($item->price);
            $this->assertSame('hubungi kami', $item->price_note);
            $this->assertNull($item->formattedAmount());
        }
    }

    public function test_real_products_are_never_marked_as_samples(): void
    {
        // Kalau tertandai, `jcorp:clear-samples` akan menghapus produk asli.
        $this->seedFresh();

        foreach ($this->sweetness()->catalogItems as $item) {
            $this->assertNotSame(CatalogItem::SAMPLE_MARKER, $item->category);
        }
    }

    // ------------------------------------- INTI: teks contoh tergantikan

    public function test_it_replaces_sample_text_that_is_already_in_the_database(): void
    {
        // Test terpenting di berkas ini.
        //
        // Tanpa penggantian, seluruh pekerjaan ini lolos setiap pemeriksaan
        // tapi halamannya tetap menampilkan cerita karangan.
        $this->seedOverSamples();

        $business = $this->sweetness();
        $sample = SampleContent::businesses()['sweetness-things'];
        $client = ClientContent::businesses()['sweetness-things'];

        $this->assertNotSame($sample['description'], $business->description);
        $this->assertSame($client['description'], $business->description);
    }

    public function test_it_replaces_the_sample_phone_number(): void
    {
        // Nomor karangan yang tetap tayang bukan sekadar salah data — ada
        // orang sungguhan di ujung nomor itu.
        $this->seedOverSamples();

        $business = $this->sweetness();

        $this->assertNotSame('6281234567890', $business->whatsapp);
        $this->assertSame('6281938020815', $business->whatsapp);
        $this->assertSame('628813742352', $business->whatsapp_alt);
    }

    public function test_it_replaces_the_sample_address(): void
    {
        $this->seedOverSamples();

        $this->assertSame('Semarang', $this->sweetness()->address);
    }

    public function test_it_empties_what_the_client_did_not_provide(): void
    {
        // Tagline dan jam buka tidak ada di materi client. Keduanya sudah
        // terisi teks contoh di database, jadi harus dikosongkan — bukan
        // dibiarkan tertinggal.
        $this->seedOverSamples();

        $business = $this->sweetness();

        $this->assertNull($business->tagline);
        $this->assertNull($business->business_hours);
    }

    public function test_it_sweeps_away_sample_products(): void
    {
        // Enam produk karangan berdampingan dengan dua produk asli akan
        // membuat pengunjung tidak tahu mana yang benar-benar dijual.
        $this->seedOverSamples();

        $items = $this->sweetness()->catalogItems;

        $this->assertCount(2, $items);
        $this->assertSame(
            0,
            CatalogItem::withTrashed()
                ->where('business_id', $this->sweetness()->id)
                ->where('category', CatalogItem::SAMPLE_MARKER)
                ->count(),
        );
    }

    // ------------------------------------------- tulisan admin tidak hilang

    public function test_it_never_overwrites_what_an_admin_has_written(): void
    {
        $this->seed(BusinessSeeder::class);

        $business = $this->sweetness();
        $business->description = 'Cerita yang ditulis sendiri oleh admin.';
        $business->whatsapp = '628999888777';
        $business->save();

        $this->seed(ClientContentSeeder::class);

        $business->refresh();

        $this->assertSame('Cerita yang ditulis sendiri oleh admin.', $business->description);
        $this->assertSame('628999888777', $business->whatsapp);
    }

    public function test_it_never_empties_a_tagline_the_admin_has_written(): void
    {
        // Tagline memang harus kosong menurut materi client — tapi kalau
        // admin sudah menuliskannya sendiri, itu keputusan sadar dan tidak
        // boleh dihapus seeder.
        $this->seed(BusinessSeeder::class);

        $business = $this->sweetness();
        $business->tagline = 'Tagline yang ditulis admin';
        $business->save();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame('Tagline yang ditulis admin', $business->refresh()->tagline);
    }

    public function test_it_never_touches_products_the_admin_added(): void
    {
        $this->seed(BusinessSeeder::class);

        $business = $this->sweetness();
        CatalogItem::factory()->for($business)->create([
            'name' => 'Produk tambahan dari admin',
            'category' => 'Musiman',
        ]);

        $this->seed(ClientContentSeeder::class);

        $this->assertDatabaseHas('catalog_items', [
            'name' => 'Produk tambahan dari admin',
        ]);
    }

    // --------------------------------------------------------- aman diulang

    public function test_running_it_twice_changes_nothing(): void
    {
        $this->seedFresh();

        $before = $this->sweetness()->only([
            'description', 'vision', 'whatsapp', 'whatsapp_alt', 'address',
        ]);
        $itemsBefore = CatalogItem::count();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame($before, $this->sweetness()->only(array_keys($before)));
        $this->assertSame($itemsBefore, CatalogItem::count());
    }

    // ------------------------------------------------ nomor telepon terpakai

    /**
     * Nomor dari client bentuknya 0819-3802-0815 dan 0881-3742-352, dan
     * normalisasinya dikerjakan tangan saat menyalin. Kalau salah satu
     * meleset, tidak ada yang gagal — tombol WhatsApp-nya saja yang
     * mengarah ke akun yang tidak dikenal.
     *
     * Regex yang dipakai di sini persis yang dipakai BusinessProfileRequest.
     */
    public function test_both_phone_numbers_match_the_format_the_panel_accepts(): void
    {
        $this->seedFresh();

        $business = $this->sweetness();

        $this->assertMatchesRegularExpression('/^62[0-9]{8,13}$/', (string) $business->whatsapp);
        $this->assertMatchesRegularExpression('/^62[0-9]{8,13}$/', (string) $business->whatsapp_alt);
    }

    public function test_the_instagram_username_matches_the_format_the_panel_accepts(): void
    {
        // Username client berakhiran "._" — dua karakter yang gampang hilang
        // saat disalin, dan tautannya jadi mengarah ke akun orang lain.
        $this->seedFresh();

        $business = $this->sweetness();

        $this->assertSame('sweetnessthings._', $business->instagram);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9._]+$/', (string) $business->instagram);
    }

    // ------------------------------------------------------------ induk

    private function parent(): Business
    {
        return Business::where('slug', 'jcorp')->firstOrFail();
    }

    public function test_the_parent_gets_its_official_name(): void
    {
        // Nama lama "J Corp" sudah tersimpan di database sejak Fase 1, jadi
        // penggantiannya harus benar-benar menimpa — bukan sekadar mengisi
        // kolom kosong.
        $this->seedOverSamples();

        $this->assertSame('J-Corporate Group', $this->parent()->name);
    }

    /**
     * SETIAP ejaan lama harus tergantikan, bukan cuma yang pertama.
     *
     * Nama induk berganti dua kali: "J Corp" (Fase 1) lalu
     * "J-Corporette Group" (21 Agustus, ejaan keliru). Database yang
     * sudah jalan — termasuk server — bisa menyimpan salah satunya.
     *
     * Yang berbahaya justru yang kedua: bentuknya sudah mirip nama
     * resmi, jadi kalau tertinggal tidak langsung terlihat salah.
     */
    public function test_every_former_name_is_replaced(): void
    {
        foreach (['J Corp', 'J-Corporette Group'] as $namaLama) {
            $this->seed(BusinessSeeder::class);

            $induk = $this->parent();
            $induk->name = $namaLama;
            $induk->save();

            $this->seed(ClientContentSeeder::class);

            $this->assertSame(
                'J-Corporate Group',
                $induk->refresh()->name,
                "Nama lama [{$namaLama}] tidak tergantikan.",
            );

            // Bersihkan supaya putaran berikutnya mulai dari nol.
            Business::query()->forceDelete();
        }
    }

    public function test_a_name_the_admin_wrote_is_never_replaced(): void
    {
        // Batas dari test di atas: yang BUKAN nama lama adalah keputusan
        // sadar admin, dan seeder tidak berhak membatalkannya.
        $this->seed(BusinessSeeder::class);

        $induk = $this->parent();
        $induk->name = 'Nama Pilihan Admin';
        $induk->save();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame('Nama Pilihan Admin', $induk->refresh()->name);
    }

    public function test_the_parent_profile_is_filled(): void
    {
        $this->seedFresh();

        $parent = $this->parent();
        $expected = ClientContent::businesses()['jcorp'];

        $this->assertSame($expected['description'], $parent->description);
        $this->assertSame($expected['vision'], $parent->vision);
        $this->assertSame($expected['mission'], $parent->mission);
        $this->assertSame(ClientContent::JCORP_LOGO, $parent->logo_path);
    }

    public function test_the_parent_gets_its_tagline_and_unit_socials(): void
    {
        $this->seedFresh();

        $parent = $this->parent();

        $this->assertSame('Growing Together, Serving All', $parent->tagline);
        $this->assertNotEmpty($parent->contact_note);

        // Empat sejak akun J-Land dipastikan (magerpindah.id). Yang belum
        // ada: PT. Ayodya Utama Logistic — memang tidak disebut client.
        $this->assertCount(4, $parent->unit_socials ?? []);
    }

    public function test_the_previous_official_parent_tagline_is_upgraded(): void
    {
        $this->seedFresh();

        $parent = $this->parent();
        $parent->tagline = 'Tumbuh Bersama, Melayani Semua';
        $parent->save();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame('Growing Together, Serving All', $parent->refresh()->tagline);
    }

    public function test_a_parent_tagline_written_by_an_admin_is_not_replaced(): void
    {
        $this->seedFresh();

        $parent = $this->parent();
        $parent->tagline = 'Tagline pilihan admin';
        $parent->save();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame('Tagline pilihan admin', $parent->refresh()->tagline);
    }

    /**
     * Setiap username Instagram harus bisa jadi tautan yang hidup.
     *
     * Materi client memuat "@j-landproperty" — Instagram tidak menerima
     * tanda hubung pada username, jadi tautannya pasti mati. Itu sengaja
     * tidak dipasang, dan test ini yang menjaga supaya tidak ada yang
     * memasukkannya kembali tanpa memeriksa.
     */
    public function test_every_unit_social_username_is_a_valid_instagram_handle(): void
    {
        $this->seedFresh();

        foreach ($this->parent()->unit_socials ?? [] as $unit) {
            $this->assertMatchesRegularExpression(
                '/^[A-Za-z0-9._]+$/',
                $unit['username'],
                "Username [{$unit['username']}] tidak bisa jadi tautan Instagram yang hidup.",
            );

            $this->assertArrayHasKey('label', $unit);
            $this->assertNotSame('', trim($unit['label']));
        }
    }

    public function test_the_parent_carries_no_invented_contact_details(): void
    {
        // Data contoh sempat mengisi induk dengan nomor 6281200000000 dan
        // alamat "Jl. Contoh Utama No. 1, Bekasi". Client baru memberikan
        // satu nomor, dan nomor itu sendiri masih diragukan (lihat catatan
        // di ClientContent) — jadi bagian kontak induk harus kosong, bukan
        // menyisakan sisa karangan.
        $this->seedOverSamples();

        $parent = $this->parent();

        $this->assertNull($parent->whatsapp);
        $this->assertNull($parent->instagram);
        $this->assertNull($parent->address);
        $this->assertNull($parent->business_hours);

        // Tagline karangan ("Beberapa usaha yang tumbuh dari hal-hal
        // kecil…") harus tergantikan oleh slogan resmi dari client.
        $this->assertSame('Growing Together, Serving All', $parent->tagline);
    }

    public function test_the_parent_sells_nothing_of_its_own(): void
    {
        // Induk perannya etalase (spec §3): mengantar pengunjung ke anak
        // usaha, tidak punya katalog sendiri.
        $this->seedFresh();

        $this->assertSame(0, $this->parent()->catalogItems()->count());
    }

    // ---------------------------------------------------------- ngelash

    private function ngelash(): Business
    {
        return Business::where('slug', 'ngelash')->firstOrFail();
    }

    public function test_ngelash_profile_is_filled(): void
    {
        $this->seedFresh();

        $business = $this->ngelash();

        $this->assertSame('Enhance Your Beauty, Elevate Your Confidence.', $business->tagline);
        $this->assertCount(7, $business->mission ?? []);
        $this->assertCount(7, $business->highlights ?? []);
        $this->assertSame('6285156886646', $business->whatsapp);
        $this->assertSame('Kios G-09, Apartment Candiland', $business->address);
        $this->assertSame(ClientContent::NGELASH_LOGO, $business->logo_path);
    }

    public function test_ngelash_price_list_is_complete(): void
    {
        $this->seedOverSamples();

        $business = $this->ngelash();

        // Lima belas layanan, bukan dua belas — lihat test berikutnya.
        $this->assertSame(15, $business->catalogItems()->count());
        $this->assertSame(
            'Biaya sudah termasuk Primer + Lash Bound + Free Spoolie.',
            $business->catalog_note,
        );

        // Tarif karangan (Classic Rp150.000, dst) benar-benar tersapu.
        $this->assertSame(
            0,
            CatalogItem::withTrashed()
                ->where('business_id', $business->id)
                ->where('category', CatalogItem::SAMPLE_MARKER)
                ->count(),
        );
    }

    /**
     * Nama layanan yang sama di kategori berbeda tidak boleh saling menimpa.
     *
     * "Natural", "Medium", dan "Volume" ada di 2D LASH maupun 3D LASH dengan
     * harga berbeda. Seeder yang mencocokkan item dari NAMA SAJA membuat
     * tarif 2D tertimpa tarif 3D — dari 15 layanan tinggal 12, dan tiga
     * harga diam-diam berubah tanpa ada yang gagal.
     */
    public function test_services_with_the_same_name_in_different_categories_survive(): void
    {
        $this->seedFresh();

        $business = $this->ngelash();

        $harga = fn (string $name, string $category) => $business->catalogItems()
            ->where('name', $name)
            ->where('category', $category)
            ->value('price');

        $this->assertSame('80000.00', $harga('Natural', '2D Lash'));
        $this->assertSame('85000.00', $harga('Natural', '3D Lash'));
        $this->assertSame('90000.00', $harga('Medium', '2D Lash'));
        $this->assertSame('95000.00', $harga('Medium', '3D Lash'));
        $this->assertSame('100000.00', $harga('Volume', '2D Lash'));
        $this->assertSame('105000.00', $harga('Volume', '3D Lash'));
    }

    public function test_running_the_seeder_twice_does_not_duplicate_the_price_list(): void
    {
        // Kalau kunci pencocokan salah, jalan kedua menambah 15 baris lagi.
        $this->seedFresh();
        $this->seed(ClientContentSeeder::class);

        $this->assertSame(15, $this->ngelash()->catalogItems()->count());
    }

    public function test_ngelash_placeholder_photos_are_swept(): void
    {
        // Tiga foto contoh (images/placeholder/lash-*.webp) sempat tampil
        // sebagai "Hasil Kerja" — padahal itu gambar abstrak, bukan hasil
        // treatment sungguhan.
        $this->seedOverSamples();

        $this->assertSame(0, $this->ngelash()->portfolioItems()->count());
    }

    public function test_ngelash_keeps_its_portfolio_switch_on(): void
    {
        // Fotonya belum ada, tapi sakelarnya dibiarkan menyala: section-nya
        // sudah hilang sendiri karena kosong, dan admin bisa langsung
        // mengunggah tanpa perlu menyalakan apa pun lebih dulu.
        $this->seedOverSamples();

        $this->assertTrue($this->ngelash()->has_portfolio);
    }

    public function test_ngelash_invented_tiktok_is_removed(): void
    {
        // @ngelash.id di TikTok karangan — tidak ada di materi mana pun.
        // Instagram-nya dipertahankan karena disebut di materi induk.
        $this->seedOverSamples();

        $business = $this->ngelash();

        $this->assertNull($business->tiktok);
        $this->assertSame('ngelash.id', $business->instagram);
    }

    public function test_ngelash_invented_contact_details_are_replaced(): void
    {
        $this->seedOverSamples();

        $business = $this->ngelash();

        $this->assertNotSame('6281233334444', $business->whatsapp);
        $this->assertNotSame('Jl. Contoh Melati No. 8, Bekasi', $business->address);
        $this->assertNull($business->business_hours);
    }

    // ---------------------------------------------------------- Nail's by Me

    private function nails(): Business
    {
        return Business::where('slug', 'nails-by-me')->firstOrFail();
    }

    public function test_nails_profile_is_filled(): void
    {
        $this->seedFresh();

        $business = $this->nails();

        $this->assertCount(3, $business->mission ?? []);
        $this->assertCount(3, $business->services ?? []);
        $this->assertCount(5, $business->highlights ?? []);
        $this->assertSame('Profil Layanan', $business->services_label);
        $this->assertSame(ClientContent::NAILS_LOGO, $business->logo_path);
        $this->assertSame('nails.bymeeeeeee', $business->instagram);
        $this->assertSame('nails.bymeeeeeee', $business->tiktok);
    }

    public function test_nails_price_list_reaches_the_page(): void
    {
        $this->seedOverSamples();

        $business = $this->nails();

        $this->assertSame(10, $business->catalogItems()->count());
        $this->assertSame(
            'Harga di atas berlaku untuk tangan atau kaki, serta bebas pilih warna sesuka hati.',
            $business->catalog_note,
        );

        $this->get('/nails-by-me')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog', 10)
                ->where('catalog.0.name', 'Nail Art Polos')
                ->where('catalog.0.formatted_price', 'Rp'."\u{00A0}".'50.000')
                ->where('catalog.1.name', 'Nail Art Ombre')
                ->where('catalog.1.price_note', 'per kuku')
                ->where('catalog.6.price_note', 'Rp 5.000–25.000 per kuku')
                ->where('catalog.9.name', 'Remove Nail Art')
                ->where('catalog.9.formatted_price', 'Rp'."\u{00A0}".'30.000')
                ->where('business.catalog_note', $business->catalog_note)
            );
    }

    public function test_nails_price_list_is_not_duplicated_when_seeded_again(): void
    {
        $this->seedFresh();
        $this->seed(ClientContentSeeder::class);

        $this->assertSame(10, $this->nails()->catalogItems()->count());
    }

    public function test_nails_previous_starting_price_note_is_upgraded(): void
    {
        $this->seed(BusinessSeeder::class);

        $business = $this->nails();
        $business->contact_note = 'Nail art mulai dari Rp 30.000, harga akhir menyesuaikan tingkat kerumitan desain. Pemesanan lewat chat WhatsApp dengan sistem janji temu (appointment only), dan tersedia home service.';
        $business->save();

        $this->seed(ClientContentSeeder::class);

        $this->assertSame(
            'Pemesanan lewat chat WhatsApp dengan sistem janji temu (appointment only), dan tersedia home service.',
            $business->refresh()->contact_note,
        );
    }

    public function test_nails_placeholder_photos_are_swept(): void
    {
        // Empat gambar abstrak yang tampil sebagai "Hasil Kerja" — calon
        // pelanggan menilai kualitas nail art dari situ.
        $this->seedOverSamples();

        $this->assertSame(0, $this->nails()->portfolioItems()->count());
        $this->assertTrue($this->nails()->has_portfolio);
    }

    public function test_nails_invented_contact_details_are_replaced(): void
    {
        $this->seedOverSamples();

        $business = $this->nails();

        $this->assertNotSame('6281211112222', $business->whatsapp);
        $this->assertNotSame('nailsbyme.id', $business->instagram);
        $this->assertSame('6281225539182', $business->whatsapp);
        $this->assertNull($business->address);
        $this->assertNull($business->business_hours);
        $this->assertNull($business->tagline);
    }

    /**
     * Instagram yang tercantum di halaman induk harus sama dengan yang ada
     * di halaman anak usahanya.
     *
     * Dulu berbeda: induk menyebut "nails.bymeeeeeee" (dari client),
     * sementara halaman Nail's by Me sendiri memakai "nailsbyme.id" karangan
     * saya. Dua akun berbeda untuk usaha yang sama, dan salah satunya tidak
     * ada.
     */
    public function test_the_parent_and_the_subsidiary_agree_on_instagram(): void
    {
        $this->seedFresh();

        $daftar = collect($this->parent()->unit_socials ?? [])
            ->pluck('username', 'label');

        $this->assertSame(
            $this->nails()->instagram,
            $daftar["Nail's by Me"] ?? null,
        );
    }

    // ------------------------------------------------------- J-Land Property

    private function jland(): Business
    {
        return Business::where('slug', 'j-land-property')->firstOrFail();
    }

    public function test_jland_profile_is_filled(): void
    {
        $this->seedFresh();

        $business = $this->jland();

        $this->assertSame('J-Land Property', $business->name);
        $this->assertCount(5, $business->mission ?? []);
        $this->assertCount(3, $business->featured_services ?? []);
        $this->assertCount(4, $business->highlights ?? []);
        $this->assertCount(3, $business->services ?? []);
        $this->assertSame('Layanan Kami', $business->services_label);
    }

    public function test_jland_keeps_both_phone_numbers_with_their_owners(): void
    {
        $this->seedFresh();

        $business = $this->jland();

        $this->assertSame('6281225539182', $business->whatsapp);
        $this->assertSame('Sekar', $business->whatsapp_label);
        $this->assertSame('6282226256525', $business->whatsapp_alt);
        $this->assertSame('Bu Agung', $business->whatsapp_alt_label);
    }

    /**
     * Slug berganti, tapi teks contohnya tersimpan di bawah slug LAMA.
     *
     * `isSafeToReplace` dulu mencocokkan hanya terhadap
     * `SampleContent::businesses()[$slug]`. Begitu Lumintu jadi J-Land
     * beserta slug-nya, pencarian itu tidak menemukan apa pun — dan nomor
     * `6281277778888` serta alamat "Jl. Contoh Damai No. 3, Bekasi"
     * bertahan di halaman yang sudah berganti nama, tanpa satu pun error.
     */
    public function test_jland_invented_details_are_replaced_despite_the_slug_change(): void
    {
        $this->seedOverSamples();

        $business = $this->jland();

        $this->assertNotSame('6281277778888', $business->whatsapp);
        $this->assertNotSame('lumintuproperty', $business->instagram);
        $this->assertNotSame('Jl. Contoh Damai No. 3, Bekasi', $business->address);

        $this->assertSame('magerpindah.id', $business->instagram);
        $this->assertSame('Jalan Rorojonggrang VIII Nomor 08', $business->address);
        $this->assertSame('24 jam', $business->business_hours);
        $this->assertNull($business->tagline);
    }

    public function test_jland_sample_units_are_swept(): void
    {
        // Lima "Unit Tersedia" karangan lengkap dengan tarif bulanan —
        // client belum mengirim daftar unit sungguhan.
        $this->seedOverSamples();

        $this->assertSame(0, $this->jland()->catalogItems()->count());
    }

    public function test_the_old_lumintu_slug_no_longer_exists(): void
    {
        $this->seedFresh();

        $this->assertFalse(
            Business::where('slug', 'lumintu-property')->exists(),
        );
    }

    public function test_the_parent_lists_jland_instagram(): void
    {
        // Materi induk menulis "@j-landproperty" yang tidak valid; materi
        // J-Land memastikan akunnya bernama magerpindah.id.
        $this->seedFresh();

        $usernames = array_column($this->parent()->unit_socials ?? [], 'username');

        $this->assertContains('magerpindah.id', $usernames);
        $this->assertNotContains('j-landproperty', $usernames);
    }

    // ------------------------------------------- PT. Ayodya Utama Logistic

    private function ayodya(): Business
    {
        return Business::where('slug', 'ayodya-logistic')->firstOrFail();
    }

    public function test_ayodya_profile_is_filled(): void
    {
        $this->seedFresh();

        $business = $this->ayodya();

        $this->assertSame(
            'International Freight Forwarding & Global Logistic',
            $business->tagline,
        );
        $this->assertCount(3, $business->mission ?? []);
        $this->assertCount(4, $business->featured_services ?? []);
        $this->assertCount(3, $business->services ?? []);
        $this->assertSame(ClientContent::AYODYA_LOGO, $business->logo_path);
    }

    /**
     * Materi client memakai DUA ejaan: "AYODYA" untuk nama perusahaan,
     * "ayudyalogistic" untuk web dan email.
     *
     * Keduanya harus disalin apa adanya. Membetulkan ejaan di alamat web
     * atau email justru merusak: tautannya jadi mengarah ke domain yang
     * tidak ada, dan email tidak sampai ke siapa pun.
     */
    public function test_ayodya_keeps_the_client_spelling_of_its_web_and_email(): void
    {
        $this->seedFresh();

        $note = (string) $this->ayodya()->contact_note;

        $this->assertStringContainsString('ayudyalogistic@gmail.com', $note);
        $this->assertStringContainsString('www.ayudyalogistic.com', $note);
        $this->assertStringContainsString('johansetiady@yahoo.com', $note);
    }

    public function test_ayodya_office_phone_and_fax_are_not_lost(): void
    {
        // Telepon kantor dan fax tidak punya kolom sendiri di skema ini.
        // Digabung ke catatan kontak supaya tidak ada informasi client yang
        // hilang diam-diam.
        $this->seedFresh();

        $note = (string) $this->ayodya()->contact_note;

        $this->assertStringContainsString('024-3511609', $note);
        $this->assertStringContainsString('024-3511610', $note);
    }

    public function test_ayodya_invented_details_are_replaced(): void
    {
        $this->seedOverSamples();

        $business = $this->ayodya();

        $this->assertNotSame('6281255556666', $business->whatsapp);
        $this->assertSame('62811276265', $business->whatsapp);
        $this->assertSame('Johan Setiadi', $business->whatsapp_label);

        // Instagram "ayodyalogistic" karangan — materi client tidak menyebut
        // media sosial sama sekali, dan daftar akun di halaman induk pun
        // tidak memuat Ayodya.
        $this->assertNull($business->instagram);
        $this->assertNull($business->business_hours);

        $this->assertSame(
            'Ruko Kuala Mas III No. 1A, Tanah Mas, Semarang',
            $business->address,
        );
    }

    public function test_ayodya_sample_services_are_swept(): void
    {
        // Lima layanan karangan, dua di antaranya bertarif (Sewa Armada
        // Harian Rp850.000). Client belum mengirim daftar tarif.
        $this->seedOverSamples();

        $this->assertSame(0, $this->ayodya()->catalogItems()->count());
    }

    // -------------------------------------------------- cakupan seluruhnya

    /**
     * Penjaga terakhir: SETIAP entitas harus punya materi asli.
     *
     * Sejak 22 Agustus keenamnya sudah masuk. Test ini yang menahan
     * kemunduran — kalau suatu saat ada entitas baru ditambahkan ke
     * BusinessSeeder tanpa materi, halamannya akan tayang berisi karangan
     * atau kosong, dan tidak ada yang mengingatkan.
     */
    public function test_every_business_now_has_client_material(): void
    {
        $this->seedFresh();

        $tanpaMateri = Business::query()
            ->whereNotIn('slug', array_keys(ClientContent::businesses()))
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $tanpaMateri,
            'Belum punya materi asli: '.implode(', ', $tanpaMateri),
        );
    }

    /**
     * SETIAP entitas harus punya logo yang terpasang.
     *
     * Sejak 23 Agustus 2026 keenamnya sudah punya — J-Land Property yang
     * terakhir. Sebelumnya test ini mengecualikannya secara eksplisit;
     * pengecualian itu dihapus begitu logonya datang, dan justru test ini
     * yang mengingatkan bahwa daftarnya sudah tidak berlaku.
     */
    public function test_every_logo_the_client_sent_is_attached(): void
    {
        $this->seedFresh();

        $belumTerpasang = [];

        foreach (ClientContent::logos() as $slug => $path) {
            $terpasang = Business::where('slug', $slug)->value('logo_path');

            if ($terpasang !== $path) {
                $belumTerpasang[] = $slug;
            }
        }

        $this->assertSame(
            [],
            $belumTerpasang,
            'Logonya ada di ClientContent tapi tidak terpasang: '
                .implode(', ', $belumTerpasang),
        );

        // Tidak boleh ada satu pun yang tanpa logo. Kalau anak usaha baru
        // ditambahkan tanpa logo, di sinilah ketahuannya.
        $tanpaLogo = Business::query()
            ->whereNull('logo_path')
            ->pluck('slug')
            ->all();

        $this->assertSame(
            [],
            $tanpaLogo,
            'Belum punya logo: '.implode(', ', $tanpaLogo),
        );
    }

    /**
     * Tidak boleh ada satu pun baris data contoh yang tersisa.
     *
     * Keenam entitas sudah punya materi asli, jadi tidak ada lagi alasan
     * satu pun item bertanda contoh berdiri di database.
     */
    public function test_no_sample_catalog_items_survive_anywhere(): void
    {
        $this->seedOverSamples();

        $this->assertSame(
            0,
            CatalogItem::withTrashed()
                ->where('category', CatalogItem::SAMPLE_MARKER)
                ->count(),
        );
    }

    // ---------------------------------------- seeder contoh tidak mengganggu

    public function test_the_sample_seeder_skips_businesses_that_have_client_material(): void
    {
        // Urutan sebaliknya: materi asli sudah masuk, lalu seseorang
        // menjalankan seeder contoh. Produk karangan tidak boleh kembali.
        $this->seedFresh();

        $this->seed(SampleContentSeeder::class);

        $business = $this->sweetness();

        $this->assertCount(2, $business->catalogItems);
        $this->assertSame(
            ClientContent::businesses()['sweetness-things']['description'],
            $business->description,
        );
        $this->assertNull($business->tagline);
    }
}
