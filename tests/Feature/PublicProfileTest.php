<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Halaman profil publik (spec §3, §10, §11).
 *
 * Yang dijaga di sini terutama aturan section kosong: section tanpa isi tidak
 * dirender sama sekali — bukan ditampilkan kosong, bukan diisi "coming soon".
 * Ini yang membuat website tetap layak tayang saat konten terisi sebagian.
 */
class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    private function business(array $attributes = []): Business
    {
        $business = Business::factory()->create([
            'slug' => 'sweetness-things',
            'name' => 'Sweetness Things',
            'description' => null,
            'whatsapp' => null,
            ...$attributes,
        ]);

        return $business;
    }

    // ------------------------------------------------------- akses & 404

    public function test_a_published_business_is_reachable(): void
    {
        $this->business();

        $this->get('/sweetness-things')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/business')
                ->where('business.name', 'Sweetness Things')
            );
    }

    public function test_an_unpublished_business_returns_404(): void
    {
        // Spec §4: yang belum terbit mengembalikan 404, bukan pesan "belum
        // tersedia" — supaya keberadaannya tidak terungkap.
        $this->business(['is_published' => false]);

        $this->get('/sweetness-things')->assertNotFound();
    }

    public function test_an_unknown_slug_returns_404(): void
    {
        $this->get('/tidak-ada-sama-sekali')->assertNotFound();
    }

    public function test_the_parent_business_is_not_reachable_as_a_subsidiary(): void
    {
        // J Corp punya route sendiri di "/" — tidak boleh juga muncul di /jcorp.
        Business::factory()->parent()->create(['slug' => 'jcorp', 'is_published' => true]);

        $this->get('/jcorp')->assertNotFound();
    }

    public function test_the_admin_panel_is_not_swallowed_by_the_slug_route(): void
    {
        // `/{slug}` cocok dengan hampir semua alamat satu segmen. Kalau
        // urutannya salah, /jcorp-panel akan tertangkap dan jadi 404.
        $this->business();

        $this->get('/jcorp-panel')->assertRedirect(route('login'));
    }

    // -------------------------------------------- aturan section kosong

    public function test_the_about_section_is_absent_when_there_is_no_description(): void
    {
        $this->business(['description' => null]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('business.description', null));
    }

    public function test_the_catalog_section_is_absent_when_there_are_no_items(): void
    {
        $this->business();

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('catalog', null));
    }

    public function test_the_catalog_section_is_absent_when_every_item_is_hidden(): void
    {
        // Inti aturannya (spec §3): penilaian berdasarkan apa yang benar-benar
        // TAMPIL, bukan apa yang ada di database. Item yang disembunyikan
        // sementara tidak menghitung.
        $business = $this->business();
        CatalogItem::factory()->for($business)->unavailable()->count(3)->create();

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('catalog', null));
    }

    public function test_hidden_items_are_excluded_but_the_section_survives(): void
    {
        $business = $this->business();
        CatalogItem::factory()->for($business)->create(['name' => 'Tampil']);
        CatalogItem::factory()->for($business)->unavailable()->create(['name' => 'Disembunyikan']);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->has('catalog', 1)
                ->where('catalog.0.name', 'Tampil')
            );
    }

    public function test_soft_deleted_items_never_appear(): void
    {
        $business = $this->business();
        $item = CatalogItem::factory()->for($business)->create();
        $item->delete();

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('catalog', null));
    }

    public function test_the_portfolio_section_is_absent_when_there_are_no_photos(): void
    {
        $this->business();

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('portfolio', null));
    }

    public function test_the_portfolio_section_appears_when_photos_exist(): void
    {
        $business = $this->business(['has_portfolio' => true]);
        PortfolioItem::factory()->for($business)->create(['caption' => 'Hasil kerja']);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->has('portfolio', 1)
                ->where('portfolio.0.caption', 'Hasil kerja')
            );
    }

    public function test_the_portfolio_section_is_absent_when_the_switch_is_off(): void
    {
        // Sakelar dimatikan super-admin: sectionnya hilang walaupun fotonya
        // masih tersimpan. Itu yang membuat sakelarnya bisa dinyalakan lagi
        // tanpa kehilangan apa pun.
        $business = $this->business(['has_portfolio' => false]);
        PortfolioItem::factory()->for($business)->count(3)->create();

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('portfolio', null));

        // Fotonya TIDAK ikut terhapus.
        $this->assertSame(3, $business->portfolioItems()->count());
    }

    public function test_the_contact_section_is_absent_when_every_field_is_empty(): void
    {
        $this->business([
            'whatsapp' => null,
            'instagram' => null,
            'tiktok' => null,
            'address' => null,
            'business_hours' => null,
        ]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('contact', null));
    }

    public function test_the_contact_section_only_carries_fields_that_are_filled(): void
    {
        $this->business([
            'whatsapp' => '628123456789',
            'instagram' => null,
            'address' => 'Jl. Contoh No. 1',
        ]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->where('contact.whatsapp', '628123456789')
                ->where('contact.address', 'Jl. Contoh No. 1')
                ->missing('contact.instagram')
            );
    }

    // ------------------------------------------------------------- isi

    public function test_the_price_note_and_amount_are_sent_separately(): void
    {
        // DESIGN_SYSTEM §6 menaruh keterangan di baris kecil DI ATAS angkanya,
        // jadi keduanya tidak boleh digabung jadi satu string.
        $business = $this->business();
        CatalogItem::factory()->for($business)->create([
            'price' => 150000,
            'price_note' => 'mulai dari',
        ]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->where('catalog.0.price_note', 'mulai dari')
                ->where('catalog.0.formatted_price', 'Rp'."\u{00A0}".'150.000')
            );
    }

    public function test_an_item_without_a_price_carries_no_price_at_all(): void
    {
        $business = $this->business();
        CatalogItem::factory()->for($business)->withoutPrice()->create();

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->where('catalog.0.formatted_price', null)
                ->where('catalog.0.price_note', null)
            );
    }

    public function test_a_note_without_a_price_is_still_sent(): void
    {
        // "hubungi kami" pada item yang harganya menyesuaikan — justru di
        // situ keterangannya paling berguna. Sebelumnya dibuang controller
        // kalau harganya kosong, jadi kartunya tampil tanpa informasi apa pun.
        $business = $this->business();
        CatalogItem::factory()->for($business)->create([
            'price' => null,
            'price_note' => 'hubungi kami',
        ]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->where('catalog.0.formatted_price', null)
                ->where('catalog.0.price_note', 'hubungi kami')
            );
    }

    public function test_items_carry_initials_for_the_image_fallback(): void
    {
        // Spec §10: gambar gagal dimuat menampilkan kotak berisi inisial nama
        // item, bukan ikon rusak.
        $business = $this->business();
        CatalogItem::factory()->for($business)->create(['name' => 'Dessert Box Coklat']);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('catalog.0.initials', 'DB'));
    }

    public function test_the_sample_data_marker_never_shows_up_as_a_category(): void
    {
        $business = $this->business();
        CatalogItem::factory()->for($business)->create([
            'category' => CatalogItem::SAMPLE_MARKER,
        ]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page->where('catalog.0.category', null));
    }

    public function test_items_are_ordered_by_sort_order(): void
    {
        $business = $this->business();
        CatalogItem::factory()->for($business)->create(['name' => 'Ketiga', 'sort_order' => 3]);
        CatalogItem::factory()->for($business)->create(['name' => 'Pertama', 'sort_order' => 1]);
        CatalogItem::factory()->for($business)->create(['name' => 'Kedua', 'sort_order' => 2]);

        $this->get('/sweetness-things')
            ->assertInertia(fn (Assert $page) => $page
                ->where('catalog.0.name', 'Pertama')
                ->where('catalog.1.name', 'Kedua')
                ->where('catalog.2.name', 'Ketiga')
            );
    }

    public function test_public_pages_are_not_marked_noindex(): void
    {
        // Kebalikan dari panel: halaman publik justru perlu terindeks
        // (spec §3 — SEO induk mengalir ke anak usaha).
        $this->business();

        $this->get('/sweetness-things')->assertHeaderMissing('X-Robots-Tag');
    }
}
