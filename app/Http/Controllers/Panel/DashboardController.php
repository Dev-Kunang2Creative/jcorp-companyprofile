<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\ResolvesActiveBusiness;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesActiveBusiness;

    public function __invoke(Request $request): Response
    {
        $business = $this->activeBusiness($request);
        $switchableBusinesses = $request->user()->isSuperAdmin()
            ? Business::query()
                ->withCount(['catalogItems', 'portfolioItems'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Business $item) => [
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'is_published' => $item->is_published,
                    'has_portfolio' => $item->has_portfolio,
                    'catalog_count' => $item->catalog_items_count,
                    'portfolio_count' => $item->portfolio_items_count,
                    'accent_color' => $item->safeAccentColor(),
                ])
            : null;

        return Inertia::render('panel/dashboard', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
                'is_parent' => $business->is_parent,
                'is_published' => $business->is_published,
                'has_portfolio' => $business->has_portfolio,
                'has_contact' => filled($business->whatsapp)
                    || filled($business->whatsapp_alt)
                    || filled($business->instagram)
                    || filled($business->tiktok)
                    || filled($business->address),
                'accent_color' => $business->safeAccentColor(),
                'public_url' => $business->is_parent ? '/' : "/{$business->slug}",
            ],
            'counts' => [
                'catalog' => $business->catalogItems()->count(),
                'portfolio' => $business->portfolioItems()->count(),
            ],
            // Pemilih konteks hanya dikirim ke super-admin. Business_admin
            // tidak pernah menerima daftar anak usaha lain (spec §6).
            'switchableBusinesses' => $switchableBusinesses,
        ]);
    }
}
