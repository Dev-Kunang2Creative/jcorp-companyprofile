# PROGRESS — J Corp Company Profile

**Terakhir diperbarui:** 11 Agustus 2026
**Spec:** [`docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md`](docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md)

> Baca spec lebih dulu sebelum mengerjakan apa pun. File ini hanya mencatat **sudah sampai mana**; spec yang menjelaskan **apa yang dibangun dan kenapa**.

---

## Status Fase

| Fase | Isi | Status |
|---|---|---|
| **0** | Brainstorming & spec | ✅ Selesai |
| **1** | Fondasi — Laravel 13 + Inertia + React + MySQL, 4 tabel, auth & peran | ✅ Selesai |
| **2** | Desain — `DESIGN_SYSTEM.md`, proses logo, mockup Sweetness | ✅ Selesai & disetujui |
| **3** | Sweetness Things — halaman profil + panel admin fungsional | ✅ Selesai |
| **4** | Halaman induk J Corp (etalase) — website bisa diakses publik | ✅ Selesai |
| **5** | Empat profil sisanya, menyusul seiring konten terkumpul | ⬜ Menunggu materi dari client |

**Arah visual terpilih:** Butik Pâtisserie dengan panel kaca — gabungan arah A dan B. Mockupnya di `public/preview/mockup.html`.

---

## Yang Sudah Berjalan (Fase 1)

Semua diverifikasi dengan dijalankan, bukan diasumsikan.

| Bagian | Bukti |
|---|---|
| Laravel 13.24 + Inertia 3.3 + React 19 + Tailwind 4 | `npm run build` sukses |
| Empat tabel + enam entitas ter-seed | dicek langsung di MySQL |
| Login di `/jcorp-panel/login` | login sungguhan lewat HTTP berhasil |
| Pengecekan kepemilikan | serangan lewat HTTP → 403 |
| Header `noindex` di seluruh panel | dicek lewat `curl -D -` |
| Pemrosesan gambar | `Logo-Sweetness.jpeg` 44,4 KB → WebP 19,6 KB |
| Perintah `jcorp:make-admin` | dua akun dibuat, validasi peran menolak yang salah |

**Verifikasi menyeluruh — semuanya hijau:**

```
php artisan test        139 lulus
vendor/bin/pint         passed
vendor/bin/phpstan      0 error
npm run types:check     bersih
npm run lint:check      bersih
npm run format:check    bersih
npm run build           sukses
```

## Yang Sudah Berjalan (Fase 3)

| Bagian | Bukti |
|---|---|
| Halaman `/sweetness-things` | dibuka lewat HTTP, 200 |
| Anak usaha belum terbit & slug tak dikenal | keduanya 404 |
| Font Fraunces + Jost di-host sendiri | 6 berkas `.woff2`, CSS tanpa URL eksternal |
| Unggah gambar lewat HTTP sungguhan | JPEG 44 KB → WebP 23 KB + thumbnail 8,7 KB |
| Berkas sampah ditolak | teks bernama `.jpg`, SVG berisi skrip, PHP menyamar PNG |
| Soft delete | baris ditandai terhapus, berkas gambar dipertahankan |
| Aturan section kosong | portfolio kosong → sectionnya hilang |

## Yang Sudah Berjalan (Fase 4)

| Bagian | Bukti |
|---|---|
| Halaman induk `/` | etalase dengan 5 kartu anak usaha |
| Semua tautan kartu bisa dibuka | keenam alamat 200 |
| `/jcorp` tetap 404 | induk hanya punya satu alamat, yaitu `/` |
| Inisial mengabaikan awalan badan usaha | "PT. Ayodya Utama Logistic" → **AU**, bukan PA |
| Kartu hanya yang diterbitkan | dijaga test; yang belum terbit tidak terungkap |

---

## Data Contoh yang Harus Dibersihkan Sebelum Tayang

**Seluruh isi website sekarang karangan** — dimasukkan agar client bisa melihat bentuk jadinya sebelum materi asli terkumpul.

| Jenis | Jumlah | Dikenali dari |
|---|---|---|
| Item katalog | 28 di 5 anak usaha | penanda `__CONTOH__` di kolom `category` |
| Foto portfolio | 7 di Nail's by Me & ngelash.id | jalur `images/placeholder/` |
| Tagline, cerita, kontak, label | 5 anak usaha + induk | dicocokkan isinya dengan `Database\Seeders\SampleContent` |

Masukkan ulang kapan saja dengan `php artisan db:seed --class=SampleContentSeeder`.

Bersihkan dengan:

```
php artisan jcorp:clear-samples
```

**Perintah ini aman terhadap isian sungguhan.** Teks contoh dikenali lewat perbandingan isi — begitu admin menimpanya lewat panel, isinya tidak lagi cocok dan tidak ikut terhapus. Berlaku juga untuk nomor WhatsApp dan alamat. Ada 12 test yang menjaganya.

Tiga hal yang **tidak** disentuh perintah itu:

| | Alasan |
|---|---|
| Logo Sweetness | Berkas asli dari client, bukan contoh |
| Foto yang diunggah admin | Tersimpan di folder slug, bukan `images/placeholder/` |
| Sakelar terbit | Menyembunyikan profil keputusan sadar super-admin |

Label section dikembalikan ke nilai bawaan ("Katalog", "Portfolio"), bukan dikosongkan — halaman publik butuh sebutan untuk sectionnya.

**Akun yang dibuat di komputer ini** (hanya lokal, password ada pada user):

| Email | Peran |
|---|---|
| `owner@jcorp.test` | super_admin |
| `sweetness@jcorp.test` | business_admin → Sweetness Things |

---

## Keputusan yang Sudah Final

Sudah diputuskan user secara sadar. **Jangan ditawar ulang** kecuali user sendiri yang mengangkatnya.

| Keputusan | Alasan singkat |
|---|---|
| URL subfolder (`/sweetness-things`) | Satu domain, SEO induk mengalir ke anak usaha |
| Template seragam untuk 5 anak usaha | Label menyesuaikan lewat kolom database |
| Dua koleksi: katalog + portfolio | Opsional per anak usaha; yang kosong sectionnya hilang |
| Admin per anak usaha (1 admin : 1 usaha) | 1 super-admin + 5 business_admin |
| URL admin `/jcorp-panel` + login | URL tersembunyi = penyamaran, bukan kunci |
| Pendekatan A — bertahap, Sweetness dulu | Satu-satunya yang punya logo |
| Halaman induk = etalase + profil singkat | Data lengkap induk belum ada |
| WhatsApp saja untuk kontak | Form bisa ditambah nanti tanpa membongkar |
| Bahasa Indonesia | 4 dari 5 anak usaha melayani konsumen lokal |
| Tema terang + gradasi lembut + glassmorphism | Permintaan client |
| Emas `#B28C27` untuk Sweetness saja | Anak usaha lain dapat palet sendiri |
| Latar putih logo dihapus otomatis di Fase 2 | JPEG tidak bisa transparan |

### Ditambahkan 11 Agustus 2026

| Keputusan | Alasan |
|---|---|
| Database bernama `jcorp_company_profile` | Sama dengan nama folder |
| TypeScript untuk sisi React | Satu template dipakai 5 anak usaha — salah nama prop ketahuan saat menulis |
| `intervention/image` v4 di atas GD | Imagick tidak terpasang di Laragon ini; GD sudah mendukung WebP/AVIF |
| Scaffold dari `laravel/react-starter-kit` | Versi Laravel/Inertia/React/Tailwind sudah dipastikan cocok oleh tim Laravel |
| ~~J Corp `is_published: true`, lima anak usaha `false`~~ | **Diubah di Fase 4:** semuanya diterbitkan dengan tagline contoh, supaya grid etalase bisa dinilai dengan lima kartu. Sembunyikan lagi lewat panel kalau perlu |
| Fase 1 mencakup lapisan controller | Tanpa alamat HTTP nyata, test kepemilikan tidak membuktikan apa-apa |
| Test memakai MySQL, bukan SQLite | `enum` dan foreign key berbeda perilaku; test SQLite tidak membuktikan apa-apa |
| Fitur hapus-akun-sendiri dibuang | Business_admin yang menghapus akunnya sendiri meninggalkan anak usahanya tanpa pengelola, dan `users` tidak pakai soft delete |

---

## Lingkungan

| Komponen | Versi |
|---|---|
| PHP | 8.3.30 (Laragon) |
| MySQL | 8.4.3 (Laragon) |
| Node | 22.15.0 |
| Composer | 2.8.6 |

**PATH — sudah diselesaikan 11 Agustus 2026.** Folder PHP Laragon didaftarkan ke **PATH user** komputer ini, jadi `php`, `composer`, dan `php artisan` sekarang jalan di terminal mana pun. Cadangan PATH lama ada di `~\path-user-backup-2026-08-11.txt`.

Kalau Laragon meng-upgrade PHP nanti, nama foldernya berubah dan entri PATH ini perlu disesuaikan. Caranya dicatat di README.

**`PHP_BINARY` di `.env` wajib diisi** — Vite memanggil `php artisan` sendiri saat build lewat plugin Wayfinder. Tanpa itu `npm run build` gagal dengan `'php' is not recognized`.

Project ada di drive D, di luar `C:\laragon\www` — jadi tidak dapat alamat `.test` otomatis. Server dijalankan dengan `php artisan serve`.

---

## Tiga Hal yang Ditemukan Saat Fase 1

Dicatat karena ketiganya tidak terlihat dari membaca kode, dan bisa menghabiskan waktu kalau ditemukan ulang.

**1. `composer create-project laravel/react-starter-kit` mengambil versi lama.**
Tag rilis terbarunya v1.0.1 (Februari 2025) — Laravel 12, tanpa Fortify, tanpa Chisel. Harus pakai `--stability=dev`.

**2. Header `noindex` tidak sampai ke respons redirect.**
Redirect ke login dari middleware `auth` dibuat lewat exception di dalam pipeline route, yang lebih dalam daripada middleware `web`. Halaman login sendiri juga di luar grup route panel karena route-nya milik Fortify. Sekarang ditangani di dua tempat: middleware `web` untuk respons normal, dan hook `respond()` di `bootstrap/app.php` untuk respons hasil exception. Ini ketahuan saat diuji lewat HTTP sungguhan — test awal saya lolos karena hanya menguji keadaan sudah login.

**3. API `intervention/image` 4.2 berbeda dari 4.0.**
Contoh yang beredar umumnya memakai `ImageManager::gd()`, `read()`, dan `toWebp()` — ketiganya tidak ada. Yang benar dicatat di [`docs/catatan-pemrosesan-gambar.md`](docs/catatan-pemrosesan-gambar.md).

---

## Tiga Hal yang Ditemukan Saat Fase 3

**1. PHP tidak mengurai `multipart/form-data` pada request PUT.**
Form ubah yang mengunggah berkas harus mengirim POST dengan `_method=put`. Ini tidak terlihat dari test biasa: `$this->put()` milik Laravel mem-*bypass* PHP dan menyuntikkan berkas langsung, jadi lulus di test padahal gagal di browser. Ada test khusus (`test_an_update_sent_as_post_with_method_override_still_carries_the_file`) yang menguji jalur browser sungguhan.

**2. `UploadedFile::fake()->createWithContent()` memalsukan MIME type.**
Helper itu menyetel `image/jpeg` alih-alih membiarkannya dideteksi dari isi berkas, jadi berkas sampah *lolos* validasi di test padahal ditolak di kenyataan — kebalikan dari yang mau dibuktikan. Test unggahan memakai berkas sungguhan di disk lewat helper `realFile()`.

**3. `respondUsing` hanya menyimpan SATU callback.**
Inertia `handleExceptionsUsing()` memakai hook yang sama dengan header `noindex`. Kalau halaman error didaftarkan terpisah, header noindex hilang diam-diam. Keduanya digabung dalam satu callback di `bootstrap/app.php`.

---

## Menggantung — Perlu Jawaban User

**Ayodya Logistic: melayani klien asing / pengiriman internasional?** Menentukan apakah perlu Bahasa Inggris nanti. Sekarang diasumsikan tidak.

**Materi konten** — ini yang menentukan kapan website bisa online, bukan kecepatan menulis kode.

| Entitas | Logo | Foto | Teks |
|---|---|---|---|
| J Corp | ❌ | ❌ | ❌ |
| Sweetness Things | ⚠️ JPEG, perlu transparan | ❌ | ❌ |
| Nail's by Me | ❌ | ❌ | ❌ |
| ngelash.id | ❌ | ❌ | ❌ |
| Ayodya Logistic | ❌ | ❌ | ❌ |
| Lumintu Property | ❌ | ❌ | ❌ |

Daftar rinci per anak usaha ada di **bagian 15 spec**.

---

## Aturan Kerja

Sesuai `CLAUDE.md` (global, berlaku otomatis):

| Tahap | Pemilik |
|---|---|
| Spec | `superpowers:brainstorming` — selesai |
| Design system & mockup | `professional-designer` |
| Rencana implementasi | `superpowers:writing-plans` |
| Kode | `professional-programmer` |
| Verifikasi sebelum klaim selesai | `superpowers:verification-before-completion` |
| Bug | `superpowers:systematic-debugging` |

**Jangan commit atau push tanpa diminta eksplisit.** Repo sengaja dibiarkan tanpa commit.

**TDD bukan Iron Law di project ini.** Test wajib untuk logika nyata — terutama pengecekan kepemilikan antar admin. Tidak wajib untuk markup, styling, konten statis, dan konfigurasi.

---

## Keterbatasan Sesi

**Membaca gambar lewat path file GAGAL** — dicoba di Fase 2 dengan `Logo-Sweetness.jpeg`, hasilnya kosong. Catatan asli dari Fase 0 ternyata benar; koreksi yang sempat ditulis di Fase 1 keliru dan sudah dicabut.

Akibatnya, analisis logo dikerjakan lewat **pembacaan piksel dengan GD** (warna dominan, HSL, kualitas tepi) alih-alih melihat gambarnya. Itu cukup untuk keputusan warna dan pemrosesan latar, tapi **penilaian akhir apakah logonya terlihat bagus tetap perlu mata user** — itu sebabnya `public/preview/logo.html` dibuat.

---

## Riwayat

**11 Agustus 2026 — Data contoh lengkap + perbaikan logo**
- **Logo Sweetness akhirnya dipakai.** Berkasnya sudah diproses di Fase 2 tapi `logo_path` tidak pernah diisi, jadi menganggur — kelalaian yang ditemukan user
- `ImageService::url()` kini membedakan aset tetap di `public/` dari unggahan admin di `storage/`
- Seluruh anak usaha diisi data contoh: 28 item katalog, 7 foto portfolio, kontak lengkap
- Dua seeder lama digabung jadi `SampleContentSeeder`
- `jcorp:clear-samples` diperluas ke kontak dan foto portfolio; 139 test lulus

**11 Agustus 2026 — Fase 4 selesai**
- Halaman induk `/` — hero, profil singkat, dan grid kartu anak usaha
- `SubsidiaryCard` dengan lencana inisial untuk yang belum punya logo
- Empat anak usaha lain diterbitkan dengan tagline contoh, supaya grid bisa dinilai
- `jcorp:clear-samples` diperluas: ikut mengosongkan tagline & cerita contoh, dengan perbandingan isi supaya tulisan admin aman
- 133 test lulus (naik dari 113)

**11 Agustus 2026 — Fase 3 selesai**
- Token design system dituangkan ke `app.css`; font diganti ke Fraunces + Jost
- `ImageService` — validasi isi berkas, perkecil 1200px, thumbnail 400×400, konversi WebP
- Halaman publik `/sweetness-things` beserta 9 komponen yang bisa dipakai ulang di Fase 5
- Halaman error 404/500 bergaya website
- Panel admin ditulis ulang: unggah berpratinjau, dialog hapus yang menyebut nama item
- Seeder data contoh + perintah `jcorp:clear-samples`
- 113 test lulus (naik dari 76); Pint, PHPStan, tsc, ESLint, Prettier, build semuanya bersih

**11 Agustus 2026 — Fase 2 selesai**
- Logo Sweetness diproses: bingkai hitam 2px dibuang, latar putih dihapus, halo putih di tepi dihilangkan (dari 88,8% jadi 0%)
- Berkas logo dibuat di `public/images/brand/` — PNG/WebP transparan + lencana bulat sampai 48px
- Tiga arah visual disodorkan; user memilih **arah A dengan panel kaca dari arah B**
- Kontras diuji menyeluruh: 11 pasangan warna, satu gagal dan diperbaiki
- Ditemukan: emas logo `#B28C27` gagal AA sebagai teks → dipecah jadi tiga peran
- `DESIGN_SYSTEM.md` dan `docs/design-tokens.css` ditulis
- Mockup desktop + mobile di `public/preview/mockup.html`

**11 Agustus 2026 — Fase 1 selesai**
- Scaffold `laravel/react-starter-kit` (`--stability=dev`), fitur auth berlebih dipangkas lewat Chisel resmi
- Sisa bawaan dibuang: dark mode, halaman welcome/dashboard, hapus-akun-sendiri, komponen header tak terpakai
- Empat tabel + model + policy + enum peran + factory + seeder enam entitas
- Panel `/jcorp-panel` dengan pengecekan kepemilikan dua sisi (baca dan tulis)
- Perintah `php artisan jcorp:make-admin`
- 76 test lulus; Pint, PHPStan, tsc, ESLint, Prettier, dan build semuanya bersih
- Diuji lewat HTTP sungguhan: login berhasil, serangan lintas anak usaha ditolak 403
- `intervention/image` diverifikasi dengan `Logo-Sweetness.jpeg`

**10 Agustus 2026 — Fase 0**
- Brainstorming selesai, spec ditulis dan ditinjau (3 ambiguitas diperbaiki inline)
- Repo git diinisialisasi di branch `main`
- Plugin Superpowers 6.2.0 dipasang, telemetri dimatikan
- Aturan pembagian peran ditulis ke `CLAUDE.md` bagian 4
- Project dipindah ke lokasi sekarang
- Lingkungan diverifikasi: Laragon PHP 8.3.30, XAMPP dikeluarkan dari PATH
