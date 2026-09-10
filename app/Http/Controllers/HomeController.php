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
    /**
     * Label bidang yang sudah disetujui untuk orbit homepage induk.
     *
     * Dua unit kecantikan tetap dikirim dengan label yang sama; frontend
     * menggabungkannya menjadi satu pill agar komposisinya seperti mockup.
     *
     * @var array<string, string>
     */
    private const HOME_SECTOR_LABELS = [
        'sweetness-things' => 'Kuliner',
        'nails-by-me' => 'Kecantikan',
        'ngelash' => 'Kecantikan',
        'ayodya-logistic' => 'Logistik',
        'j-land-property' => 'Properti',
    ];

    /**
     * Tagline editorial yang disetujui khusus untuk kartu Unit Usaha induk.
     *
     * Hanya menjadi fallback ketika kolom tagline bisnis masih kosong. Dengan
     * begitu copy ini tidak mengganti tulisan admin dan tidak ikut masuk ke
     * hero halaman anak usaha.
     *
     * @var array<string, string>
     */
    private const HOME_CARD_TAGLINES = [
        'sweetness-things' => 'Homemade Desserts for Every Little Celebration.',
        'nails-by-me' => 'Beautiful, Neat, and Long-Lasting Nail Art.',
        'j-land-property' => 'Safe, Comfortable, and Trusted Property Solutions.',
    ];

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
                'cover_image_url' => $this->images->url($parent->cover_image_path),

                // Lihat catatan di PublicProfileController: nilainya masuk
                // ke atribut `style`, jadi bentuknya dipastikan dulu.
                'accent_color' => $parent->safeAccentColor(),
                'favicon_url' => $this->images->url($parent->faviconPath()),

                // Induk memakai bagian yang sama dengan anak usaha. Yang
                // tidak dipakai di sini: layanan dan keunggulan — induk tidak
                // menjual apa pun sendiri, itu wilayah masing-masing unit.
                'vision' => $parent->vision,
                'mission' => $parent->mission ?: null,
            ],

            // Hanya yang sudah diterbitkan (spec §4). Yang belum terbit tidak
            // muncul sebagai kartu DAN halamannya mengembalikan 404 — dua-duanya
            // perlu, kalau tidak keberadaannya terungkap lewat etalase.
            'subsidiaries' => Business::query()
                ->subsidiaries()
                ->published()
                ->with('featuredPortfolioItem')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Business $business) => [
                    'slug' => $business->slug,
                    'name' => $business->name,
                    'tagline' => $business->tagline
                        ?: (self::HOME_CARD_TAGLINES[$business->slug] ?? null),
                    'logo_url' => $this->images->url($business->logo_path),
                    'featured_image_url' => $this->images->url(
                        $business->featuredPortfolioItem?->image_path,
                    ),
                    'accent_color' => $business->safeAccentColor(),
                    'initials' => $this->initials($business->name),
                    'sector_label' => self::HOME_SECTOR_LABELS[$business->slug] ?? null,
                ])
                ->values()
                ->all(),

            'contact' => $this->contact($parent),
        ]);
    }

    /**
     * Bagian kontak induk. null berarti sectionnya tidak dirender.
     *
     * Berbeda dari anak usaha dalam dua hal: induk punya daftar akun unit
     * usaha, dan `contact_note` di sini bisa berdiri sendiri sebagai isi —
     * kalimatnya menerangkan cara bekerja sama, bukan sekadar pengantar
     * untuk baris nomor di bawahnya.
     *
     * @return array<string, mixed>|null
     */
    private function contact(Business $parent): ?array
    {
        $rows = array_filter([
            'whatsapp' => $parent->whatsapp,
            'whatsapp_alt' => $parent->whatsapp_alt,
            'instagram' => $parent->instagram,
            'tiktok' => $parent->tiktok,
            'address' => $parent->address,
            'business_hours' => $parent->business_hours,
        ], fn (?string $value) => $value !== null && $value !== '');

        $socials = $parent->unit_socials ?: [];
        $note = $parent->contact_note;

        // Sectionnya hilang hanya kalau BENAR-BENAR tidak ada cara
        // menghubungi — bukan sekadar tidak ada nomor. Daftar akun unit
        // usaha sudah cukup jadi isi.
        if ($rows === [] && $socials === [] && ($note === null || $note === '')) {
            return null;
        }

        if ($note !== null && $note !== '') {
            $rows['contact_note'] = $note;
        }

        if ($socials !== []) {
            $rows['unit_socials'] = $socials;
        }

        return $rows;
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
