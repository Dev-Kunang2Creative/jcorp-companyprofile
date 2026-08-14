# J Corp — Company Profile

Satu website berisi enam company profile: J Corp sebagai induk, dan lima anak usaha di bawahnya. Dilengkapi panel admin tersembunyi untuk mengelola katalog, portfolio, dan info kontak.

**Status:** Fase 1–4 selesai. Website sudah bisa diakses: halaman induk, profil Sweetness Things, dan panel admin yang berfungsi.

⚠️ **Isi website saat ini adalah data contoh.** Bersihkan dengan `php artisan jcorp:clear-samples` sebelum tayang sungguhan.

| Dokumen | Isi |
|---|---|
| [`docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md`](docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md) | Spesifikasi utama — **baca ini dulu** |
| [`PROGRESS.md`](PROGRESS.md) | Sudah sampai mana, dan keputusan apa yang sudah final |
| [`docs/catatan-pemrosesan-gambar.md`](docs/catatan-pemrosesan-gambar.md) | API library gambar yang sudah diverifikasi |

---

## Menjalankan di komputer sendiri

### Yang harus ada lebih dulu

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | 8.3+ | dengan `gd`, `intl`, `pdo_mysql`, `mbstring`, `fileinfo`, `exif`, `zip` |
| MySQL | 8.0+ | |
| Node | 22+ | |
| Composer | 2.8+ | |

Project ini dikembangkan di **Laragon** (PHP 8.3.30, MySQL 8.4.3).

### Catatan PATH — penting di Laragon

Laragon tidak mendaftarkan PHP ke PATH sistem, jadi terminal biasa (termasuk terminal VS Code) menolak dengan:

```
php : The term 'php' is not recognized as the name of a cmdlet...
```

**Solusi yang dipakai di komputer ini** — folder PHP Laragon didaftarkan ke **PATH user** (bukan PATH sistem, jadi tidak butuh hak admin):

```powershell
$phpDir = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64"
$old = [Environment]::GetEnvironmentVariable('Path', 'User')
[Environment]::SetEnvironmentVariable('Path', $old.TrimEnd(';') + ';' + $phpDir, 'User')
```

**Tutup VS Code sepenuhnya lalu buka lagi** setelah menjalankannya — membuka tab terminal baru saja tidak cukup, karena VS Code mewariskan PATH yang dibacanya saat aplikasi dimulai.

> Kalau nanti Laragon meng-upgrade PHP, nama foldernya ikut berubah (`php-8.4.x-...`) dan baris PATH ini perlu disesuaikan.

**Dua alternatif** kalau tidak ingin mengubah PATH:

- Jalankan lewat **Laragon Terminal** (tombol *Terminal* di jendela Laragon) — di sana PHP sudah dikenali
- Panggil dengan path lengkap: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan ...`

Apa pun caranya, isi **`PHP_BINARY` di `.env`** — Vite memanggil `php artisan` sendiri saat build, dan tanpa itu `npm run build` gagal dengan `'php' is not recognized`:

```
PHP_BINARY="C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe"
```

### Langkah pemasangan

**1. Nyalakan MySQL** lewat tombol *Start All* di Laragon.

**2. Buat dua database:**

```sql
CREATE DATABASE jcorp_company_profile      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE jcorp_company_profile_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Yang kedua dipakai test. Test memakai MySQL sungguhan, bukan SQLite di memori — kolom `role` berupa `enum` dan perilaku foreign key berbeda antara keduanya, jadi test yang lulus di SQLite tidak membuktikan apa-apa.

**3. Pasang dependensi dan siapkan aplikasi:**

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Lalu sesuaikan `.env` — `DB_USERNAME`, `DB_PASSWORD`, dan `PHP_BINARY`.

**4. Isi database:**

```bash
php artisan migrate --seed
```

Seeder mengisi enam entitas (J Corp + lima anak usaha). **Tidak ada akun admin yang dibuat** — akun dengan password yang bisa ditebak berbahaya kalau seeder ikut jalan di server sungguhan.

**5. Buat akun super-admin pertama:**

```bash
php artisan jcorp:make-admin
```

Interaktif — menanyakan nama, email, peran, dan anak usaha. Password diminta dalam mode tersembunyi.

Perintah ini hanya diperlukan untuk **akun pertama**. Setelah ada satu super-admin, admin berikutnya diundang lewat panel — lihat [Kelola Akun](#kelola-akun) di bawah.

**6. Jalankan:**

```bash
composer dev
```

Menjalankan server, queue, log viewer, dan Vite sekaligus. Kalau ingin terpisah:

```bash
php artisan serve      # http://localhost:8000
npm run dev            # di terminal lain
```

Panel admin ada di **`/jcorp-panel`**.

---

## Perintah yang sering dipakai

| Perintah | Kegunaan |
|---|---|
| `php artisan jcorp:make-admin` | Membuat akun admin |
| `php artisan test` | Menjalankan seluruh test |
| `vendor/bin/pint` | Merapikan gaya kode PHP |
| `vendor/bin/phpstan analyse` | Analisis statis PHP |
| `npm run types:check` | Type-check TypeScript |
| `npm run lint` | ESLint + perbaikan otomatis |
| `npm run format` | Prettier |
| `npm run build` | Build aset produksi |
| `composer ci:check` | Menjalankan semuanya sekaligus |

---

## Struktur

```
app/
├── Console/Commands/MakeAdminCommand.php   pembuatan akun
├── Enums/UserRole.php                      super_admin | business_admin
├── Http/
│   ├── Controllers/Panel/                   panel admin
│   ├── Middleware/
│   │   ├── EnsureUserIsSuperAdmin.php
│   │   ├── HandleInertiaRequests.php        props bersama
│   │   └── NoIndexPanel.php                 header noindex
│   └── Requests/Panel/                      validasi form
├── Models/                                  Business, CatalogItem, PortfolioItem, User
└── Policies/                                pengecekan kepemilikan

database/
├── factories/     dipakai test
├── migrations/    4 tabel
└── seeders/       enam entitas

resources/js/
├── layouts/       app (sidebar) + auth
├── pages/
│   ├── auth/      login, lupa password, reset
│   ├── panel/     halaman admin — POLOS, ditulis ulang di Fase 3
│   └── settings/  profil akun, ganti password
└── types/

routes/
├── panel.php      /jcorp-panel/*
├── settings.php   pengaturan akun
└── web.php        route publik

tests/Feature/
├── Auth/          login, throttle, reset password
├── Console/       jcorp:make-admin
├── Panel/         kepemilikan, akses, anak usaha belum terbit
└── Settings/
```

**Halaman di `resources/js/pages/panel/` sengaja polos**, tanpa gaya. Fungsinya membuktikan alur controller dan pengecekan kepemilikan bekerja lewat HTTP sungguhan. Tampilan sebenarnya ditulis di Fase 3 di atas design system.

---

## Versi yang dikunci

Terpasang dan terverifikasi 11 Agustus 2026.

| Paket | Versi |
|---|---|
| `laravel/framework` | 13.24.0 |
| `inertiajs/inertia-laravel` | 3.3.1 |
| `laravel/fortify` | 1.37.3 |
| `laravel/wayfinder` | 0.1.21 |
| `intervention/image` | 4.2.1 |
| `intervention/image-laravel` | 4.1.1 |
| `react` / `react-dom` | 19.2.8 |
| `@inertiajs/react` | 3.6.1 |
| `tailwindcss` | 4.3.3 |
| `vite` | 8.2.1 |
| `typescript` | 5.9.3 |

**Catatan pemasangan:** starter kit `laravel/react-starter-kit` harus dipasang dengan `--stability=dev`. Tag rilis terbarunya (v1.0.1) masih Laravel 12 tanpa Fortify.

---

## Keamanan

**Alamat panel tersembunyi adalah penyamaran, bukan kunci.** Alamat bocor lewat riwayat browser, header `Referer`, log server, atau sekali salah kirim link. Yang melindungi adalah lapisan di baliknya:

- **Pengecekan kepemilikan di server.** Setiap simpan/ubah/hapus diperiksa lewat Policy. Menyembunyikan tombol di tampilan tidak dianggap pengamanan — permintaan tetap bisa dikirim langsung dengan mengubah angka di alamat.
- **Sisi baca juga dijaga.** Business_admin hanya menerima data miliknya; data anak usaha lain tidak pernah dikirim ke browsernya.
- **`role` dan `business_id` tidak fillable.** Kalau ikut terisi dari form, seorang admin bisa menaikkan dirinya jadi super_admin dengan menambah satu field di request.
- **Tidak ada halaman pendaftaran.** Akun dibuat lewat undangan dari super-admin atau perintah artisan; password tidak pernah melintas sebagai argumen perintah maupun lewat form super-admin.
- **Akun nonaktif langsung ditolak**, bukan menunggu sesinya kedaluwarsa — diperiksa di setiap permintaan, bukan hanya saat login.
- **Percobaan login dibatasi** 5 kali per menit per kombinasi email + IP.
- **Header `noindex`** di seluruh `/jcorp-panel/*`, termasuk halaman login dan setiap redirect.

`.env` tidak pernah di-commit. Tidak ada satu pun kredensial di dalam kode.

---

## Kelola Akun

Super-admin bisa mengundang admin baru lewat panel, tanpa perlu SSH.

**Cara kerjanya:**

1. Super-admin mengisi nama, email, dan anak usaha — **tanpa password**
2. Sistem membuat tautan sekali pakai yang disalin dan dikirim lewat WhatsApp
3. Admin baru membuka tautan itu dan membuat passwordnya sendiri

Password tidak pernah diketahui super-admin dan tidak pernah melewati form panel. Ini menghormati alasan di balik spec §6 — password tidak boleh melintas lewat form web — tanpa mempertahankan keterbatasannya.

| | |
|---|---|
| Masa berlaku tautan | 7 hari, sekali pakai |
| Token di database | Disimpan sebagai hash, bukan apa adanya |
| Kalau tautan kedaluwarsa | Tombol **Buat tautan baru** di baris akunnya |

**Menonaktifkan akun** mencabut akses seketika — bahkan kalau orangnya sedang login, permintaan berikutnya langsung ditolak. Datanya tidak dihapus, jadi bisa dinyalakan lagi kapan saja.

Dua hal yang **tidak** bisa dilakukan lewat panel, sengaja:

- **Menghapus akun.** Tabel `users` tidak memakai soft delete, jadi sekali hilang tidak bisa dipulihkan. "Nonaktifkan" menjawab hampir semua alasan ingin menghapus.
- **Memindahkan admin antar anak usaha.** Aturannya satu anak usaha satu admin; memindahkan berarti yang lama jadi tanpa pengelola. Caranya: nonaktifkan yang lama, undang yang baru.

`php artisan jcorp:make-admin` tetap ada sebagai jalan membuat super-admin pertama, dan jalan keluar kalau semua super-admin kehilangan akses.

---

## Test

```bash
php artisan test
```

197 test. Yang dijaga (spec §11):

**Kepemilikan & akses**

- Admin anak usaha A tidak bisa mengubah/menghapus milik B, termasuk saat ID di alamat diganti manual
- Admin tidak menerima data anak usaha lain dalam props
- Pembatasan akses panel bagi yang belum login, dan pembatasan menu super-admin
- Konteks super-admin bertahan antar menu; business_admin tidak bisa menggesernya
- Header `noindex` di panel, dan **tidak ada** di halaman publik

**Halaman publik**

- Section kosong tidak dirender — termasuk katalog yang seluruh itemnya disembunyikan
- Anak usaha belum terbit mengembalikan 404, sama persis dengan slug tak dikenal
- Kartu di etalase hanya menampilkan yang sudah diterbitkan
- `/jcorp-panel` tidak tertangkap route `/{slug}`
- Format harga Rupiah

**Unggah gambar**

- Berkas bukan gambar ditolak walau dinamai `.jpg` — termasuk SVG berisi skrip dan PHP menyamar PNG
- Nama berkas dibuat ulang sistem; nama asli tidak pernah dipakai
- Berkas lama dihapus saat gambar diganti, tapi dipertahankan saat soft delete
- Unggahan lewat POST + `_method=put` membawa berkasnya (jalur browser sungguhan)

**Kelola akun**

- Undangan membuat akun tanpa password; token disimpan sebagai hash
- Tautan undangan hanya bisa dipakai sekali, dan mati setelah kedaluwarsa
- Akun nonaktif **langsung** tidak bisa mengubah atau menghapus data
- Akun yang undangannya belum dibuka tidak bisa masuk sama sekali
- Super-admin tidak bisa menonaktifkan akunnya sendiri
- business_admin ditolak di seluruh route kelola akun

**Pesan validasi**

- Tidak ada pesan yang masih berupa kunci mentah seperti `validation.required`

**Data contoh**

- `jcorp:clear-samples` tidak menghapus tulisan yang sudah ditimpa admin

Tampilan, susunan CSS, dan isi teks tidak diuji — diverifikasi lewat mata di browser.
