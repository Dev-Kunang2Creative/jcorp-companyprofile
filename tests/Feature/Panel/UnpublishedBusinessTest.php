<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Spec §4: `is_published` hanya membatasi halaman publik, bukan panel admin.
 *
 * Justru itu gunanya — konten disiapkan sampai lengkap, baru diterbitkan
 * dengan satu sakelar. Kalau admin ikut terkunci saat anak usahanya belum
 * terbit, tidak ada cara mengisi konten sebelum tayang.
 */
class UnpublishedBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_of_an_unpublished_business_can_still_reach_the_panel(): void
    {
        $business = Business::factory()->unpublished()->withPortfolio()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)->get(route('panel.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('panel.catalog.index'))->assertOk();
        $this->actingAs($admin)->get(route('panel.portfolio.index'))->assertOk();
        $this->actingAs($admin)->get(route('panel.profile.edit'))->assertOk();
    }

    public function test_admin_of_an_unpublished_business_can_still_add_content(): void
    {
        $business = Business::factory()->unpublished()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Disiapkan sebelum tayang',
                'is_available' => true,
                'sort_order' => 0,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('catalog_items', [
            'name' => 'Disiapkan sebelum tayang',
            'business_id' => $business->id,
        ]);
    }

    public function test_admin_of_an_unpublished_business_can_still_edit_contact_info(): void
    {
        $business = Business::factory()->unpublished()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), [
                'whatsapp' => '628123456789',
                'catalog_label' => 'Menu Kami',
                'portfolio_label' => 'Hasil Kerja',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('628123456789', $business->refresh()->whatsapp);
    }

    public function test_catalog_counts_include_items_hidden_from_the_public(): void
    {
        // Panel menghitung seluruh item — yang disembunyikan sementara tetap
        // terlihat oleh adminnya, tinggal dinyalakan lagi (spec §4).
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        CatalogItem::factory()->for($business)->create();
        CatalogItem::factory()->for($business)->unavailable()->create();

        $this->actingAs($admin)
            ->get(route('panel.dashboard'))
            ->assertInertia(fn ($page) => $page->where('counts.catalog', 2));
    }
}
