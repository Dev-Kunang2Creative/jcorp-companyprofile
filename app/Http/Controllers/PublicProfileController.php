<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use App\Services\ImageService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman profil anak usaha — `GET /{slug}` (spec §3).
 *
 * Satu controller untuk kelima anak usaha. Yang membedakan tampilannya hanya
 * isi kolom database, bukan lima kelas berbeda.
 */
class PublicProfileController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function __invoke(string $slug): Response
    {
        // Yang belum diterbitkan mengembalikan 404, bukan pesan "belum
        // tersedia" — supaya keberadaannya tidak terungkap (spec §4).
        $business = Business::query()
            ->where('slug', $slug)
            ->where('is_parent', false)
            ->published()
            ->firstOrFail();

        $catalog = $this->catalogItems($business);
        $portfolio = $this->portfolioItems($business);
        $contact = $this->contact($business);

        return Inertia::render('public/business', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
                'tagline' => $business->tagline,
                'description' => $business->description,
                'logo_url' => $this->images->url($business->logo_path),

                // Warna aksen halaman (DESIGN_SYSTEM §2.3). Lewat
                // safeAccentColor(), bukan kolomnya langsung: nilainya
                // berakhir di atribut `style`, jadi bentuknya harus
                // dipastikan lebih dulu.
                'accent_color' => $business->safeAccentColor(),

                // Ikon tab browser. Halaman anak usaha dibuka di tab
                // sendiri dari etalase induk, jadi ikonnya yang
                // membedakan tab mana milik siapa.
                'favicon_url' => $this->images->url($business->faviconPath()),

                'catalog_label' => $business->catalog_label,
                'catalog_note' => $business->catalog_note,
                'portfolio_label' => $business->portfolio_label,

                // Bagian profil yang bentuknya menyusul materi client. Yang
                // bernilai null menghilangkan sectionnya, sama seperti
                // katalog dan portfolio di bawah.
                'vision' => $business->vision,
                'mission' => $business->mission ?: null,
                'featured_services' => $business->featured_services ?: null,
                'services' => $business->services ?: null,
                'services_label' => $business->services_label,
                'highlights' => $business->highlights ?: null,
            ],

            // Nama induk untuk footer ("bagian dari …"). Diambil dari
            // database, bukan ditulis di komponen — kalau nama induk berubah,
            // footer lima halaman anak usaha tidak perlu ikut disunting.
            'parentName' => Business::where('is_parent', true)->value('name'),

            // Aturan section kosong (spec §3) ditegakkan DI SINI, bukan di
            // React: section yang tidak dirender datanya juga tidak dikirim
            // ke browser. Yang bernilai null menghilangkan sectionnya.
            'catalog' => $catalog === [] ? null : $catalog,
            'portfolio' => $portfolio === [] ? null : $portfolio,
            'contact' => $contact === [] ? null : $contact,
        ]);
    }

    /**
     * Item katalog yang benar-benar tampil di halaman publik.
     *
     * Disaring `available()` — item yang disembunyikan sementara (stok habis)
     * tidak ikut. Kalau seluruh item disembunyikan, hasilnya kosong dan
     * section katalog hilang seluruhnya.
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalogItems(Business $business): array
    {
        return $business->catalogItems()
            ->available()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CatalogItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                // Penanda data contoh tidak pernah tampil sebagai kategori —
                // itu penanda internal, bukan isi yang dimaksudkan admin.
                'category' => $item->publicCategory(),
                // Keterangan dan angka dikirim terpisah: DESIGN_SYSTEM §6
                // menaruh keterangan di baris kecil DI ATAS angkanya, supaya
                // angkanya tetap yang paling menonjol.
                //
                // Keterangan tetap dikirim walau harganya kosong: justru di
                // situ ia paling berguna — "hubungi kami" pada item yang
                // harganya menyesuaikan. Sebelumnya dibuang, jadi kartunya
                // menyisakan ruang kosong tanpa informasi apa pun.
                'price_note' => $item->price_note,
                'formatted_price' => $item->formattedAmount(),
                'image_url' => $item->image_path
                    ? $this->images->url($this->images->thumbnailPath($item->image_path))
                    : null,
                // Dipakai sebagai isi kotak cadangan saat gambar gagal dimuat
                // atau memang belum ada (spec §10).
                'initials' => $this->initials($item->name),
            ])
            // values() memaksa kunci berurutan 0,1,2. Tanpa itu kunci asli
            // koleksi dipertahankan, dan kunci yang renggang membuat JSON-nya
            // jadi objek alih-alih array — React gagal me-map-nya.
            ->values()
            ->all();
    }

    /**
     * Foto portfolio yang tampil di halaman publik.
     *
     * Anak usaha yang sakelar portfolio-nya dimatikan mengembalikan array
     * kosong, jadi sectionnya hilang — walaupun fotonya masih tersimpan.
     * Itu yang membuat sakelarnya bisa dinyalakan lagi tanpa kehilangan apa
     * pun.
     *
     * @return array<int, array<string, mixed>>
     */
    private function portfolioItems(Business $business): array
    {
        if (! $business->has_portfolio) {
            return [];
        }

        return $business->portfolioItems()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PortfolioItem $item) => [
                'id' => $item->id,
                'caption' => $item->caption,
                'image_url' => $this->images->url(
                    $this->images->thumbnailPath($item->image_path)
                ),
                'full_url' => $this->images->url($item->image_path),
            ])
            // values() memaksa kunci berurutan 0,1,2. Tanpa itu kunci asli
            // koleksi dipertahankan, dan kunci yang renggang membuat JSON-nya
            // jadi objek alih-alih array — React gagal me-map-nya.
            ->values()
            ->all();
    }

    /**
     * Kolom kontak yang terisi saja. Array kosong berarti seluruh kolom
     * kontak kosong, dan sectionnya tidak dirender.
     *
     * @return array<string, string>
     */
    private function contact(Business $business): array
    {
        $filled = fn (?string $value) => $value !== null && $value !== '';

        $rows = array_filter([
            'whatsapp' => $business->whatsapp,
            'whatsapp_alt' => $business->whatsapp_alt,
            'instagram' => $business->instagram,
            'tiktok' => $business->tiktok,
            'address' => $business->address,
            'business_hours' => $business->business_hours,
        ], $filled);

        // `contact_note` menyertai baris kontak, bukan menggantikannya: kalau
        // tidak ada satu pun cara menghubungi, catatan cara pemesanan tidak
        // ada gunanya — sectionnya akan berisi judul dan satu kalimat tanpa
        // nomor atau akun yang bisa dituju.
        if ($rows === []) {
            return [];
        }

        // Nama pemilik nomor ikut HANYA kalau nomornya ada. Label sendirian
        // bukan cara menghubungi — dan kalau ikut lolos ke penyaringan di
        // atas, sebuah label yatim bisa membuat section kontak muncul tanpa
        // satu pun nomor di dalamnya.
        foreach (['whatsapp', 'whatsapp_alt'] as $column) {
            $label = $business->{$column.'_label'};

            if (isset($rows[$column]) && $filled($label)) {
                $rows[$column.'_label'] = $label;
            }
        }

        if ($filled($business->contact_note)) {
            $rows['contact_note'] = $business->contact_note;
        }

        return $rows;
    }

    /**
     * Dua huruf pertama dari nama item, untuk kotak cadangan gambar.
     */
    private function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return '?';
        }

        if (\count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
    }
}
