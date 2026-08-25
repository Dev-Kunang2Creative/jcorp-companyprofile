<?php

namespace Tests\Feature\Console;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Database\Seeders\BusinessSeeder;
use Database\Seeders\ClientContent;
use Database\Seeders\ClientContentSeeder;
use Database\Seeders\SampleContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perintah pembersih data contoh.
 *
 * Yang paling penting dijaga: perintah ini TIDAK BOLEH menghapus tulisan
 * sungguhan. Data contoh dikenali dari penanda (item katalog) atau dari
 * kecocokan teks (tagline & cerita) — begitu admin menimpanya lewat panel,
 * tulisannya tidak lagi cocok dan aman.
 */
class ClearSamplesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_marked_catalog_items(): void
    {
        $business = Business::factory()->create();
        CatalogItem::factory()->for($business)->count(3)->create([
            'category' => CatalogItem::SAMPLE_MARKER,
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(0, CatalogItem::withTrashed()->count());
    }

    public function test_it_leaves_real_catalog_items_alone(): void
    {
        $business = Business::factory()->create();
        CatalogItem::factory()->for($business)->create([
            'name' => 'Item Sungguhan',
            'category' => 'Dessert Box',
        ]);
        CatalogItem::factory()->for($business)->create([
            'category' => CatalogItem::SAMPLE_MARKER,
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(1, CatalogItem::count());
        $this->assertDatabaseHas('catalog_items', ['name' => 'Item Sungguhan']);
    }

    /**
     * Slug pembersihan teks memakai `lumintu-property`, BUKAN anak usaha
     * yang sedang tayang.
     *
     * Sejak 22 Agustus keenam entitas punya materi asli dari client, dan
     * `jcorp:clear-samples` sengaja MELEWATI anak usaha yang ada di
     * ClientContent — sebagian nilainya kebetulan sama di kedua berkas
     * (label "Layanan Kami" milik Ayodya, misalnya), jadi tanpa pengecualian
     * itu perintahnya akan mengembalikan sebutan pilihan client ke bawaan.
     *
     * `lumintu-property` satu-satunya slug yang tersisa di SampleContent
     * tanpa padanan di ClientContent, jadi jalur pembersih teks masih bisa
     * diuji lewat situ.
     */
    private const SLUG_CONTOH = 'lumintu-property';

    public function test_it_clears_sample_taglines(): void
    {
        $sample = SampleContent::businesses()[self::SLUG_CONTOH];
        Business::factory()->create([
            'slug' => self::SLUG_CONTOH,
            'tagline' => $sample['tagline'],
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertNull(Business::where('slug', self::SLUG_CONTOH)->value('tagline'));
    }

    public function test_it_never_touches_a_tagline_the_admin_has_rewritten(): void
    {
        // Inti ujiannya. Kalau perintah ini menghapus tulisan admin, kerja
        // mengisi konten hilang tanpa bisa dikembalikan.
        Business::factory()->create([
            'slug' => 'ayodya-logistic',
            'tagline' => 'Tagline asli yang ditulis admin sendiri',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(
            'Tagline asli yang ditulis admin sendiri',
            Business::where('slug', 'ayodya-logistic')->value('tagline'),
        );
    }

    public function test_it_does_not_change_the_publish_switch(): void
    {
        // Menyembunyikan profil adalah keputusan sadar super-admin, bukan
        // efek samping membersihkan data contoh.
        $sample = SampleContent::businesses()['ayodya-logistic'];
        $business = Business::factory()->create([
            'slug' => 'ayodya-logistic',
            'tagline' => $sample['tagline'],
        ]);

        $business->is_published = true;
        $business->save();

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertTrue($business->refresh()->is_published);
    }

    public function test_it_reports_when_there_is_nothing_to_clean(): void
    {
        Business::factory()->create(['slug' => 'ayodya-logistic', 'tagline' => 'Tulisan sendiri']);

        $this->artisan('jcorp:clear-samples --force')
            ->expectsOutputToContain('Tidak ada data contoh yang tersisa.')
            ->assertSuccessful();
    }

    public function test_it_clears_sample_contact_details(): void
    {
        // Data contoh mengisi nomor WA dan alamat karangan juga. Kalau ini
        // tertinggal saat tayang, pengunjung menghubungi nomor yang salah.
        $sample = SampleContent::businesses()[self::SLUG_CONTOH];
        Business::factory()->create([
            'slug' => self::SLUG_CONTOH,
            'whatsapp' => $sample['whatsapp'],
            'address' => $sample['address'],
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $business = Business::where('slug', self::SLUG_CONTOH)->firstOrFail();

        $this->assertNull($business->whatsapp);
        $this->assertNull($business->address);
    }

    public function test_it_never_touches_a_phone_number_the_admin_has_entered(): void
    {
        Business::factory()->create([
            'slug' => 'ayodya-logistic',
            'whatsapp' => '628999888777',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(
            '628999888777',
            Business::where('slug', 'ayodya-logistic')->value('whatsapp'),
        );
    }

    public function test_it_deletes_placeholder_portfolio_photos(): void
    {
        $business = Business::factory()->create(['slug' => 'ayodya-logistic']);
        PortfolioItem::factory()->for($business)->create([
            'image_path' => 'images/placeholder/nail-1.webp',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(0, PortfolioItem::withTrashed()->count());
    }

    public function test_it_leaves_real_uploaded_photos_alone(): void
    {
        // Foto yang diunggah admin tersimpan di folder slug anak usaha,
        // bukan di images/placeholder/.
        $business = Business::factory()->create(['slug' => 'ayodya-logistic']);
        PortfolioItem::factory()->for($business)->create([
            'image_path' => 'nails-by-me/foto-asli.webp',
            'caption' => 'Foto sungguhan',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertDatabaseHas('portfolio_items', ['caption' => 'Foto sungguhan']);
    }

    public function test_it_restores_section_labels_to_their_default(): void
    {
        // Label section tidak boleh dikosongkan — halaman publik butuh
        // sebutan untuk sectionnya. Dikembalikan ke nilai bawaan migrasi.
        //
        // `portfolio_label` disetel manual, bukan diambil dari SampleContent:
        // tidak semua anak usaha punya kunci itu di sana — yang tidak
        // memamerkan hasil kerja memang tidak perlu label portfolio.
        $sample = SampleContent::businesses()[self::SLUG_CONTOH];
        Business::factory()->create([
            'slug' => self::SLUG_CONTOH,
            'catalog_label' => $sample['catalog_label'],
            'portfolio_label' => 'Hasil Kerja',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $business = Business::where('slug', self::SLUG_CONTOH)->firstOrFail();

        $this->assertSame('Katalog', $business->catalog_label);

        // Label portfolio TIDAK dikembalikan: nilainya bukan teks contoh,
        // jadi diperlakukan sebagai tulisan admin — dan itu memang aturannya.
        $this->assertSame('Hasil Kerja', $business->portfolio_label);
    }

    public function test_it_keeps_the_sweetness_logo(): void
    {
        // Logo Sweetness berkas ASLI dari client, bukan data contoh —
        // seeder memang yang menghubungkannya, tapi tidak boleh ikut hilang.
        $business = Business::factory()->create([
            'slug' => 'sweetness-things',
            'logo_path' => SampleContent::SWEETNESS_LOGO,
            'tagline' => 'Tagline contoh apa pun',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(
            SampleContent::SWEETNESS_LOGO,
            $business->refresh()->logo_path,
        );
    }

    // ------------------------------------------------ materi asli dari client

    /**
     * Penjaga terpenting sejak materi asli client masuk.
     *
     * Perintah ini dijalankan justru MENJELANG tayang — saat data contoh
     * dibersihkan. Kalau ia ikut menghapus materi asli, kerugiannya terjadi
     * di waktu yang paling buruk: halaman jadi kosong tepat ketika client
     * mulai membagikan alamatnya.
     *
     * Yang menjaganya: ClientContent berkas terpisah, dan perintah ini hanya
     * mengenali teks dari SampleContent.
     */
    public function test_it_never_touches_material_that_came_from_the_client(): void
    {
        $this->seed(BusinessSeeder::class);
        $this->seed(ClientContentSeeder::class);

        // Data contoh, supaya perintahnya benar-benar punya sesuatu untuk
        // dikerjakan dan tidak berhenti lebih awal.
        //
        // Memakai SLUG_CONTOH — anak usaha yang tayang tidak bisa dipakai
        // lagi di sini: keenamnya sudah punya materi asli, dan perintah ini
        // sengaja melewati semuanya.
        $other = Business::factory()->create([
            'slug' => self::SLUG_CONTOH,
            'tagline' => SampleContent::businesses()[self::SLUG_CONTOH]['tagline'],
        ]);

        CatalogItem::factory()->for($other)->count(2)->create([
            'category' => CatalogItem::SAMPLE_MARKER,
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $sweetness = Business::where('slug', 'sweetness-things')->firstOrFail();
        $client = ClientContent::businesses()['sweetness-things'];

        $this->assertSame($client['description'], $sweetness->description);
        $this->assertSame($client['vision'], $sweetness->vision);
        $this->assertSame($client['mission'], $sweetness->mission);
        $this->assertSame($client['whatsapp'], $sweetness->whatsapp);
        $this->assertSame($client['whatsapp_alt'], $sweetness->whatsapp_alt);
        $this->assertSame($client['address'], $sweetness->address);
        $this->assertSame($client['contact_note'], $sweetness->contact_note);
        $this->assertSame($client['catalog_label'], $sweetness->catalog_label);

        // Produk aslinya juga harus utuh — dua, bukan nol.
        $this->assertCount(2, $sweetness->catalogItems);

        // Sementara data contoh milik anak usaha lain memang tersapu.
        $this->assertNull($other->refresh()->tagline);
        $this->assertSame(0, $other->catalogItems()->count());
    }
}
