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

        return Inertia::render('panel/dashboard', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
                'is_published' => $business->is_published,
            ],
            'counts' => [
                'catalog' => $business->catalogItems()->count(),
                'portfolio' => $business->portfolioItems()->count(),
            ],
            // Pemilih konteks hanya dikirim ke super-admin. Business_admin
            // tidak pernah menerima daftar anak usaha lain (spec §6).
            'switchableBusinesses' => $request->user()->isSuperAdmin()
                ? Business::orderBy('sort_order')->get(['slug', 'name'])
                : null,
        ]);
    }
}
