<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Inti Fase 1 (spec §6 dan §11).
 *
 * Ditulis dari sisi penyerang: masuk sebagai admin anak usaha A, lalu kirim
 * permintaan langsung ke ID milik anak usaha B dengan mengubah angka di
 * alamat. Semuanya harus 403 — menyembunyikan tombol di tampilan tidak
 * dianggap pengamanan.
 */
class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    private Business $mine;

    private Business $theirs;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // withPortfolio() karena sebagian test di sini menyentuh halaman
        // portfolio — yang sakelarnya mati mengembalikan 404.
        $this->mine = Business::factory()->withPortfolio()->create(['name' => 'Punya Saya']);
        $this->theirs = Business::factory()->withPortfolio()->create(['name' => 'Punya Orang Lain']);
        $this->admin = User::factory()->forBusiness($this->mine)->create();
    }

    // ---------------------------------------------------------------- katalog

    public function test_admin_cannot_update_catalog_item_of_another_business(): void
    {
        $victim = CatalogItem::factory()->for($this->theirs)->create(['name' => 'Item Orang Lain']);

        $this->actingAs($this->admin)
            ->put(route('panel.catalog.update', $victim), [
                'name' => 'Dibajak',
                'is_available' => true,
                'sort_order' => 0,
            ])
            ->assertForbidden();

        $this->assertSame('Item Orang Lain', $victim->refresh()->name);
    }

    public function test_admin_cannot_delete_catalog_item_of_another_business(): void
    {
        $victim = CatalogItem::factory()->for($this->theirs)->create();

        $this->actingAs($this->admin)
            ->delete(route('panel.catalog.destroy', $victim))
            ->assertForbidden();

        $this->assertNotSoftDeleted($victim);
    }

    public function test_admin_can_update_own_catalog_item(): void
    {
        $item = CatalogItem::factory()->for($this->mine)->create(['name' => 'Sebelum']);

        $this->actingAs($this->admin)
            ->put(route('panel.catalog.update', $item), [
                'name' => 'Sesudah',
                'is_available' => true,
                'sort_order' => 0,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Sesudah', $item->refresh()->name);
    }

    public function test_admin_can_delete_own_catalog_item(): void
    {
        $item = CatalogItem::factory()->for($this->mine)->create();

        $this->actingAs($this->admin)
            ->delete(route('panel.catalog.destroy', $item))
            ->assertSessionHasNoErrors();

        // Soft delete: barisnya ditandai terhapus, bukan dimusnahkan (spec §4).
        $this->assertSoftDeleted($item);
    }

    public function test_new_catalog_item_belongs_to_the_admins_own_business(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Baru',
                'is_available' => true,
                'sort_order' => 0,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('catalog_items', [
            'name' => 'Item Baru',
            'business_id' => $this->mine->id,
        ]);
    }

    public function test_business_id_in_the_request_body_cannot_move_an_item_elsewhere(): void
    {
        // `business_id` tidak fillable — kalau sampai ikut terisi, seorang
        // admin bisa menitipkan itemnya ke anak usaha lain hanya dengan
        // menambah satu field di request.
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Selundupan',
                'is_available' => true,
                'sort_order' => 0,
                'business_id' => $this->theirs->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('catalog_items', [
            'name' => 'Item Selundupan',
            'business_id' => $this->mine->id,
        ]);
    }

    // -------------------------------------------------------------- portfolio

    public function test_admin_cannot_update_portfolio_item_of_another_business(): void
    {
        $victim = PortfolioItem::factory()->for($this->theirs)->create(['caption' => 'Foto Orang Lain']);

        $this->actingAs($this->admin)
            ->put(route('panel.portfolio.update', $victim), [
                'image_path' => 'portfolio/dibajak.webp',
                'sort_order' => 0,
            ])
            ->assertForbidden();

        $this->assertSame('Foto Orang Lain', $victim->refresh()->caption);
    }

    public function test_admin_cannot_delete_portfolio_item_of_another_business(): void
    {
        $victim = PortfolioItem::factory()->for($this->theirs)->create();

        $this->actingAs($this->admin)
            ->delete(route('panel.portfolio.destroy', $victim))
            ->assertForbidden();

        $this->assertNotSoftDeleted($victim);
    }

    // ------------------------------------------------------------- sisi baca

    public function test_admin_only_receives_their_own_catalog_items(): void
    {
        CatalogItem::factory()->for($this->mine)->create(['name' => 'Punya Saya']);
        CatalogItem::factory()->for($this->theirs)->create(['name' => 'Punya Orang Lain']);

        $this->actingAs($this->admin)
            ->get(route('panel.catalog.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('panel/catalog/index')
                ->has('items', 1)
                ->where('items.0.name', 'Punya Saya')
            );
    }

    public function test_admin_only_receives_their_own_portfolio_items(): void
    {
        PortfolioItem::factory()->for($this->mine)->create(['caption' => 'Punya Saya']);
        PortfolioItem::factory()->for($this->theirs)->create(['caption' => 'Punya Orang Lain']);

        $this->actingAs($this->admin)
            ->get(route('panel.portfolio.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('panel/portfolio/index')
                ->has('items', 1)
                ->where('items.0.caption', 'Punya Saya')
            );
    }

    public function test_admin_is_never_sent_the_list_of_other_businesses(): void
    {
        $this->actingAs($this->admin)
            ->get(route('panel.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('switchableBusinesses', null)
                ->where('business.name', 'Punya Saya')
                ->where('auth.switchableBusinesses', null)
                ->where('auth.business.slug', $this->mine->slug)
            );
    }

    public function test_super_admin_receives_the_global_business_switcher_context(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('panel.dashboard', ['business' => $this->theirs->slug]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.business.slug', $this->theirs->slug)
                ->has('auth.switchableBusinesses', 2)
                ->where('auth.switchableBusinesses', fn ($businesses) => $businesses
                    ->pluck('slug')
                    ->sort()
                    ->values()
                    ->all() === collect([$this->mine->slug, $this->theirs->slug])
                    ->sort()
                    ->values()
                    ->all())
            );
    }

    public function test_admin_cannot_switch_context_by_adding_a_query_parameter(): void
    {
        // Parameter `?business=` hanya berlaku untuk super-admin. Untuk
        // business_admin parameternya diabaikan, bukan dituruti.
        $this->actingAs($this->admin)
            ->get(route('panel.dashboard', ['business' => $this->theirs->slug]))
            ->assertInertia(fn (Assert $page) => $page->where('business.name', 'Punya Saya'));
    }

    public function test_admin_cannot_edit_contact_info_of_another_business_via_query_parameter(): void
    {
        $this->actingAs($this->admin)
            ->put(route('panel.profile.update', ['business' => $this->theirs->slug]), [
                'catalog_label' => 'Dibajak',
                'portfolio_label' => 'Dibajak',
            ])
            ->assertSessionHasNoErrors();

        // Yang berubah anak usahanya sendiri, bukan milik orang lain.
        $this->assertSame('Dibajak', $this->mine->refresh()->catalog_label);
        $this->assertNotSame('Dibajak', $this->theirs->refresh()->catalog_label);
    }

    // ------------------------------------------------------------ super-admin

    public function test_super_admin_context_survives_moving_between_menus(): void
    {
        // Konteks disimpan di sesi. Tanpa itu, super-admin yang memilih
        // Sweetness di dashboard akan mendapati halaman katalog kembali
        // menampilkan J Corp.
        Business::factory()->parent()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('panel.dashboard', ['business' => $this->theirs->slug]))
            ->assertOk();

        // Menu berikutnya dibuka TANPA parameter.
        $this->actingAs($superAdmin)
            ->get(route('panel.catalog.index'))
            ->assertInertia(fn (Assert $page) => $page->where('business.name', 'Punya Orang Lain'));
    }

    public function test_a_business_admin_context_is_never_stored_in_the_session(): void
    {
        // Business_admin tidak boleh bisa menggeser konteksnya sendiri lewat
        // sesi — parameternya diabaikan, jadi tidak ada yang tersimpan.
        $this->actingAs($this->admin)
            ->get(route('panel.dashboard', ['business' => $this->theirs->slug]))
            ->assertOk();

        $this->assertNull(session('panel.business'));

        $this->actingAs($this->admin)
            ->get(route('panel.catalog.index'))
            ->assertInertia(fn (Assert $page) => $page->where('business.name', 'Punya Saya'));
    }

    public function test_super_admin_can_touch_any_business(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $item = CatalogItem::factory()->for($this->theirs)->create();

        $this->actingAs($superAdmin)
            ->delete(route('panel.catalog.destroy', $item))
            ->assertSessionHasNoErrors();

        $this->assertSoftDeleted($item);
    }

    public function test_business_admin_without_a_business_is_refused(): void
    {
        // Data rusak: peran business_admin tapi tidak terhubung ke anak usaha
        // mana pun. Menolaknya lebih aman daripada menebak.
        $orphan = User::factory()->create(['business_id' => null]);

        $this->actingAs($orphan)
            ->get(route('panel.dashboard'))
            ->assertForbidden();
    }
}
