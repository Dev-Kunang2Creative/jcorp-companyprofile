<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientMediaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_client_media_import_is_complete_and_idempotent(): void
    {
        Storage::fake('public');

        $this->createSubsidiaries();

        $this->artisan('jcorp:import-client-media')->assertSuccessful();
        $this->artisan('jcorp:import-client-media')->assertSuccessful();

        $this->assertSame(11, PortfolioItem::query()->count());
        $this->assertSame(
            5,
            Business::query()->subsidiaries()->where('has_portfolio', true)->count(),
        );

        $businesses = Business::query()
            ->subsidiaries()
            ->get()
            ->keyBy('slug');
        $sweetness = $businesses['sweetness-things'];
        $ngelash = $businesses['ngelash'];

        foreach ($businesses as $business) {
            $this->assertSame(
                "{$business->slug}/profile/client-generated-hero.webp",
                $business->cover_image_path,
            );
            Storage::disk('public')->assertExists($business->cover_image_path);
        }

        $this->assertSame(4, $sweetness->portfolioItems()->count());
        $this->assertSame(7, $ngelash->portfolioItems()->count());
        $this->assertSame(2, PortfolioItem::query()->where('is_featured_on_home', true)->count());
        $this->assertTrue(
            $sweetness->portfolioItems()
                ->where('image_path', 'sweetness-things/portfolio/client-browncho.webp')
                ->firstOrFail()
                ->is_featured_on_home,
        );
        $this->assertTrue(
            $ngelash->portfolioItems()
                ->where('image_path', 'ngelash/portfolio/client-volume-cat-eye-12-13mm.webp')
                ->firstOrFail()
                ->is_featured_on_home,
        );
    }

    public function test_the_import_replaces_legacy_poster_cover_and_keeps_the_selected_home_photo(): void
    {
        Storage::fake('public');

        $businesses = $this->createSubsidiaries();
        $sweetness = $businesses['sweetness-things'];
        $legacyCover = 'sweetness-things/profile/client-cover.webp';
        $legacyPortfolio = 'sweetness-things/portfolio/client-browncho.webp';

        $sweetness->forceFill(['cover_image_path' => $legacyCover])->save();
        $sweetness->portfolioItems()->create([
            'image_path' => $legacyPortfolio,
            'caption' => 'Browncho',
            'is_featured_on_home' => true,
            'sort_order' => 1,
        ]);
        Storage::disk('public')->put($legacyCover, 'legacy-cover');
        Storage::disk('public')->put(
            'sweetness-things/profile/client-cover_thumb.webp',
            'legacy-thumbnail',
        );

        $this->artisan('jcorp:import-client-media')->assertSuccessful();

        $this->assertSame(
            'sweetness-things/profile/client-generated-hero.webp',
            $sweetness->refresh()->cover_image_path,
        );
        $this->assertTrue(
            $sweetness->portfolioItems()->where('image_path', $legacyPortfolio)->firstOrFail()
                ->is_featured_on_home,
        );
        Storage::disk('public')->assertMissing($legacyCover);
        Storage::disk('public')->assertMissing(
            'sweetness-things/profile/client-cover_thumb.webp',
        );
    }

    public function test_the_import_soft_deletes_the_old_ngelash_photo(): void
    {
        Storage::fake('public');

        $businesses = $this->createSubsidiaries();
        $legacy = $businesses['ngelash']->portfolioItems()->create([
            'image_path' => 'ngelash/dd45e627-854b-4b68-9f95-6492efa9ed75.webp',
            'caption' => 'Eye Lash',
            'is_featured_on_home' => false,
            'sort_order' => 1,
        ]);

        $this->artisan('jcorp:import-client-media')->assertSuccessful();

        $this->assertSoftDeleted('portfolio_items', ['id' => $legacy->id]);
        $this->assertSame(7, $businesses['ngelash']->portfolioItems()->count());
    }

    public function test_the_import_preserves_a_cover_selected_by_an_admin(): void
    {
        Storage::fake('public');

        $businesses = $this->createSubsidiaries();
        $nails = $businesses['nails-by-me'];
        $customCover = 'nails-by-me/profile/admin-selected-cover.webp';
        $nails->forceFill(['cover_image_path' => $customCover])->save();
        Storage::disk('public')->put($customCover, 'admin-cover');

        $this->artisan('jcorp:import-client-media')->assertSuccessful();

        $this->assertSame($customCover, $nails->refresh()->cover_image_path);
        Storage::disk('public')->assertExists($customCover);
        Storage::disk('public')->assertMissing(
            'nails-by-me/profile/client-generated-hero.webp',
        );
    }

    /** @return Collection<string, Business> */
    private function createSubsidiaries(): Collection
    {
        return collect([
            'sweetness-things',
            'nails-by-me',
            'ngelash',
            'ayodya-logistic',
            'j-land-property',
        ])->mapWithKeys(fn (string $slug) => [
            $slug => Business::factory()->create([
                'slug' => $slug,
                'has_portfolio' => false,
            ]),
        ]);
    }
}
