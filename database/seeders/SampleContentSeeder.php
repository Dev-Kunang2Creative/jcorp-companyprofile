<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

/**
 * Mengisi website dengan data contoh.
 *
 * Tujuannya supaya client bisa melihat bentuk jadi website ini sebelum
 * materi asli terkumpul — lengkap dengan katalog, portfolio, dan kontak.
 *
 * Semua yang dimasukkan di sini bisa dibersihkan dengan:
 *
 *     php artisan jcorp:clear-samples
 *
 * Aman dijalankan berulang: kolom yang sudah diisi admin tidak ditimpa.
 *
 * Anak usaha yang materi aslinya sudah masuk (ada di ClientContent) DILEWATI
 * seluruhnya — lihat `hasClientContent()`. Tanpa itu, menjalankan seeder ini
 * lagi akan menambahkan produk karangan berdampingan dengan produk asli di
 * halaman yang sama.
 */
class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->fillProfiles();
        $this->fillCatalogs();
        $this->fillPortfolios();

        // Logo Sweetness tidak lagi diurus di sini — ClientContentSeeder yang
        // memasangnya, karena berkas itu materi asli client, bukan contoh.

        $this->command->info('Anak usaha yang belum punya materi asli terisi data contoh dan diterbitkan.');
        $this->command->line('Bersihkan sebelum tayang sungguhan: php artisan jcorp:clear-samples');
    }

    /**
     * Anak usaha yang materi aslinya sudah masuk.
     *
     * Data contoh tidak boleh menyentuhnya sama sekali — bukan hanya
     * kolomnya, tapi juga katalog dan portfolionya.
     */
    private function hasClientContent(string $slug): bool
    {
        return \array_key_exists($slug, ClientContent::businesses());
    }

    /**
     * Tagline, cerita, kontak, dan label section.
     *
     * Hanya mengisi kolom yang MASIH KOSONG — tulisan admin tidak ditimpa.
     */
    private function fillProfiles(): void
    {
        foreach (SampleContent::businesses() as $slug => $content) {
            if ($this->hasClientContent($slug)) {
                continue;
            }

            $business = Business::where('slug', $slug)->first();

            if (! $business) {
                $this->command->warn("Anak usaha [{$slug}] tidak ditemukan — lewati.");

                continue;
            }

            foreach ($content as $column => $value) {
                $business->{$column} ??= $value;
            }

            // is_published dan has_portfolio tidak fillable — disetel lewat
            // properti langsung. has_portfolio menyala hanya untuk anak usaha
            // yang punya daftar foto contoh (nail art dan eyelash).
            $business->is_published = true;
            $business->has_portfolio = \array_key_exists(
                $slug,
                SampleContent::portfolioItems(),
            );
            $business->save();
        }
    }

    private function fillCatalogs(): void
    {
        foreach (SampleContent::catalogItems() as $slug => $items) {
            if ($this->hasClientContent($slug)) {
                continue;
            }

            $business = Business::where('slug', $slug)->first();

            if (! $business) {
                continue;
            }

            foreach ($items as $index => $attributes) {
                CatalogItem::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $attributes['name'],
                    ],
                    [
                        ...$attributes,
                        // Penanda ini yang dipakai jcorp:clear-samples.
                        // Kategori sungguhan dari data contoh ikut hilang,
                        // tapi itu wajar — barisnya memang akan dihapus.
                        'category' => CatalogItem::SAMPLE_MARKER,
                        'sort_order' => $index,
                        'is_available' => true,
                    ],
                );
            }
        }
    }

    private function fillPortfolios(): void
    {
        foreach (SampleContent::portfolioItems() as $slug => $items) {
            if ($this->hasClientContent($slug)) {
                continue;
            }

            $business = Business::where('slug', $slug)->first();

            if (! $business) {
                continue;
            }

            foreach ($items as $index => $attributes) {
                PortfolioItem::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'image_path' => $attributes['image'],
                    ],
                    [
                        'caption' => $attributes['caption'],
                        'sort_order' => $index,
                    ],
                );
            }
        }
    }
}
