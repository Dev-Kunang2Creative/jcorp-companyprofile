# Catatan Pemrosesan Gambar

**Diverifikasi:** 11 Agustus 2026, dengan berkas sungguhan `Logo-Sweetness.jpeg`.

Menutup kalimat spec §2: *"Library pemrosesan gambar ditentukan dan diverifikasi di Fase 1."*

---

## Yang dipakai

| Paket | Versi | Alasan |
|---|---|---|
| `intervention/image` | 4.2.1 | Jalan di atas GD murni |
| `intervention/image-laravel` | 4.1.1 | Pembungkus resmi, mendukung `illuminate/support ^13` |
| `intervention/gif` | 5.0.1 | Ikut sebagai dependensi |

**Kenapa bukan `spatie/image`:** paket itu bekerja paling baik dengan Imagick, dan `php -m` memastikan **Imagick tidak terpasang** di Laragon ini. `intervention/image` hanya butuh `ext-gd` dan `ext-mbstring`, keduanya aktif.

GD di sini sudah mendukung **WebP dan AVIF** (dicek lewat `gd_info()`), jadi konversi ke format ringan bisa dilakukan tanpa menambah ekstensi PHP.

---

## Hasil pengujian

```
Sumber : Logo-Sweetness.jpeg, 44,4 KB, 828×750
Web    : 800×725 WebP kualitas 85 → 19,6 KB
Thumb  : 300×300 WebP kualitas 80 →  6,3 KB
```

---

## API yang benar untuk versi 4.2

Versi 4.2 **berbeda dari 4.0**. Contoh yang beredar di internet umumnya memakai API lama dan akan gagal:

| Salah (v4.0 / karangan) | Benar (v4.2) |
|---|---|
| `ImageManager::gd()` | `ImageManager::usingDriver(new Driver())` |
| `$manager->read($path)` | `$manager->decodePath($path)` |
| `$image->toWebp(quality: 85)` | `$image->encode(new WebpEncoder(quality: 85))` |

Contoh lengkap yang sudah dijalankan dan terbukti bekerja:

```php
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

$manager = ImageManager::usingDriver(new Driver);

// Versi web — sisi terpanjang dibatasi, rasio dijaga.
// scaleDown() tidak pernah memperbesar gambar yang sudah lebih kecil.
$web = $manager->decodePath($path)
    ->scaleDown(width: 1200)
    ->encode(new WebpEncoder(quality: 85));

// Thumbnail — dipotong ke kotak agar grid rapi.
$thumb = $manager->decodePath($path)
    ->coverDown(400, 400)
    ->encode(new WebpEncoder(quality: 80));
```

---

## Yang masih harus dikerjakan di Fase 3

Fase 1 hanya membuktikan librarynya bekerja. Alur unggah sungguhannya belum ada:

- Validasi berkas — periksa **isi** berkasnya, bukan akhiran nama (spec §5)
- Batas ukuran unggahan
- Nama berkas dibuat ulang sistem; nama asli dari pengguna tidak pernah dipakai
- Penyimpanan ke `storage/app/public` + `php artisan storage:link`
- Menghapus berkas lama saat gambar diganti

## Logo Sweetness — Fase 2

Latar putihnya dihapus lewat proses otomatis, menghasilkan PNG transparan plus
versi lencana bulat. Kalau tepi kurva kaligrafinya ternyata bergerigi, itu jadi
alasan konkret meminta berkas SVG/PNG asli ke pembuat logo (spec §7).
