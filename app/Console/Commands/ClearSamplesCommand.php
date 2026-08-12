<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Database\Seeders\SampleContent;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

/**
 * Menghapus seluruh data contoh yang dimasukkan SampleContentSeeder.
 *
 * Ditulis bersamaan dengan seedernya, bukan menyusul — supaya tidak ada data
 * karangan yang tertinggal saat website tayang karena lupa dibersihkan.
 *
 * Tiga jenis data contoh, dikenali dengan cara berbeda:
 *
 * 1. Item katalog — ditandai penanda di kolom `category`
 * 2. Foto portfolio — jalur gambarnya menunjuk ke images/placeholder/
 * 3. Teks profil & kontak — dicocokkan isinya dengan teks contoh
 *
 * Yang ketiga memakai perbandingan isi, bukan penanda, karena kolomnya
 * memang kolom isi sungguhan. Konsekuensinya menguntungkan: kalau admin
 * sudah menimpanya lewat panel, isinya tidak lagi cocok dan tulisan
 * sungguhan itu aman.
 *
 * Yang TIDAK disentuh perintah ini:
 * - Logo Sweetness, karena itu berkas asli dari client
 * - Sakelar terbit, karena menyembunyikan profil adalah keputusan sadar
 */
class ClearSamplesCommand extends Command
{
    protected $signature = 'jcorp:clear-samples {--force : Lewati konfirmasi}';

    protected $description = 'Menghapus data contoh yang dibuat seeder';

    public function handle(): int
    {
        $items = CatalogItem::withTrashed()
            ->where('category', CatalogItem::SAMPLE_MARKER);

        $photos = PortfolioItem::withTrashed()
            ->where('image_path', 'like', 'images/placeholder/%');

        $itemCount = $items->count();
        $photoCount = $photos->count();
        $textTargets = $this->businessesWithSampleText();

        if ($itemCount === 0 && $photoCount === 0 && $textTargets === []) {
            $this->info('Tidak ada data contoh yang tersisa.');

            return self::SUCCESS;
        }

        if ($itemCount > 0) {
            $this->warn("{$itemCount} item katalog contoh akan dihapus permanen.");
        }

        if ($photoCount > 0) {
            $this->warn("{$photoCount} foto portfolio contoh akan dihapus permanen.");
        }

        if ($textTargets !== []) {
            $this->warn(\count($textTargets).' anak usaha punya teks contoh yang akan dikosongkan:');

            foreach ($textTargets as $business) {
                $this->line("  - {$business->name}");
            }
        }

        if (! $this->option('force') && ! confirm('Lanjutkan?', default: false)) {
            $this->line('Dibatalkan.');

            return self::SUCCESS;
        }

        // forceDelete, bukan delete: data contoh memang tidak perlu bisa
        // dipulihkan, dan menyisakannya sebagai baris soft-deleted justru
        // membuat pemeriksaan berikutnya membingungkan.
        $items->forceDelete();
        $photos->forceDelete();

        $this->clearSampleText($textTargets);

        $this->info('Data contoh dibersihkan.');
        $this->line('Logo Sweetness dipertahankan — itu berkas asli, bukan contoh.');
        $this->line('Sakelar terbit TIDAK diubah — sembunyikan lewat panel Kelola Anak Usaha kalau perlu.');

        return self::SUCCESS;
    }

    /**
     * Mengosongkan kolom yang isinya masih persis teks contoh.
     *
     * @param  array<int, Business>  $businesses
     */
    private function clearSampleText(array $businesses): void
    {
        $samples = SampleContent::businesses();

        foreach ($businesses as $business) {
            foreach ($samples[$business->slug] as $column => $sampleValue) {
                if ($business->{$column} === $sampleValue) {
                    // Label section punya nilai bawaan di migrasi, jadi
                    // dikembalikan ke sana alih-alih dikosongkan — halaman
                    // publik butuh sebutan untuk sectionnya.
                    $business->{$column} = str_ends_with($column, '_label')
                        ? $this->defaultLabel($column)
                        : null;
                }
            }

            $business->save();
        }
    }

    private function defaultLabel(string $column): string
    {
        return $column === 'catalog_label' ? 'Katalog' : 'Portfolio';
    }

    /**
     * Anak usaha yang salah satu kolomnya masih berisi teks contoh.
     *
     * @return array<int, Business>
     */
    private function businessesWithSampleText(): array
    {
        $samples = SampleContent::businesses();

        return Business::whereIn('slug', array_keys($samples))
            ->get()
            ->filter(function (Business $business) use ($samples) {
                foreach ($samples[$business->slug] as $column => $sampleValue) {
                    if ($business->{$column} === $sampleValue) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
    }
}
