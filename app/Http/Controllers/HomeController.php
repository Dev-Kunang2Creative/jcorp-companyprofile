<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\ImageService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman induk J Corp — etalase (spec §3).
 *
 * Route dan komponennya terpisah dari profil anak usaha karena bentuknya
 * memang berbeda: hero, profil singkat, lalu kartu anak usaha. Induk tidak
 * punya katalog maupun portfolio sendiri.
 */
class HomeController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function __invoke(): Response
    {
        $parent = Business::where('is_parent', true)->firstOrFail();

        return Inertia::render('home', [
            'parent' => [
                'name' => $parent->name,
                'tagline' => $parent->tagline,
                'description' => $parent->description,
                'logo_url' => $this->images->url($parent->logo_path),
            ],

            // Hanya yang sudah diterbitkan (spec §4). Yang belum terbit tidak
            // muncul sebagai kartu DAN halamannya mengembalikan 404 — dua-duanya
            // perlu, kalau tidak keberadaannya terungkap lewat etalase.
            'subsidiaries' => Business::query()
                ->subsidiaries()
                ->published()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Business $business) => [
                    'slug' => $business->slug,
                    'name' => $business->name,
                    'tagline' => $business->tagline,
                    'logo_url' => $this->images->url($business->logo_path),
                    'initials' => $this->initials($business->name),
                ])
                ->values()
                ->all(),

            'contact' => array_filter([
                'whatsapp' => $parent->whatsapp,
                'instagram' => $parent->instagram,
                'address' => $parent->address,
                'business_hours' => $parent->business_hours,
            ], fn (?string $value) => $value !== null && $value !== '') ?: null,
        ]);
    }

    /**
     * Inisial nama usaha, untuk kartu yang belum punya logo.
     *
     * Sengaja mengabaikan awalan badan usaha ("PT.", "CV") — inisial dari
     * "PT. Ayodya Utama Logistic" seharusnya AU, bukan PA.
     */
    private function initials(string $name): string
    {
        $name = preg_replace('/^(PT\.?|CV\.?|UD\.?)\s+/i', '', trim($name)) ?? $name;

        $words = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return '?';
        }

        if (\count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
    }
}
