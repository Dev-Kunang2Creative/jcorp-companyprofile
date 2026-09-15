<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageManagerInterface;
use InvalidArgumentException;

/**
 * Pemrosesan gambar unggahan (spec §5).
 *
 * Foto dari HP berukuran 3–5 MB. Dengan 20 item, halaman jadi berat puluhan
 * MB dan lambat di jaringan seluler — kondisi mayoritas pengunjung. Jadi
 * setiap gambar diperkecil, dibuatkan thumbnail, dan dikonversi ke WebP di
 * sisi server. Admin cukup mengunggah apa adanya dari HP.
 *
 * Catatan API: Intervention 4.2 berbeda dari 4.0 — tidak ada `read()` maupun
 * `ImageManager::gd()`. Yang benar `usingDriver()` + `decodePath()` +
 * `encode()`. Sudah diverifikasi, lihat docs/catatan-pemrosesan-gambar.md.
 */
class ImageService
{
    /** Sisi terpanjang versi web, dalam piksel. */
    private const WEB_MAX_WIDTH = 1200;

    /** Sisi thumbnail (dipotong ke kotak agar grid rapi). */
    private const THUMB_SIZE = 400;

    private const WEB_QUALITY = 85;

    private const THUMB_QUALITY = 80;

    /** Batas ukuran unggahan dalam kilobyte — foto HP biasanya 3–5 MB. */
    public const MAX_UPLOAD_KB = 4096;

    private ImageManagerInterface $manager;

    public function __construct()
    {
        // Driver GD, bukan Imagick: Imagick tidak terpasang di Laragon, dan
        // GD di sini sudah mendukung WebP (diperiksa lewat gd_info()).
        $this->manager = ImageManager::usingDriver(new Driver);
    }

    /**
     * Menyimpan satu gambar beserta thumbnailnya.
     *
     * Mengembalikan jalur relatif versi web. Thumbnailnya bisa ditebak dari
     * jalur itu lewat thumbnailPath().
     *
     * @param  string  $folder  Sub-folder penyimpanan, biasanya slug anak usaha
     */
    public function store(UploadedFile $file, string $folder): string
    {
        // Nama berkas dibuat ulang sistem. Nama asli dari pengguna tidak
        // pernah dipakai — bisa memuat karakter jalur ("../"), karakter yang
        // bermasalah di sistem berkas, atau akhiran ganda yang menyesatkan.
        return $this->storeFromPath(
            $file->getRealPath(),
            $folder,
            Str::uuid()->toString(),
        );
    }

    /**
     * Memproses aset statis dengan nama keluaran yang tetap.
     *
     * Dipakai untuk materi client yang ikut Git. Nama yang deterministik
     * membuat proses impor aman dijalankan ulang tanpa membuat berkas baru
     * dan baris portfolio ganda setiap kali deployment.
     */
    public function storeFromPath(string $source, string $folder, string $name): string
    {
        if (! is_file($source)) {
            throw new InvalidArgumentException("Berkas gambar tidak ditemukan: {$source}");
        }

        $folder = trim($folder, '/');
        $safeName = Str::slug($name);

        if ($folder === '' || $safeName === '') {
            throw new InvalidArgumentException('Folder dan nama gambar tidak boleh kosong.');
        }

        $webPath = "{$folder}/{$safeName}.webp";
        $thumbPath = "{$folder}/{$safeName}_thumb.webp";

        $web = $this->manager->decodePath($source)
            ->scaleDown(self::WEB_MAX_WIDTH)
            ->encode(new WebpEncoder(self::WEB_QUALITY));

        $thumb = $this->manager->decodePath($source)
            ->coverDown(self::THUMB_SIZE, self::THUMB_SIZE)
            ->encode(new WebpEncoder(self::THUMB_QUALITY));

        $disk = Storage::disk('public');
        $disk->put($webPath, (string) $web);
        $disk->put($thumbPath, (string) $thumb);

        return $webPath;
    }

    /**
     * Mengganti gambar lama dengan yang baru, lalu menghapus berkas lama.
     *
     * Tanpa penghapusan ini, folder penyimpanan membengkak oleh berkas yatim
     * yang tidak lagi dirujuk baris mana pun.
     */
    public function replace(UploadedFile $file, string $folder, ?string $oldPath): string
    {
        $newPath = $this->store($file, $folder);

        if ($oldPath !== null && $oldPath !== $newPath) {
            $this->delete($oldPath);
        }

        return $newPath;
    }

    /**
     * Menghapus berkas gambar beserta thumbnailnya.
     *
     * TIDAK dipanggil saat soft delete — data yang ditandai terhapus masih
     * bisa dipulihkan, dan foto produk yang berkasnya sudah dimusnahkan tidak
     * bisa dikembalikan (spec §4).
     */
    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);
        $disk->delete($this->thumbnailPath($path));
    }

    /**
     * Jalur thumbnail untuk sebuah jalur gambar.
     */
    public function thumbnailPath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            return $path.'_thumb';
        }

        return substr($path, 0, -(\strlen($extension) + 1)).'_thumb.'.$extension;
    }

    /**
     * URL publik sebuah gambar. null bila jalurnya kosong, supaya tampilan
     * bisa menampilkan kotak inisial alih-alih ikon rusak (spec §10).
     *
     * Ada dua tempat gambar bisa berada:
     *
     * 1. `storage/app/public/` — unggahan admin. Diakses lewat symlink
     *    /storage, dan TIDAK ikut git.
     * 2. `public/` langsung — aset merek seperti logo yang diproses sekali
     *    lalu tidak berubah. Ikut git, jadi tetap ada setelah repo di-clone.
     *
     * Jalur yang diawali "images/" dianggap milik kelompok kedua.
     */
    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        // Gunakan asset('storage/...') agar URL selalu mengikuti host & port request aktif
        return asset('storage/'.ltrim($path, '/'));
    }
}
