<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\ResolvesActiveBusiness;
use App\Http\Requests\Panel\PortfolioItemRequest;
use App\Models\Business;
use App\Models\PortfolioItem;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioItemController extends Controller
{
    use ResolvesActiveBusiness;

    public function __construct(private readonly ImageService $images) {}

    /**
     * Menolak akses ke anak usaha yang tidak memakai portfolio.
     *
     * Menyembunyikan menu di sidebar TIDAK dianggap pengamanan — alamatnya
     * masih bisa diketik langsung. Sama seperti pengecekan kepemilikan
     * (spec §6), penolakan sungguhan terjadi di sisi server.
     */
    private function ensureUsesPortfolio(Business $business): void
    {
        abort_unless(
            $business->has_portfolio,
            404,
            'Anak usaha ini tidak memakai portfolio.',
        );
    }

    public function index(Request $request): Response
    {
        $business = $this->activeBusiness($request);

        $this->ensureUsesPortfolio($business);

        return Inertia::render('panel/portfolio/index', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
                'portfolio_label' => $business->portfolio_label,
            ],
            'items' => $business->portfolioItems()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (PortfolioItem $item) => [
                    'id' => $item->id,
                    'caption' => $item->caption,
                    'sort_order' => $item->sort_order,
                    'image_url' => $this->images->url($item->image_path),
                    'thumb_url' => $this->images->url(
                        $this->images->thumbnailPath($item->image_path)
                    ),
                ]),
        ]);
    }

    public function store(PortfolioItemRequest $request): RedirectResponse
    {
        $business = $this->activeBusiness($request);

        $this->ensureUsesPortfolio($business);
        $this->authorize('create', [PortfolioItem::class, $business]);

        $business->portfolioItems()->create([
            ...$request->safe()->except('image'),
            'image_path' => $this->images->store($request->file('image'), $business->slug),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Foto portfolio ditambahkan.']);

        return back();
    }

    public function update(PortfolioItemRequest $request, PortfolioItem $portfolioItem): RedirectResponse
    {
        $this->authorize('update', $portfolioItem);

        $attributes = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $this->images->replace(
                $request->file('image'),
                $portfolioItem->business->slug,
                $portfolioItem->image_path,
            );
        }

        $portfolioItem->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Foto portfolio diperbarui.']);

        return back();
    }

    public function destroy(PortfolioItem $portfolioItem): RedirectResponse
    {
        $this->authorize('delete', $portfolioItem);

        // Soft delete — berkas gambarnya sengaja dipertahankan, lihat
        // CatalogItemController::destroy() untuk alasannya.
        $portfolioItem->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Foto portfolio dihapus.']);

        return back();
    }
}
