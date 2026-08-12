<?php

namespace Tests\Feature\Console;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
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

    public function test_it_clears_sample_taglines(): void
    {
        $sample = SampleContent::businesses()['ngelash'];
        Business::factory()->create([
            'slug' => 'ngelash',
            'tagline' => $sample['tagline'],
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertNull(Business::where('slug', 'ngelash')->value('tagline'));
    }

    public function test_it_never_touches_a_tagline_the_admin_has_rewritten(): void
    {
        // Inti ujiannya. Kalau perintah ini menghapus tulisan admin, kerja
        // mengisi konten hilang tanpa bisa dikembalikan.
        Business::factory()->create([
            'slug' => 'ngelash',
            'tagline' => 'Tagline asli yang ditulis admin sendiri',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(
            'Tagline asli yang ditulis admin sendiri',
            Business::where('slug', 'ngelash')->value('tagline'),
        );
    }

    public function test_it_does_not_change_the_publish_switch(): void
    {
        // Menyembunyikan profil adalah keputusan sadar super-admin, bukan
        // efek samping membersihkan data contoh.
        $sample = SampleContent::businesses()['ngelash'];
        $business = Business::factory()->create([
            'slug' => 'ngelash',
            'tagline' => $sample['tagline'],
        ]);

        $business->is_published = true;
        $business->save();

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertTrue($business->refresh()->is_published);
    }

    public function test_it_reports_when_there_is_nothing_to_clean(): void
    {
        Business::factory()->create(['slug' => 'ngelash', 'tagline' => 'Tulisan sendiri']);

        $this->artisan('jcorp:clear-samples --force')
            ->expectsOutputToContain('Tidak ada data contoh yang tersisa.')
            ->assertSuccessful();
    }

    public function test_it_clears_sample_contact_details(): void
    {
        // Data contoh mengisi nomor WA dan alamat karangan juga. Kalau ini
        // tertinggal saat tayang, pengunjung menghubungi nomor yang salah.
        $sample = SampleContent::businesses()['ngelash'];
        Business::factory()->create([
            'slug' => 'ngelash',
            'whatsapp' => $sample['whatsapp'],
            'address' => $sample['address'],
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $business = Business::where('slug', 'ngelash')->firstOrFail();

        $this->assertNull($business->whatsapp);
        $this->assertNull($business->address);
    }

    public function test_it_never_touches_a_phone_number_the_admin_has_entered(): void
    {
        Business::factory()->create([
            'slug' => 'ngelash',
            'whatsapp' => '628999888777',
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $this->assertSame(
            '628999888777',
            Business::where('slug', 'ngelash')->value('whatsapp'),
        );
    }

    public function test_it_deletes_placeholder_portfolio_photos(): void
    {
        $business = Business::factory()->create(['slug' => 'nails-by-me']);
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
        $business = Business::factory()->create(['slug' => 'nails-by-me']);
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
        $sample = SampleContent::businesses()['ngelash'];
        Business::factory()->create([
            'slug' => 'ngelash',
            'catalog_label' => $sample['catalog_label'],
            'portfolio_label' => $sample['portfolio_label'],
        ]);

        $this->artisan('jcorp:clear-samples --force')->assertSuccessful();

        $business = Business::where('slug', 'ngelash')->firstOrFail();

        $this->assertSame('Katalog', $business->catalog_label);
        $this->assertSame('Portfolio', $business->portfolio_label);
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
}
