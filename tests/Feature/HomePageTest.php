<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Halaman induk J Corp — etalase (spec §3, §4).
 *
 * Yang paling penting dijaga: kartu anak usaha HANYA menampilkan yang sudah
 * diterbitkan. Kalau yang belum terbit ikut muncul di sini, keberadaannya
 * terungkap — padahal halamannya sengaja dibuat 404 untuk menyembunyikannya.
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private function parent(array $attributes = []): Business
    {
        return Business::factory()->parent()->create([
            'slug' => 'jcorp',
            'name' => 'J Corp',
            'description' => null,
            'whatsapp' => null,
            ...$attributes,
        ]);
    }

    public function test_the_home_page_renders(): void
    {
        $this->parent();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('home')
                ->where('parent.name', 'J Corp')
            );
    }

    public function test_the_home_page_preloads_the_parent_logo_with_high_priority(): void
    {
        $this->parent([
            'logo_path' => 'images/brand/jcorp-logo-600.webp',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('rel="preload" as="image"', false)
            ->assertSee('jcorp-logo-600.webp" fetchpriority="high"', false);
    }

    public function test_the_vision_and_mission_section_is_absent_by_default(): void
    {
        $this->parent();

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('parent.vision', null)
                ->where('parent.mission', null)
            );
    }

    public function test_the_vision_and_mission_section_appears_when_filled(): void
    {
        $this->parent([
            'vision' => 'Menjadi grup usaha keluarga yang tumbuh berkelanjutan.',
            'mission' => ['Menghadirkan produk berkualitas.', 'Melayani dengan ramah.'],
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('parent.vision', 'Menjadi grup usaha keluarga yang tumbuh berkelanjutan.')
                ->has('parent.mission', 2)
            );
    }

    public function test_an_empty_mission_list_is_sent_as_null(): void
    {
        // Array kosong bernilai truthy di JavaScript — kalau lolos, judul
        // sectionnya dirender tanpa satu pun isi di bawahnya.
        $this->parent(['mission' => []]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('parent.mission', null));
    }

    // ------------------------------------------------------ hubungi kami

    public function test_unit_socials_reach_the_contact_section(): void
    {
        $this->parent([
            'unit_socials' => [
                ['label' => 'Sweetness Things', 'username' => 'sweetnessthings._'],
                ['label' => 'ngelash.id', 'username' => 'ngelash.id'],
            ],
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->has('contact.unit_socials', 2)
                ->where('contact.unit_socials.0.username', 'sweetnessthings._')
            );
    }

    public function test_the_contact_section_survives_on_unit_socials_alone(): void
    {
        // Keadaan induk sekarang: tidak punya nomor, email, atau alamat —
        // yang ada hanya kalimat pembuka dan daftar akun unit usaha. Itu
        // sudah cara menghubungi yang sah, jadi sectionnya harus tetap ada.
        $this->parent([
            'whatsapp' => null,
            'instagram' => null,
            'address' => null,
            'business_hours' => null,
            'contact_note' => 'Silakan hubungi kami.',
            'unit_socials' => [
                ['label' => 'Sweetness Things', 'username' => 'sweetnessthings._'],
            ],
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('contact.contact_note', 'Silakan hubungi kami.')
                ->has('contact.unit_socials', 1)
                ->missing('contact.whatsapp')
            );
    }

    public function test_the_contact_section_is_absent_when_there_is_nothing_at_all(): void
    {
        $this->parent([
            'whatsapp' => null,
            'instagram' => null,
            'address' => null,
            'business_hours' => null,
            'contact_note' => null,
            'unit_socials' => null,
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('contact', null));
    }

    public function test_an_empty_unit_socials_list_does_not_create_a_section(): void
    {
        // Array kosong truthy di JavaScript — kalau lolos, judul "Instagram
        // unit usaha" dirender tanpa satu pun akun di bawahnya.
        $this->parent([
            'whatsapp' => null,
            'instagram' => null,
            'address' => null,
            'business_hours' => null,
            'contact_note' => null,
            'unit_socials' => [],
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('contact', null));
    }

    // ------------------------------------------------- kartu anak usaha

    public function test_only_published_subsidiaries_appear_as_cards(): void
    {
        $this->parent();
        Business::factory()->create(['name' => 'Sudah Terbit']);
        Business::factory()->unpublished()->create(['name' => 'Belum Terbit']);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->has('subsidiaries', 1)
                ->where('subsidiaries.0.name', 'Sudah Terbit')
            );
    }

    public function test_the_parent_never_appears_among_its_own_cards(): void
    {
        $this->parent();
        Business::factory()->create(['name' => 'Anak Usaha']);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->has('subsidiaries', 1)
                ->where('subsidiaries.0.name', 'Anak Usaha')
            );
    }

    public function test_cards_follow_sort_order(): void
    {
        $this->parent();
        Business::factory()->create(['name' => 'Ketiga', 'sort_order' => 3]);
        Business::factory()->create(['name' => 'Pertama', 'sort_order' => 1]);
        Business::factory()->create(['name' => 'Kedua', 'sort_order' => 2]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('subsidiaries.0.name', 'Pertama')
                ->where('subsidiaries.1.name', 'Kedua')
                ->where('subsidiaries.2.name', 'Ketiga')
            );
    }

    public function test_known_subsidiaries_carry_their_home_sector_labels(): void
    {
        $this->parent();
        Business::factory()->create(['slug' => 'sweetness-things', 'sort_order' => 1]);
        Business::factory()->create(['slug' => 'nails-by-me', 'sort_order' => 2]);
        Business::factory()->create(['slug' => 'ngelash', 'sort_order' => 3]);
        Business::factory()->create(['slug' => 'ayodya-logistic', 'sort_order' => 4]);
        Business::factory()->create(['slug' => 'j-land-property', 'sort_order' => 5]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('subsidiaries.0.sector_label', 'Kuliner')
                ->where('subsidiaries.1.sector_label', 'Kecantikan')
                ->where('subsidiaries.2.sector_label', 'Kecantikan')
                ->where('subsidiaries.3.sector_label', 'Logistik')
                ->where('subsidiaries.4.sector_label', 'Properti')
            );
    }

    public function test_three_cards_receive_their_approved_english_tagline_fallbacks(): void
    {
        $this->parent();
        Business::factory()->create([
            'slug' => 'sweetness-things',
            'tagline' => null,
            'sort_order' => 1,
        ]);
        Business::factory()->create([
            'slug' => 'nails-by-me',
            'tagline' => null,
            'sort_order' => 2,
        ]);
        Business::factory()->create([
            'slug' => 'j-land-property',
            'tagline' => null,
            'sort_order' => 3,
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where(
                    'subsidiaries.0.tagline',
                    'Homemade Desserts for Every Little Celebration.',
                )
                ->where(
                    'subsidiaries.1.tagline',
                    'Beautiful, Neat, and Long-Lasting Nail Art.',
                )
                ->where(
                    'subsidiaries.2.tagline',
                    'Safe, Comfortable, and Trusted Property Solutions.',
                )
            );
    }

    public function test_an_admin_tagline_still_wins_over_the_home_card_fallback(): void
    {
        $this->parent();
        Business::factory()->create([
            'slug' => 'sweetness-things',
            'tagline' => 'Tagline dari admin',
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where(
                'subsidiaries.0.tagline',
                'Tagline dari admin',
            ));
    }

    public function test_soft_deleted_subsidiaries_never_appear(): void
    {
        $this->parent();
        $business = Business::factory()->create();
        $business->delete();

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->has('subsidiaries', 0));
    }

    public function test_the_showcase_survives_when_nothing_is_published_yet(): void
    {
        // Halaman tetap dirender — hanya bagian etalasenya yang kosong.
        // Ini kondisi sebelum anak usaha mana pun diterbitkan.
        $this->parent();
        Business::factory()->unpublished()->count(3)->create();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('subsidiaries', 0));
    }

    // ------------------------------------------------------------ inisial

    public function test_cards_carry_initials_for_businesses_without_a_logo(): void
    {
        $this->parent();
        Business::factory()->create(['name' => 'Sweetness Things']);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('subsidiaries.0.initials', 'ST'));
    }

    public function test_company_prefixes_are_ignored_when_building_initials(): void
    {
        // "PT. Ayodya Utama Logistic" seharusnya AU, bukan PA.
        $this->parent();
        Business::factory()->create(['name' => 'PT. Ayodya Utama Logistic']);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('subsidiaries.0.initials', 'AU'));
    }

    public function test_a_single_word_name_uses_its_first_two_letters(): void
    {
        $this->parent();
        Business::factory()->create(['name' => 'ngelash']);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('subsidiaries.0.initials', 'NG'));
    }

    // ------------------------------------------- aturan section kosong

    public function test_the_about_section_is_absent_without_a_description(): void
    {
        $this->parent(['description' => null]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('parent.description', null));
    }

    public function test_the_contact_section_is_absent_when_every_field_is_empty(): void
    {
        $this->parent([
            'whatsapp' => null,
            'instagram' => null,
            'address' => null,
            'business_hours' => null,
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('contact', null));
    }

    public function test_the_contact_section_only_carries_fields_that_are_filled(): void
    {
        $this->parent([
            'whatsapp' => '628111222333',
            'instagram' => null,
            'address' => 'Jl. Induk No. 1',
        ]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('contact.whatsapp', '628111222333')
                ->where('contact.address', 'Jl. Induk No. 1')
                ->missing('contact.instagram')
            );
    }

    // ----------------------------------------------------------- lain-lain

    public function test_the_parent_is_not_reachable_through_the_slug_route(): void
    {
        // Induk punya route sendiri di "/" — /jcorp harus 404, bukan
        // menampilkan halaman yang sama lewat dua alamat berbeda.
        $this->parent();

        $this->get('/jcorp')->assertNotFound();
    }

    public function test_the_home_page_is_not_marked_noindex(): void
    {
        $this->parent();

        $this->get('/')->assertHeaderMissing('X-Robots-Tag');
    }
}
