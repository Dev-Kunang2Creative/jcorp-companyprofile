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
