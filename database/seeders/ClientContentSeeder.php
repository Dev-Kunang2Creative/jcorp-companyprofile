<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

/**
 * Memasukkan materi ASLI dari client.
 *
 * Aman dijalankan berulang, termasuk di server yang sudah tayang:
 *
 *     php artisan db:seed --class=ClientContentSeeder --force
 *
 * TIDAK ikut dihapus `php artisan jcorp:clear-samples` — perintah itu hanya
 * mengenali teks dari SampleContent.
 *
 * ---
 *
 * Masalah yang harus diselesaikan seeder ini
 *
 * Anak usaha yang materinya baru masuk BIASANYA sudah lebih dulu terisi teks
 * contoh dari SampleContentSeeder: tagline, cerita, nomor 6281234567890,
 * alamat "Jl. Contoh Raya No. 12, Bekasi".
 *
 * SampleContentSeeder memakai `??=` — hanya mengisi yang masih kosong. Pola
 * itu salah untuk di sini: tidak satu pun teks contoh akan tergantikan, dan
 * materi asli client masuk ke database tanpa pernah muncul di halaman.
 * Nomor telepon palsu tetap tayang, dan tidak ada yang gagal — jadi tidak
 * ada yang menyadarinya sampai ada yang membuka halamannya.
 *
 * Karena itu tiap kolom diperlakukan menurut isinya sekarang:
 *
 *   kosong                            → isi dengan materi client
 *   persis sama dengan teks contoh    → TIMPA, itu memang data contoh
 *   isinya sesuatu yang lain          → biarkan, admin menulisnya sendiri
 *
 * Baris ketiga yang membuatnya aman dijalankan kapan saja. Tekniknya sama
 * dengan yang dipakai `jcorp:clear-samples` — membandingkan isi, bukan
 * menandai baris.
 */
class ClientContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ClientContent::businesses() as $slug => $content) {
            $business = Business::where('slug', $slug)->first();

            if (! $business) {
                $this->command->warn("Anak usaha [{$slug}] tidak ditemukan — lewati.");

                continue;
            }

            $this->fillProfile($business, $content);
            $this->clearWhatClientDidNotProvide($business, $slug);
            $this->attachLogo($business, $slug);
            $this->attachAccent($business, $slug);

            $business->is_published = true;

            // Sakelar portfolio hanya DINYALAKAN, tidak pernah dimatikan —
            // mematikannya adalah keputusan sadar admin lewat panel, dan
            // seeder tidak berhak membatalkannya.
            if (\in_array($slug, ClientContent::withPortfolio(), true)) {
                $business->has_portfolio = true;
            }

            $business->save();

            $this->replaceCatalog($business, $slug);
            $this->clearSamplePhotos($business);

            $this->command->info("Materi asli {$business->name} dimasukkan.");
        }
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function fillProfile(Business $business, array $content): void
    {
        foreach ($content as $column => $value) {
            if ($this->isSafeToReplace($business, $column)) {
                $business->{$column} = $value;
            }
        }
    }

    /**
     * Mengosongkan kolom yang tidak diberikan client.
     *
     * Tanpa ini, tagline dan jam buka karangan tertinggal di database dan
     * ikut tayang berdampingan dengan materi asli.
     */
    private function clearWhatClientDidNotProvide(Business $business, string $slug): void
    {
        foreach (ClientContent::emptyFor()[$slug] ?? [] as $column) {
            if ($this->isSafeToReplace($business, $column)) {
                $business->{$column} = null;
            }
        }
    }

    /**
     * Boleh ditimpa bila kolomnya masih kosong, atau isinya persis teks contoh.
     *
     * Yang isinya lain berarti sudah disunting admin lewat panel — dan itu
     * tidak boleh hilang hanya karena seeder dijalankan lagi.
     *
     * Label section punya nilai bawaan dari migrasi ("Katalog", "Portfolio").
     * Nilai bawaan itu diperlakukan sama dengan kosong: belum pernah
     * ditentukan siapa pun.
     */
    private function isSafeToReplace(Business $business, string $column): bool
    {
        $current = $business->{$column};

        if ($current === null || $current === '') {
            return true;
        }

        // Nilai bawaan yang belum pernah ditentukan siapa pun — label section
        // dari migrasi, dan nama-nama lama induk sebelum ejaan resminya
        // dipastikan client.
        //
        // Kolom `name` punya DUA nilai karena namanya berganti dua kali:
        // "J Corp" (Fase 1) -> "J-Corporette Group" (21 Agu, ejaan keliru)
        // -> "J-Corporate Group" (24 Agu). Database yang sudah jalan bisa
        // menyimpan salah satu di antaranya, dan keduanya harus ditimpa —
        // tanpa itu nama lama bertahan di judul halaman dan di footer
        // kelima anak usaha.
        $defaults = [
            'catalog_label' => ['Katalog'],
            'portfolio_label' => ['Portfolio'],
            'name' => ['J Corp', 'J-Corporette Group'],
            'tagline' => ['Tumbuh Bersama, Melayani Semua'],
            // Isi lama dari materi Nail's sebelum daftar harga lengkap masuk.
            // Sekarang keterangan harga berada di tabel, bukan di Kontak.
            'contact_note' => [
                'Nail art mulai dari Rp 30.000, harga akhir menyesuaikan tingkat kerumitan desain. Pemesanan lewat chat WhatsApp dengan sistem janji temu (appointment only), dan tersedia home service.',
            ],
        ];

        if (\in_array($current, $defaults[$column] ?? [], true)) {
            return true;
        }

        // Dicocokkan terhadap teks contoh SELURUH anak usaha, bukan hanya
        // yang slug-nya sama.
        //
        // Alasannya: slug bisa berganti. Lumintu Property jadi J-Land
        // Property beserta alamat halamannya, sementara teks contohnya masih
        // tersimpan di SampleContent di bawah slug lama. Dicocokkan per slug,
        // perbandingannya tidak menemukan apa pun — dan nomor telepon serta
        // alamat karangan bertahan di halaman yang sudah berganti nama.
        //
        // Melebarkan cakupan ke seluruh anak usaha aman: teks contohnya
        // spesifik per usaha ("Jl. Contoh Damai No. 3"), jadi kebetulan cocok
        // dengan tulisan admin di anak usaha lain praktis mustahil.
        foreach (SampleContent::businesses() as $sample) {
            if (($sample[$column] ?? null) === $current) {
                return true;
            }
        }

        // Teks yang HANYA berbeda pada ejaan lama nama induk.
        //
        // Nama induk sempat ditulis "J-Corporette Group" (21 Agustus)
        // sebelum ejaan resminya dipastikan. Ejaan itu ikut masuk ke
        // dalam teks Tentang Kami, dan teks panjang tidak bisa
        // dicocokkan lewat daftar nilai bawaan seperti kolom `name` —
        // daftarnya akan membengkak setiap kali ada perbaikan kata.
        //
        // Jadi dibandingkan SETELAH ejaan lamanya dibetulkan: kalau
        // hasilnya sama persis dengan materi client, yang tersimpan
        // memang teks lama — bukan tulisan admin.
        $target = ClientContent::businesses()[$business->slug][$column] ?? null;

        if (\is_string($target) && \is_string($current)) {
            // "J-Corporette" saja, tanpa "Group": teks Tentang Kami
            // menyebut namanya dua bentuk — lengkap di awal, singkat di
            // kalimat penutup ("satu keluarga besar J-Corporette").
            // Mengganti frasa lengkapnya saja menyisakan yang kedua.
            $dibetulkan = str_replace(
                'J-Corporette',
                'J-Corporate',
                $current,
            );

            if ($dibetulkan !== $current && $dibetulkan === $target) {
                return true;
            }
        }

        return false;
    }

    private function attachLogo(Business $business, string $slug): void
    {
        $logo = ClientContent::logos()[$slug] ?? null;

        if ($logo !== null && $business->logo_path === null) {
            $business->logo_path = $logo;
        }
    }

    /**
     * Memasang warna aksen (DESIGN_SYSTEM §2.3).
     *
     * Hanya mengisi yang masih kosong — kalau admin sudah menyetel warna
     * lain, itu keputusan sadar dan tidak ditimpa. Perlakuannya sama
     * dengan logo di atas.
     */
    private function attachAccent(Business $business, string $slug): void
    {
        $accent = ClientContent::accentColors()[$slug] ?? null;

        if ($accent !== null && $business->accent_color === null) {
            $business->accent_color = $accent;
        }
    }

    /**
     * Memasukkan produk asli, dan menyapu produk contoh milik anak usaha ini.
     *
     * Penyapuan itu wajib: tanpa itu enam produk karangan berdampingan dengan
     * dua produk asli di halaman yang sama, dan pengunjung tidak punya cara
     * membedakan mana yang benar-benar dijual.
     *
     * PENYAPUAN JALAN WALAU DAFTAR ASLINYA KOSONG. Itu bukan kelalaian:
     * ngelash sudah mengirim profil tapi belum daftar harga, dan justru di
     * situ bahayanya paling besar — halamannya menjanjikan "Harga Transparan"
     * sementara yang tampil enam tarif karangan. Lebih baik section-nya
     * hilang sampai daftar aslinya datang.
     *
     * Yang disapu HANYA yang bertanda SAMPLE_MARKER dan HANYA milik anak
     * usaha ini — produk yang ditambahkan admin sendiri tidak tersentuh.
     */
    private function replaceCatalog(Business $business, string $slug): void
    {
        CatalogItem::withTrashed()
            ->where('business_id', $business->id)
            ->where('category', CatalogItem::SAMPLE_MARKER)
            ->forceDelete();

        foreach (ClientContent::catalogItems()[$slug] ?? [] as $index => $attributes) {
            // Dikenali dari nama DAN kategori, bukan nama saja.
            //
            // Daftar harga ngelash memuat nama yang berulang antar kategori:
            // "Natural", "Medium", dan "Volume" ada di 2D LASH maupun 3D LASH
            // dengan harga berbeda. Dicocokkan dengan nama saja, yang kedua
            // menimpa yang pertama — dari 15 layanan hanya 12 yang tersimpan,
            // dan tiga tarif 2D diam-diam berubah jadi tarif 3D.
            CatalogItem::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $attributes['name'],
                    'category' => $attributes['category'] ?? null,
                ],
                [
                    ...$attributes,
                    'sort_order' => $index,
                    'is_available' => true,
                ],
            );
        }
    }

    /**
     * Menyapu foto portfolio contoh milik anak usaha ini.
     *
     * Foto contoh dikenali dari jalurnya di `images/placeholder/` — sama
     * seperti yang dipakai `jcorp:clear-samples`. Unggahan admin tersimpan
     * di storage dengan nama acak, jadi tidak mungkin ikut tersapu.
     *
     * Sakelar `has_portfolio` TIDAK dimatikan: fotonya memang belum ada,
     * tapi section-nya sudah hilang sendiri karena kosong, dan admin bisa
     * langsung mengunggah tanpa perlu menyalakan apa pun lebih dulu.
     */
    private function clearSamplePhotos(Business $business): void
    {
        PortfolioItem::withTrashed()
            ->where('business_id', $business->id)
            ->where('image_path', 'like', 'images/placeholder/%')
            ->forceDelete();
    }
}
