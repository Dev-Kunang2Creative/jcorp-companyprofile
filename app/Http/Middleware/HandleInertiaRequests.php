<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Support\PanelContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props yang ikut di setiap halaman.
     *
     * Yang dikirim tentang akun sengaja dibatasi ke field yang memang dipakai
     * tampilan. `password` dan `remember_token` sudah tersaring lewat
     * atribut #[Hidden] di model, tapi membatasi di sini juga berarti kolom
     * baru yang ditambahkan ke tabel `users` nanti tidak otomatis ikut
     * terkirim ke browser tanpa ada yang memutuskan begitu.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $business = $this->activeBusiness($request);

        $businessContext = $business ? [
            'slug' => $business->slug,
            'name' => $business->name,
            'isParent' => $business->is_parent,
            'isPublished' => $business->is_published,
            'hasPortfolio' => $business->has_portfolio,
            'accentColor' => $business->safeAccentColor(),
            'publicUrl' => $business->is_parent ? '/' : "/{$business->slug}",
        ] : null;

        // Daftar perpindahan konteks tetap hanya dikirim ke super-admin.
        // Ini memindahkan pemilih yang sebelumnya hanya ada di Dashboard ke
        // topbar global tanpa mengubah cara konteks disimpan di sesi.
        $switchableBusinesses = $user?->isSuperAdmin()
            ? Business::query()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Business $item) => [
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'accentColor' => $item->safeAccentColor(),
                ])
            : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'business_id' => $user->business_id,
                ] : null,
                // Nama anak usaha yang sedang dikelola, untuk judul panel.
                'businessName' => $business?->name,
                'isSuperAdmin' => $user?->isSuperAdmin() ?? false,
                // Menentukan apakah menu Portfolio muncul di sidebar. Tidak
                // semua anak usaha memamerkan hasil kerja — dessert, logistik,
                // dan properti menampilkan katalog saja (spec §3).
                'hasPortfolio' => $business !== null && $business->has_portfolio,
                'business' => $businessContext,
                'switchableBusinesses' => $switchableBusinesses,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Anak usaha yang sedang dikelola.
     *
     * Mengulang logika ResolvesActiveBusiness dalam bentuk yang lebih longgar:
     * middleware ini jalan di SETIAP halaman, termasuk halaman publik dan
     * login, jadi tidak boleh melempar 403 atau 404 saat konteksnya belum
     * jelas — cukup mengembalikan null.
     */
    private function activeBusiness(Request $request): ?Business
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if (! $user->isSuperAdmin()) {
            return $user->business;
        }

        $slug = $request->query('business')
            ?? $request->session()->get(PanelContext::SESSION_KEY);

        return $slug
            ? Business::where('slug', $slug)->first()
            : Business::where('is_parent', true)->first();
    }
}
