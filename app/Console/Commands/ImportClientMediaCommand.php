<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\PortfolioItem;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportClientMediaCommand extends Command
{
    protected $signature = 'jcorp:import-client-media';

    protected $description = 'Impor materi galeri client dan ilustrasi hero tanpa menggandakan data';

    /** @var list<string> */
    private const PORTFOLIO_SLUGS = [
        'sweetness-things',
        'nails-by-me',
        'ngelash',
        'ayodya-logistic',
        'j-land-property',
    ];

    /**
     * Foto ilustratif ini hanya dipakai sebagai cover hero. Foto-foto ini
     * bukan bukti pekerjaan client dan tidak boleh dibuat menjadi portfolio,
     * katalog, atau gambar pilihan pada kartu halaman induk.
     *
     * @var array<string, string>
     */
    private const HERO_MEDIA = [
        'sweetness-things' => 'images/generated/heroes/sweetness-things-hero.png',
        'nails-by-me' => 'images/generated/heroes/nails-by-me-hero.png',
        'ngelash' => 'images/generated/heroes/ngelash-hero.png',
        'ayodya-logistic' => 'images/generated/heroes/ayodya-logistic-hero.png',
        'j-land-property' => 'images/generated/heroes/j-land-property-hero.png',
    ];

    /**
     * Versi awal importer memakai poster pertama sebagai cover. Jalur ini
     * hanya dipakai untuk mengenali data warisan yang boleh dimigrasikan.
     * Cover lain dianggap pilihan admin dan harus dipertahankan.
     *
     * @var array<string, string>
     */
    private const LEGACY_AUTO_COVERS = [
        'sweetness-things' => 'sweetness-things/profile/client-cover.webp',
        'ngelash' => 'ngelash/profile/client-cover.webp',
    ];

    /**
     * Foto lama yang bukan materi client dan tidak boleh lagi tampil di galeri.
     * Soft-delete dipakai supaya masih bisa dipulihkan dari backup bila perlu.
     *
     * @var array<string, list<string>>
     */
    private const LEGACY_PORTFOLIO_MEDIA = [
        'ngelash' => [
            'ngelash/dd45e627-854b-4b68-9f95-6492efa9ed75.webp',
        ],
    ];

    /**
     * Satu foto asli yang dipilih untuk latar kartu unit di halaman induk.
     * Unit lain sengaja tidak diberi foto sampai client mengirim materinya.
     *
     * @var array<string, string>
     */
    private const HOME_FEATURED_MEDIA = [
        'sweetness-things' => 'sweetness-things/portfolio/client-browncho.webp',
        'ngelash' => 'ngelash/portfolio/client-volume-cat-eye-12-13mm.webp',
    ];

    /**
     * @var array<string, list<array{source: string, name: string, caption: string}>>
     */
    private const MEDIA = [
        'sweetness-things' => [
            [
                'source' => 'images/sweetness/372eeb39-9315-4a95-a996-a9e204f4a0a7.jpg',
                'name' => 'browncho',
                'caption' => 'Browncho',
            ],
            [
                'source' => 'images/sweetness/766e1816-bad3-4abc-987d-de35e87b28d8.jpg',
                'name' => 'brownberry',
                'caption' => 'Brownberry',
            ],
            [
                'source' => 'images/sweetness/98c29fae-2c02-449f-bbe5-cca427870a3e.jpg',
                'name' => 'brownoreo',
                'caption' => 'Brownoreo',
            ],
            [
                'source' => 'images/sweetness/b031fa62-e92f-493b-ae79-699ed1e5997b.jpg',
                'name' => 'brownchio',
                'caption' => 'Brownchio',
            ],
        ],
        'ayodya-logistic' => [
            [
                'source' => 'images/ayodya/aul-truck-box.jpg',
                'name' => 'armada-truk-box-aul',
                'caption' => 'Truk Box AUL — Armada Darat',
            ],
            [
                'source' => 'images/ayodya/aul-charter-truck.jpg',
                'name' => 'charter-truk-ekspedisi',
                'caption' => 'Charter Truk Ekspedisi — Armada Darat',
            ],
        ],
        'ngelash' => [
            [
                'source' => 'images/ngelash/4d4d405e-b7f2-4eb8-bb8b-4b7d1ddffe8a.jpg',
                'name' => 'volume-cat-eye-12-13mm',
                'caption' => 'Volume 12–13 mm — Cat Eye',
            ],
            [
                'source' => 'images/ngelash/265ef61c-e393-4d19-85d0-e05de8d83c4d.jpg',
                'name' => 'yy-lash-cat-eye-10-12mm',
                'caption' => 'YY Lash 10–12 mm — Cat Eye',
            ],
            [
                'source' => 'images/ngelash/846d94e8-3def-49d0-9f22-e3a15442497e.jpg',
                'name' => 'yy-lash-cat-eye-10-11mm-a',
                'caption' => 'YY Lash 10–11 mm — Cat Eye',
            ],
            [
                'source' => 'images/ngelash/938da7be-f6ed-415f-969a-fbbb3d8f6de7.jpg',
                'name' => 'lash-lift-tinted',
                'caption' => 'Lash Lift — Tinted',
            ],
            [
                'source' => 'images/ngelash/b70998e1-2c97-45d8-87f4-d927cb3c99b9.jpg',
                'name' => 'lash-lift-tint',
                'caption' => 'Lash Lift — Tint',
            ],
            [
                'source' => 'images/ngelash/cd4ec623-e983-4768-8fcc-460ffbd1844a.jpg',
                'name' => 'yy-lash-cat-eye-10-11mm-b',
                'caption' => 'YY Lash 10–11 mm — Cat Eye',
            ],
            [
                'source' => 'images/ngelash/IMG_3615.PNG',
                'name' => 'yy-lash-basic-9-10mm',
                'caption' => 'YY Lash 9–10 mm — Basic',
            ],
        ],
    ];

    public function handle(ImageService $images): int
    {
        foreach (self::MEDIA as $items) {
            foreach ($items as $item) {
                if (! is_file(public_path($item['source']))) {
                    $this->error("Materi tidak ditemukan: {$item['source']}");

                    return self::FAILURE;
                }
            }
        }

        foreach (self::HERO_MEDIA as $source) {
            if (! is_file(public_path($source))) {
                $this->error("Ilustrasi hero tidak ditemukan: {$source}");

                return self::FAILURE;
            }
        }

        $businesses = Business::query()
            ->whereIn('slug', self::PORTFOLIO_SLUGS)
            ->get()
            ->keyBy('slug');

        $missing = array_values(array_diff(self::PORTFOLIO_SLUGS, $businesses->keys()->all()));

        if ($missing !== []) {
            $this->error('Unit usaha tidak ditemukan: '.implode(', ', $missing));

            return self::FAILURE;
        }

        DB::transaction(function () use ($businesses, $images): void {
            foreach ($businesses as $business) {
                $business->forceFill(['has_portfolio' => true])->save();
            }

            foreach (self::MEDIA as $slug => $items) {
                /** @var Business $business */
                $business = $businesses[$slug];
                $nextSortOrder = ((int) $business->portfolioItems()->max('sort_order')) + 1;

                foreach ($items as $offset => $item) {
                    $imagePath = $images->storeFromPath(
                        public_path($item['source']),
                        "{$slug}/portfolio",
                        "client-{$item['name']}",
                    );

                    $portfolio = PortfolioItem::withTrashed()
                        ->where('business_id', $business->id)
                        ->where('image_path', $imagePath)
                        ->first();

                    if ($portfolio === null) {
                        $portfolio = $business->portfolioItems()->create([
                            'image_path' => $imagePath,
                            'caption' => $item['caption'],
                            'is_featured_on_home' => false,
                            'sort_order' => $nextSortOrder + $offset,
                        ]);
                    } elseif ($portfolio->trashed()) {
                        $portfolio->restore();
                    }
                }
            }

            foreach (self::LEGACY_PORTFOLIO_MEDIA as $slug => $paths) {
                /** @var Business $business */
                $business = $businesses[$slug];

                $business->portfolioItems()
                    ->whereIn('image_path', $paths)
                    ->get()
                    ->each(fn (PortfolioItem $portfolio) => $portfolio->delete());
            }

            foreach (self::HOME_FEATURED_MEDIA as $slug => $path) {
                /** @var Business $business */
                $business = $businesses[$slug];

                // Hanya mengisi pilihan awal. Pilihan yang sudah dibuat admin
                // tetap dihormati saat importer dijalankan ulang.
                if ($business->featuredPortfolioItem()->exists()) {
                    continue;
                }

                $featured = $business->portfolioItems()
                    ->where('image_path', $path)
                    ->first();

                if ($featured !== null) {
                    $featured->update(['is_featured_on_home' => true]);
                }
            }

            foreach (self::HERO_MEDIA as $slug => $source) {
                /** @var Business $business */
                $business = $businesses[$slug];
                $managedPath = "{$slug}/profile/client-generated-hero.webp";
                $legacyPath = self::LEGACY_AUTO_COVERS[$slug] ?? null;
                $isLegacyCover = $legacyPath !== null
                    && $business->cover_image_path === $legacyPath;
                $isManagedCover = $business->cover_image_path === $managedPath;

                // null berarti unit belum punya cover. Jalur managed berarti
                // aset boleh disegarkan saat file sumber diperbarui. Selain
                // ketiga kondisi ini, cover adalah pilihan admin dan tidak
                // disentuh oleh importer.
                if ($business->cover_image_path === null || $isLegacyCover || $isManagedCover) {
                    $coverPath = $images->storeFromPath(
                        public_path($source),
                        "{$slug}/profile",
                        'client-generated-hero',
                    );

                    if ($business->cover_image_path !== $coverPath) {
                        $business->forceFill([
                            'cover_image_path' => $coverPath,
                        ])->save();
                    }
                }

                if ($isLegacyCover) {
                    $images->delete($legacyPath);
                }
            }
        });

        $this->info('Materi client berhasil diimpor: 4 foto Sweetness Things, 7 foto ngelash.id, dan 2 foto armada Ayodya Logistic.');
        $this->info('Lima ilustrasi hero diterapkan tanpa memasukkannya sebagai portfolio atau katalog.');
        $this->info('Foto Browncho dan Volume Cat Eye dipilih sebagai latar kartu halaman induk.');
        $this->info('Foto lama ngelash disembunyikan dari galeri; unit tanpa foto tetap tanpa placeholder.');

        return self::SUCCESS;
    }
}
