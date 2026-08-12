<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

/**
 * Enam entitas sesuai spec §1 dan §3.
 *
 * Kolom `slug`, `is_parent`, dan `is_published` tidak fillable, jadi diisi
 * lewat updateOrCreate yang memang menerimanya secara eksplisit — bukan
 * lewat mass assignment dari input luar.
 */
class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->businesses() as $attributes) {
            Business::withTrashed()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * Hanya `name`, `slug`, dan label section yang diisi di sini.
     *
     * Tagline, cerita perusahaan, kontak, dan logo dikosongkan karena
     * materinya memang belum ada (spec §15) — diisi admin lewat panel, atau
     * ditulis di kode saat materinya terkumpul. Mengarang isian sekarang
     * berisiko ikut tayang tanpa sengaja.
     *
     * @return array<int, array<string, mixed>>
     */
    private function businesses(): array
    {
        return [
            [
                'slug' => 'jcorp',
                'name' => 'J Corp',
                'is_parent' => true,
                // Halaman induk baru dibangun di Fase 4; sampai saat itu
                // tidak ada yang bisa dilihat siapa pun.
                'is_published' => true,
                'sort_order' => 0,
                'catalog_label' => 'Katalog',
                'portfolio_label' => 'Portfolio',
            ],
            [
                'slug' => 'sweetness-things',
                'name' => 'Sweetness Things',
                'is_parent' => false,
                'is_published' => false,
                'sort_order' => 1,
                'catalog_label' => 'Menu Kami',
                'portfolio_label' => 'Portfolio',
            ],
            [
                'slug' => 'nails-by-me',
                'name' => "Nail's by Me",
                'is_parent' => false,
                'is_published' => false,
                'sort_order' => 2,
                'catalog_label' => 'Layanan & Harga',
                'portfolio_label' => 'Hasil Kerja',
            ],
            [
                'slug' => 'ngelash',
                'name' => 'ngelash.id',
                'is_parent' => false,
                'is_published' => false,
                'sort_order' => 3,
                'catalog_label' => 'Layanan & Harga',
                'portfolio_label' => 'Hasil Kerja',
            ],
            [
                'slug' => 'ayodya-logistic',
                'name' => 'PT. Ayodya Utama Logistic',
                'is_parent' => false,
                'is_published' => false,
                'sort_order' => 4,
                'catalog_label' => 'Layanan Kami',
                'portfolio_label' => 'Portfolio',
            ],
            [
                'slug' => 'lumintu-property',
                'name' => 'Lumintu Property',
                'is_parent' => false,
                'is_published' => false,
                'sort_order' => 5,
                'catalog_label' => 'Unit Tersedia',
                'portfolio_label' => 'Portfolio',
            ],
        ];
    }
}
