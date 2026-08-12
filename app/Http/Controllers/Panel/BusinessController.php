<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kelola anak usaha dan sakelar terbit — super-admin saja (spec §6).
 *
 * Pembatasannya berlapis: middleware di route, plus authorize() di setiap
 * aksi. Middleware saja tidak cukup sebagai satu-satunya penjaga — kalau
 * suatu saat route dipindah dan middleware-nya terlewat, authorize() masih
 * menahan.
 */
class BusinessController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage', Business::class);

        return Inertia::render('panel/businesses/index', [
            'businesses' => Business::withCount(['catalogItems', 'portfolioItems'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Business $business) => [
                    'id' => $business->id,
                    'slug' => $business->slug,
                    'name' => $business->name,
                    'is_parent' => $business->is_parent,
                    'is_published' => $business->is_published,
                    'has_portfolio' => $business->has_portfolio,
                    'sort_order' => $business->sort_order,
                    'catalog_count' => $business->catalog_items_count,
                    'portfolio_count' => $business->portfolio_items_count,
                ]),
        ]);
    }

    /**
     * Menyalakan atau mematikan sakelar terbit.
     *
     * `is_published` tidak fillable, jadi diisi lewat properti langsung —
     * memastikan hanya jalur ini yang bisa mengubahnya, bukan sembarang
     * form yang kebetulan mengirim field bernama sama.
     */
    public function togglePublished(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('manage', Business::class);

        $validated = $request->validate([
            'is_published' => ['required', 'boolean'],
        ]);

        $business->is_published = $validated['is_published'];
        $business->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $business->is_published
                ? "{$business->name} sekarang tayang."
                : "{$business->name} disembunyikan dari publik.",
        ]);

        return back();
    }

    /**
     * Menyalakan atau mematikan section portfolio.
     *
     * Tidak semua anak usaha memamerkan hasil kerja — dessert, logistik, dan
     * properti menampilkan katalog saja (spec §3). Sakelar ini menentukan
     * apakah menu Portfolio muncul di panel adminnya.
     *
     * `has_portfolio` tidak fillable, sama seperti `is_published` — hanya
     * jalur ini yang bisa mengubahnya.
     */
    public function togglePortfolio(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('manage', Business::class);

        $validated = $request->validate([
            'has_portfolio' => ['required', 'boolean'],
        ]);

        // Mematikan sakelar tidak menghapus foto yang sudah ada — kalau
        // dinyalakan lagi, fotonya masih utuh. Tapi selama mati, section
        // portfolio hilang dari halaman publik.
        $business->has_portfolio = $validated['has_portfolio'];
        $business->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $business->has_portfolio
                ? "Portfolio dinyalakan untuk {$business->name}."
                : "Portfolio dimatikan untuk {$business->name}.",
        ]);

        return back();
    }
}
