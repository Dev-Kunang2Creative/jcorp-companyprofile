# PROGRESS — J-Corporate Group

**Terakhir diperbarui:** 27 Agustus 2026
**Spec:** [`docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md`](docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md)

> Baca spec lebih dulu sebelum mengerjakan apa pun. File ini hanya mencatat **sudah sampai mana**; spec yang menjelaskan **apa yang dibangun dan kenapa**.

---

## Status Fase

| Fase    | Isi                                                                      | Status                                                   |
| ------- | ------------------------------------------------------------------------ | -------------------------------------------------------- |
| **0**   | Brainstorming & spec                                                     | ✅ Selesai                                               |
| **1**   | Fondasi — Laravel 13 + Inertia + React + MySQL, 4 tabel, auth & peran    | ✅ Selesai                                               |
| **2**   | Desain — `DESIGN_SYSTEM.md`, proses logo, mockup Sweetness               | ✅ Selesai & disetujui                                   |
| **3**   | Sweetness Things — halaman profil + panel admin fungsional               | ✅ Selesai                                               |
| **4**   | Halaman induk (etalase) — website bisa diakses publik                    | ✅ Selesai                                               |
| **5**   | Materi asli keenam entitas                                               | ✅ Selesai 22 Agu — semua sudah masuk                    |
| **6**   | **Arah visual A+B** — putih, garis rambut, aksen per unit, animasi       | ✅ Selesai 23 Agu — publik & panel                       |
| **6.1** | **Luminous Editorial Glass** — revisi khusus homepage induk              | ✅ Implementasi 23 Agu — child page & panel tidak diubah |
| **6.2** | **One Family, Five Signatures** — lima identitas dalam satu family shell | ✅ Implementasi 24 Agu — seluruh anak usaha aktif        |
| **6.3** | **Five Editorial Worlds — Luminous Pastel Edition**                      | ✅ Implementasi 24 Agu — desktop & mobile                |
| **6.4** | **Calm Operations Studio** — revisi sistem antarmuka panel admin         | ✅ Implementasi 25 Agu — logika tetap dipertahankan       |

**Cakupan Fase 6 — apa saja yang sudah pindah:**

| Bagian                                              | Status |
| --------------------------------------------------- | ------ |
| Halaman induk                                       | ✅     |
| Kelima halaman anak usaha                           | ✅     |
| Panel admin (6 halaman)                             | ✅     |
| Halaman auth (login, lupa/reset password, undangan) | ✅     |
| Halaman error 404/500                               | ✅     |

> Homepage induk memakai Luminous Editorial Glass. Kelima halaman anak usaha memakai Five Editorial Worlds — Luminous Pastel Edition. Panel admin memakai Calm Operations Studio; auth dan error tetap mengikuti arah A+B — lihat DESIGN_SYSTEM §-1, §0.A, dan §-0.3. §-0.5 serta §-0.4 tetap menyimpan kontrak data terdahulu yang masih berlaku.

### Pembaruan 27 Agustus 2026 — Keterbacaan navbar publik

- Navbar induk dan kelima anak usaha memakai opasitas latar **94%** agar tulisan section di belakang tidak mengganggu logo/menu. Tint serta identitas tiap brand tetap dipertahankan; panel glassmorphism lainnya tidak diubah.
- Navbar memakai satu permukaan backdrop blur. Aturan standar dan fallback Safari dipisahkan karena minifier sebelumnya hanya menyisakan `-webkit-backdrop-filter`, sehingga blur tidak aktif di browser pengujian. Blur induk `16px`, anak usaha tetap mengikuti batas brand, dan mobile maksimal `8px`.
- Menu hamburger terbuka memakai latar solid tanpa blur, termasuk tablet. Menu Sweetness yang terbuka memakai radius `1.25rem` sampai `70rem` agar logo/menu/tombol tutup tidak berada di luar kapsul; bentuk navbar tertutup dan dimensinya tetap.
- Verifikasi browser lokal: keenam halaman diperiksa pada desktop `1440 × 900` dan mobile `390 × 844`, termasuk scroll dan buka/tutup menu. Nilai latar/blur terkonfirmasi melalui computed styles; tidak ditemukan overflow horizontal pada pemeriksaan tersebut. Tambahan pemeriksaan tablet `1024 × 900` untuk Sweetness/J-Land dan layar `320px` untuk Sweetness/Ayodya, serta switch bahasa Ayodya dan penutupan menu via Escape. Safari/iPhone fisik belum diuji.
- TypeScript, ESLint, Prettier untuk file frontend yang berubah, `git diff --check`, dan build production lulus. `public/build` diperbarui melalui build, bukan diedit manual. Pengujian backend penuh tidak dijalankan ulang karena perubahan hanya pada material navbar dan class menu terbuka.
- Tidak ada perubahan pada kata/data client, urutan section, route, panel admin, atau logika backend. Tidak menjalankan migrasi, seeder, maupun perubahan isi database. Perubahan masih lokal; belum di-commit, di-push, atau diterapkan ke Hostinger testing.

### Pembaruan 25 Agustus 2026 — Panel admin Calm Operations Studio

- Sidebar dikelompokkan menjadi **Ringkasan**, **Konten**, dan **Manajemen** tanpa mengubah pembatasan menu berdasarkan peran.
- Konteks anak usaha dipindahkan ke topbar global agar super-admin selalu melihat usaha yang sedang dikelola; perpindahannya tetap memakai alur query dan sesi yang sudah ada.
- Dashboard menjadi halaman kerja dengan status publik, aksi cepat, ringkasan konten, dan peringatan kelengkapan data.
- Daftar katalog, galeri, anak usaha, dan akun memakai tabel pada desktop serta kartu pada mobile.
- Form tambah/edit memakai drawer kanan pada desktop dan layar penuh pada mobile; konfirmasi destruktif tetap memakai dialog terpisah.
- Halaman Kontak & Label memakai dua kolom pada desktop, satu kolom pada mobile, serta tombol simpan sticky hanya ketika ada perubahan.
- Tidak ada perubahan pada route, otorisasi, validasi, payload CRUD, konfirmasi penerbitan, maupun logika undangan akun.
- Verifikasi otomatis 25 Agustus: TypeScript, ESLint, Prettier, Pint, PHPStan, dan build production bersih; **321 test / 1.316 assertion lulus**. Browser berhasil memuat frontend lokal, tetapi layar admin terautentikasi belum diaudit visual karena tidak ada sesi login aktif dan autentikasi tidak dibypass.
- Bug tautan undangan diperbaiki: halaman Akun & Akses sekarang membaca payload dari kanal `flash` Inertia v3, sehingga dialog Salin Tautan muncul setelah membuat maupun memperbarui undangan. Regression test mencakup kedua alur tersebut.

**Materi client — sudah masuk atau belum:**

| Anak usaha                | Materi         | Catatan                                                                    |
| ------------------------- | -------------- | -------------------------------------------------------------------------- |
| J-Corporate Group (induk) | ✅ 21 Agu 2026 | Tentang, visi, misi, logo, slogan, Hubungi Kami. **Email/WA/alamat belum** |
| Sweetness Things          | ✅ 21 Agu 2026 | Lengkap kecuali harga produk dan foto                                      |
| ngelash.id                | ✅ 22 Agu 2026 | Profil, logo, 15 layanan + harga. **Foto hasil kerja belum**               |
| J-Land Property           | ✅ 22 Agu 2026 | Profil lengkap + logo (23 Agu). **Daftar unit & harga belum**              |
| Nail's by Me              | ✅ 22 Agu 2026 | Profil + logo. **Rincian harga & foto belum**                              |
| PT. Ayodya Utama Logistic | ✅ 22 Agu 2026 | Profil, logo, 4 mode transportasi. **Tarif belum**                         |

> **Keenam entitas sudah punya materi asli client, dan keenamnya sudah punya logo** (J-Land terakhir, 23 Agustus). Tidak ada lagi teks karangan maupun lencana inisial yang tayang. Yang tersisa hanya pelengkap: harga dan foto.

**Yang perlu ditanyakan ke client:**

| Hal                                               | Kenapa                                                                                                                                           |
| ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| Daftar harga Sweetness                            | Kartu produk sekarang "hubungi kami" tanpa angka                                                                                                 |
| Foto hasil kerja ngelash & Nail's by Me           | Sakelar portfolio menyala, tinggal diunggah lewat panel                                                                                          |
| Rincian harga Nail's by Me                        | Hanya "Mulai dari Rp 30.000", belum ada per layanan                                                                                              |
| **Nomor WA Nail's by Me = nomor Sekar di J-Land** | `0812-2553-9182` sama persis di dua unit usaha. Satu orang mengurus dua, atau salah salin?                                                       |
| **Nomor WA induk `081176265`**                    | Sembilan digit. Nomor Ayodya `0811276265` **sepuluh digit dan mirip sekali** — kemungkinan besar nomor yang sama, dan versi induk yang terpotong |
| Tarif Ayodya                                      | Section "Layanan Kami" kosong; lima layanan karangan sudah dihapus                                                                               |
| Instagram PT. Ayodya Utama Logistic               | Tidak ada di materi mana pun, termasuk daftar akun induk                                                                                         |
| Daftar unit & harga J-Land                        | Section "Unit Tersedia" kosong                                                                                                                   |
| Nomor WA kedua Sweetness `0881-3742-352`          | Sebelas digit; mungkin benar, mungkin terpotong                                                                                                  |
| Email & alamat induk                              | Belum diisi di materi yang dikirim                                                                                                               |
| Foto produk                                       | Kartu katalog sekarang memakai kotak inisial                                                                                                     |

**Arah visual terpilih:** Butik Pâtisserie dengan panel kaca — gabungan arah A dan B. Mockupnya dulu di `public/preview/` — dihapus sebelum deploy karena akan bisa dibuka publik; masih ada di riwayat git commit `edb9419`.

---

## Yang Sudah Berjalan (Fase 1)

Semua diverifikasi dengan dijalankan, bukan diasumsikan.

| Bagian                                              | Bukti                                              |
| --------------------------------------------------- | -------------------------------------------------- |
| Laravel 13.24 + Inertia 3.3 + React 19 + Tailwind 4 | `npm run build` sukses                             |
| Empat tabel + enam entitas ter-seed                 | dicek langsung di MySQL                            |
| Login di `/jcorp-panel/login`                       | login sungguhan lewat HTTP berhasil                |
| Pengecekan kepemilikan                              | serangan lewat HTTP → 403                          |
| Header `noindex` di seluruh panel                   | dicek lewat `curl -D -`                            |
| Pemrosesan gambar                                   | `Logo-Sweetness.jpeg` 44,4 KB → WebP 19,6 KB       |
| Perintah `jcorp:make-admin`                         | dua akun dibuat, validasi peran menolak yang salah |

**Verifikasi menyeluruh — semuanya hijau:**

```
php artisan test        197 lulus
vendor/bin/pint         passed
vendor/bin/phpstan      0 error
npm run types:check     bersih
npm run lint:check      bersih
npm run format:check    bersih
npm run build           sukses
```

## Yang Sudah Berjalan (Fase 3)

| Bagian                                     | Bukti                                                   |
| ------------------------------------------ | ------------------------------------------------------- |
| Halaman `/sweetness-things`                | dibuka lewat HTTP, 200                                  |
| Anak usaha belum terbit & slug tak dikenal | keduanya 404                                            |
| Font Fraunces + Jost di-host sendiri       | 6 berkas `.woff2`, CSS tanpa URL eksternal              |
| Unggah gambar lewat HTTP sungguhan         | JPEG 44 KB → WebP 23 KB + thumbnail 8,7 KB              |
| Berkas sampah ditolak                      | teks bernama `.jpg`, SVG berisi skrip, PHP menyamar PNG |
| Soft delete                                | baris ditandai terhapus, berkas gambar dipertahankan    |
| Aturan section kosong                      | portfolio kosong → sectionnya hilang                    |

## Yang Sudah Berjalan (Fase 4)

| Bagian                                 | Bukti                                          |
| -------------------------------------- | ---------------------------------------------- |
| Halaman induk `/`                      | etalase dengan 5 kartu anak usaha              |
| Semua tautan kartu bisa dibuka         | keenam alamat 200                              |
| `/jcorp` tetap 404                     | induk hanya punya satu alamat, yaitu `/`       |
| Inisial mengabaikan awalan badan usaha | "PT. Ayodya Utama Logistic" → **AU**, bukan PA |
| Kartu hanya yang diterbitkan           | dijaga test; yang belum terbit tidak terungkap |

---

## Data Contoh yang Harus Dibersihkan Sebelum Tayang

**Seluruh isi website sekarang karangan** — dimasukkan agar client bisa melihat bentuk jadinya sebelum materi asli terkumpul.

| Jenis                          | Jumlah                         | Dikenali dari                                             |
| ------------------------------ | ------------------------------ | --------------------------------------------------------- |
| Item katalog                   | 28 di 5 anak usaha             | penanda `__CONTOH__` di kolom `category`                  |
| Foto portfolio                 | 7 di Nail's by Me & ngelash.id | jalur `images/placeholder/`                               |
| Tagline, cerita, kontak, label | 5 anak usaha + induk           | dicocokkan isinya dengan `Database\Seeders\SampleContent` |

Masukkan ulang kapan saja dengan `php artisan db:seed --class=SampleContentSeeder`.

Bersihkan dengan:

```
php artisan jcorp:clear-samples
```

**Perintah ini aman terhadap isian sungguhan.** Teks contoh dikenali lewat perbandingan isi — begitu admin menimpanya lewat panel, isinya tidak lagi cocok dan tidak ikut terhapus. Berlaku juga untuk nomor WhatsApp dan alamat. Ada 12 test yang menjaganya.

Tiga hal yang **tidak** disentuh perintah itu:

|                          | Alasan                                                |
| ------------------------ | ----------------------------------------------------- |
| Logo Sweetness           | Berkas asli dari client, bukan contoh                 |
| Foto yang diunggah admin | Tersimpan di folder slug, bukan `images/placeholder/` |
| Sakelar terbit           | Menyembunyikan profil keputusan sadar super-admin     |

Label section dikembalikan ke nilai bawaan ("Katalog", "Portfolio"), bukan dikosongkan — halaman publik butuh sebutan untuk sectionnya.

**Akun yang dibuat di komputer ini** (hanya lokal, password ada pada user):

| Email                  | Peran                             |
| ---------------------- | --------------------------------- |
| `owner@jcorp.test`     | super_admin                       |
| `sweetness@jcorp.test` | business_admin → Sweetness Things |

---

## Keputusan yang Sudah Final

Sudah diputuskan user secara sadar. **Jangan ditawar ulang** kecuali user sendiri yang mengangkatnya.

| Keputusan                                     | Alasan singkat                                                                                                                                               |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| URL subfolder (`/sweetness-things`)           | Satu domain, SEO induk mengalir ke anak usaha                                                                                                                |
| Shared data contract, lima presentasi berbeda | Route, props, urutan informasi, dan primitive aksesibilitas tetap bersama; komposisi internal, atmosfer pastel, geometri, dan motion menjadi khas tiap brand |
| Dua koleksi: katalog + portfolio              | Opsional per anak usaha; yang kosong sectionnya hilang                                                                                                       |
| Admin per anak usaha (1 admin : 1 usaha)      | 1 super-admin + 5 business_admin                                                                                                                             |
| URL admin `/jcorp-panel` + login              | URL tersembunyi = penyamaran, bukan kunci                                                                                                                    |
| Pendekatan A — bertahap, Sweetness dulu       | Satu-satunya yang punya logo                                                                                                                                 |
| Halaman induk = etalase + profil singkat      | Data lengkap induk belum ada                                                                                                                                 |
| WhatsApp saja untuk kontak                    | Form bisa ditambah nanti tanpa membongkar                                                                                                                    |
| Bahasa Indonesia + bilingual khusus Ayodya    | Empat anak usaha tetap Indonesia; Ayodya menyediakan `ID / EN` karena melayani pengiriman internasional                                                      |
| Tema terang + gradasi lembut + glassmorphism  | Permintaan client                                                                                                                                            |
| Emas `#B28C27` untuk Sweetness saja           | Anak usaha lain dapat palet sendiri                                                                                                                          |
| Latar putih logo dihapus otomatis di Fase 2   | JPEG tidak bisa transparan                                                                                                                                   |

### Ditambahkan 11 Agustus 2026

| Keputusan                                                | Alasan                                                                                                                                                         |
| -------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Database bernama `jcorp_company_profile`                 | Sama dengan nama folder                                                                                                                                        |
| TypeScript untuk sisi React                              | Satu template dipakai 5 anak usaha — salah nama prop ketahuan saat menulis                                                                                     |
| `intervention/image` v4 di atas GD                       | Imagick tidak terpasang di Laragon ini; GD sudah mendukung WebP/AVIF                                                                                           |
| Scaffold dari `laravel/react-starter-kit`                | Versi Laravel/Inertia/React/Tailwind sudah dipastikan cocok oleh tim Laravel                                                                                   |
| ~~J Corp `is_published: true`, lima anak usaha `false`~~ | **Diubah di Fase 4:** semuanya diterbitkan dengan tagline contoh, supaya grid etalase bisa dinilai dengan lima kartu. Sembunyikan lagi lewat panel kalau perlu |
| Fase 1 mencakup lapisan controller                       | Tanpa alamat HTTP nyata, test kepemilikan tidak membuktikan apa-apa                                                                                            |
| Test memakai MySQL, bukan SQLite                         | `enum` dan foreign key berbeda perilaku; test SQLite tidak membuktikan apa-apa                                                                                 |
| Fitur hapus-akun-sendiri dibuang                         | Business_admin yang menghapus akunnya sendiri meninggalkan anak usahanya tanpa pengelola, dan `users` tidak pakai soft delete                                  |

---

## Lingkungan

| Komponen | Versi            |
| -------- | ---------------- |
| PHP      | 8.3.30 (Laragon) |
| MySQL    | 8.4.3 (Laragon)  |
| Node     | 22.15.0          |
| Composer | 2.8.6            |

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
Form ubah yang mengunggah berkas harus mengirim POST dengan `_method=put`. Ini tidak terlihat dari test biasa: `$this->put()` milik Laravel mem-_bypass_ PHP dan menyuntikkan berkas langsung, jadi lulus di test padahal gagal di browser. Ada test khusus (`test_an_update_sent_as_post_with_method_override_still_carries_the_file`) yang menguji jalur browser sungguhan.

**2. `UploadedFile::fake()->createWithContent()` memalsukan MIME type.**
Helper itu menyetel `image/jpeg` alih-alih membiarkannya dideteksi dari isi berkas, jadi berkas sampah _lolos_ validasi di test padahal ditolak di kenyataan — kebalikan dari yang mau dibuktikan. Test unggahan memakai berkas sungguhan di disk lewat helper `realFile()`.

**3. `respondUsing` hanya menyimpan SATU callback.**
Inertia `handleExceptionsUsing()` memakai hook yang sama dengan header `noindex`. Kalau halaman error didaftarkan terpisah, header noindex hilang diam-diam. Keduanya digabung dalam satu callback di `bootstrap/app.php`.

---

## Menggantung — Perlu Jawaban User

**Ayodya Logistic bilingual:** ✅ Diputuskan 24 Agustus 2026. Bahasa Indonesia tetap sumber utama dan versi Inggris tersedia lewat switch `ID / EN` serta `?lang=en`.

**Materi konten** — ini yang menentukan kapan website bisa online, bukan kecepatan menulis kode.

| Entitas          | Logo                      | Foto | Teks |
| ---------------- | ------------------------- | ---- | ---- |
| J Corp           | ❌                        | ❌   | ❌   |
| Sweetness Things | ⚠️ JPEG, perlu transparan | ❌   | ❌   |
| Nail's by Me     | ❌                        | ❌   | ❌   |
| ngelash.id       | ❌                        | ❌   | ❌   |
| Ayodya Logistic  | ❌                        | ❌   | ❌   |
| Lumintu Property | ❌                        | ❌   | ❌   |

Daftar rinci per anak usaha ada di **bagian 15 spec**.

---

## Aturan Kerja

Sesuai `CLAUDE.md` (global, berlaku otomatis):

| Tahap                            | Pemilik                                      |
| -------------------------------- | -------------------------------------------- |
| Spec                             | `superpowers:brainstorming` — selesai        |
| Design system & mockup           | `professional-designer`                      |
| Rencana implementasi             | `superpowers:writing-plans`                  |
| Kode                             | `professional-programmer`                    |
| Verifikasi sebelum klaim selesai | `superpowers:verification-before-completion` |
| Bug                              | `superpowers:systematic-debugging`           |

**Jangan commit atau push tanpa diminta eksplisit.** Repo sengaja dibiarkan tanpa commit.

**TDD bukan Iron Law di project ini.** Test wajib untuk logika nyata — terutama pengecekan kepemilikan antar admin. Tidak wajib untuk markup, styling, konten statis, dan konfigurasi.

---

## Keterbatasan Sesi

**Membaca gambar lewat path file GAGAL** — dicoba di Fase 2 dengan `Logo-Sweetness.jpeg`, hasilnya kosong. Catatan asli dari Fase 0 ternyata benar; koreksi yang sempat ditulis di Fase 1 keliru dan sudah dicabut.

Akibatnya, analisis logo dikerjakan lewat **pembacaan piksel dengan GD** (warna dominan, HSL, kualitas tepi) alih-alih melihat gambarnya. Itu cukup untuk keputusan warna dan pemrosesan latar, tapi **penilaian akhir apakah logonya terlihat bagus tetap perlu mata user** — itu sebabnya halaman pemeriksaan logo dibuat (sudah dihapus sebelum deploy).

---

## Riwayat

**24 Agustus 2026 — Logo Kontak Sweetness dan J-Land diperbesar**

- Logo Kontak kedua child page naik dari `7.5rem / 9rem` menjadi `11rem / 16rem` pada mobile/desktop agar menjadi jangkar visual yang jelas di bawah CTA
- Sweetness tetap memakai vessel medali bundar. J-Land tidak lagi memakai border, background, atau padding sehingga wordmark tampil langsung di atas bidang kontak
- Sumber logo, kata, data kontak, dan urutan section tidak berubah
- QA browser desktop mengukur kedua logo `256×256px`; J-Land memiliki `border: 0`, background transparan, dan padding `0`. Mobile `390px` mempertahankan logo sekitar `173–176px` tanpa horizontal overflow
- Verifikasi akhir lulus: TypeScript, ESLint, Prettier, dan production build (`2297` modul)

**24 Agustus 2026 — Headline Kontak induk dikunci satu baris pada desktop**

- `Hubungi Kami` kini memakai susunan flex horizontal pada desktop, bukan bergantung pada `display: inline`, sehingga aturan block dasar pada kedua kata tidak dapat memecahnya menjadi dua baris
- Pada breakpoint mobile `820px`, susunan kembali menjadi kolom seperti sebelumnya agar ukuran headline tetap aman tanpa overflow
- Kata, warna, isi kontak, serta urutan section tidak berubah
- QA browser mengonfirmasi kedua kata memiliki koordinat atas yang sama pada desktop `1440px`; mobile `390px` tetap bertumpuk dan tidak memiliki horizontal overflow. TypeScript, ESLint, Prettier, serta production build (`2297` modul) lulus

**24 Agustus 2026 — Grid ganjil dan ruang kosong section diseimbangkan**

- Panel Tentang ngelash diperlebar dari sekitar `502px` menjadi `625px` pada desktop `1280px`; tinggi panel turun dari sekitar `1094px` menjadi `809px` tanpa mengubah empat paragraf client
- Misi ke-7 ngelash menutup dua kolom desktop. `Jual Rumah` pada Layanan Unggulan J-Land menjadi strip penuh tiga zona—nomor, judul, deskripsi—agar mengisi baris tanpa menghasilkan kartu tinggi yang kosong
- Logo asli Sweetness Things dan J-Land Property kini muncul setelah tombol chat pada kolom kiri Kontak; data kontak di kanan tidak berubah
- Headline `Hubungi Kami` induk menjadi satu baris pada desktop dan tetap dua baris pada mobile. Seluruh aturan kembali satu kolom pada `390px` dan tidak menghasilkan horizontal overflow
- Tidak ada kata, data, harga, kontak, atau urutan section yang ditambah, dikurangi, maupun dipindahkan
- Verifikasi akhir lulus: TypeScript, ESLint, production build (`2297` modul), dan seluruh test Laravel (`320 test / 1300 assertion`)

**24 Agustus 2026 — Pergantian bahasa Ayodya dan proporsi Tentang J-Land diperbaiki**

- Kartu Profil Layanan Ayodya kini mempertahankan identity DOM saat judul berubah dari Indonesia ke Inggris atau sebaliknya. Data Inggris sebelumnya sudah ada, tetapi key berbasis judul me-remount kartu setelah `IntersectionObserver` awal terpasang sehingga kartu baru tertahan pada kondisi reveal transparan
- Grid Tentang J-Land desktop diubah dari pembagian `1.2fr / 0.8fr` menjadi `0.9fr / 1.1fr`, panel cerita diberi minimum `36rem`, dan gap dipadatkan menjadi `4rem`. Kartu lebih lebar, wrapping lebih wajar, dan tinggi section berkurang tanpa mengubah isi
- Seluruh kata, data, serta urutan section Ayodya dan J-Land tetap sama
- QA browser Ayodya mengonfirmasi ID → EN tetap menampilkan ketiga kartu pada desktop dan mobile, `?lang=en` tersimpan, serta overflow horizontal `0px`. Pada desktop `1280×720`, panel Tentang J-Land melebar dari `446px` menjadi `625px`, tinggi kartu turun dari `740px` menjadi `526px`, dan tinggi section turun dari `1034px` menjadi `819px`; mobile tetap satu kolom tanpa overflow. TypeScript, ESLint, production build (`2297` modul), dan seluruh test Laravel lulus (`320 test / 1300 assertion`)

**24 Agustus 2026 — Footer CTA kartu katalog disejajarkan**

- Area harga/teks pengganti harga dan tombol `Tanya` kini menjadi satu footer yang menempel di dasar kartu, sehingga panjang deskripsi tidak lagi membuat CTA dalam satu baris grid naik-turun
- Perubahan diterapkan pada family shell Sweetness, ngelash, Ayodya, dan J-Land serta komponen katalog khusus Nail's by Me; daftar harga tanpa gambar tidak diubah
- Seluruh kata, harga, keterangan, urutan item, serta tujuan WhatsApp tetap sama
- QA Sweetness pada desktop `1440×1000` mengukur kedua footer pada koordinat identik (`top 817px`, `bottom 929px`); katalog Nail's diperiksa pada desktop dan Sweetness pada mobile `390×844`. TypeScript, ESLint, production build (`2297` modul), serta seluruh test Laravel lulus (`320 test / 1300 assertion`)

**24 Agustus 2026 — Navbar mobile induk dan panel Sweetness dirapikan**

- Grid navbar induk berubah menjadi dua kolom hanya di bawah breakpoint desktop, sehingga identitas tetap di kiri dan tombol hamburger selalu berada di ujung kanan shell
- Menu mobile seluruh anak usaha kini mengunci scroll, dapat ditutup dengan `Escape` atau mengetuk area luar, dan memakai lapisan solid agar isi section tidak terbaca menembus panel
- Kapsul Sweetness tetap menjadi karakter navbar desktop, tetapi saat menu mobile terbuka radiusnya berubah menjadi panel `1.25rem`; bentuk oval besar yang membungkus seluruh daftar menu sudah dihapus
- Isi dan urutan tautan tidak berubah. QA browser mencakup induk dan Ayodya pada `320×844`, Sweetness pada `320×844` dan `390×844` termasuk saat dibuka dari section `Menu Kami`, serta Sweetness/induk pada desktop `1440×900`
- TypeScript, ESLint, production build (`2297` modul), dan seluruh test Laravel lulus (`320 test / 1300 assertion`)

**24 Agustus 2026 — Ukuran kartu katalog child page diseragamkan**

- Perlakuan editorial `7/5` dan kartu penuh pada katalog Sweetness dihapus karena membuat kartu produk jauh lebih besar daripada Nail's by Me
- Seluruh katalog bergambar pada child page sekarang mengikuti batas compact yang sama: tiga kolom desktop, dua kolom tablet, dan satu kolom mobile. Jumlah item yang sedikit tidak lagi membuat kartu melebar
- Warna pastel, radius, komposisi isi, hover, dan identitas visual tiap brand tetap berbeda; urutan section serta seluruh kata/data client tidak diubah
- QA desktop mengukur lebar kartu Sweetness `382px`, Nail's `388px`, Ayodya `388px`, dan J-Land `378px`; ngelash memakai daftar harga tanpa kartu bergambar. Mobile `390×844` mengukur kartu `339–347px` tanpa horizontal overflow. TypeScript, ESLint, production build (`2297` modul), dan `PublicProfileTest` lulus (`34 test / 284 assertion`)

**24 Agustus 2026 — Content dan section-order lock dipulihkan**

- Empat child page pada `BrandBusinessPage` kembali memakai urutan informasi sebelum revisi Five Editorial Worlds: Tentang → Visi & Misi → Layanan Unggulan → Keunggulan → Layanan → Katalog/Harga → Galeri → Kontak; section kosong tetap dilewati
- Navbar kembali mengikuti urutan section tersebut. Sweetness tidak lagi memajukan `Menu Kami` sebelum `Visi & Misi`; Nail's by Me tidak diubah karena urutannya memakai komponen khusus yang sudah disetujui
- Tidak ada nama, label database, deskripsi, visi, misi, layanan, harga, caption, kontak, tagline, atau terjemahan client yang ditambah, dikurangi, maupun ditulis ulang
- Seluruh visual Five Editorial Worlds tetap aktif: atmosfer pastel, komposisi hero, bentuk navbar, susunan internal kartu, glassmorphism, serta motion khas brand tidak dikembalikan ke template lama
- QA localhost mengonfirmasi urutan navbar kelima child page, urutan DOM empat halaman `BrandBusinessPage`, tidak ditemukannya copy konsep internal pada halaman publik, serta overflow horizontal `0px` pada desktop dan mobile `390×844`. TypeScript, ESLint, production build (`2297` modul), dan `PublicProfileTest` lulus (`34 test / 284 assertion`)

**24 Agustus 2026 — Five Editorial Worlds, Luminous Pastel Edition**

- Revisi diterapkan hanya pada lima child page; homepage induk yang sudah disetujui, panel admin, auth, dan error tidak diubah
- Sistem visual child page memakai rasio `55% white canvas / 35% pastel atmosphere / 10% deep brand accent`. Pastel hadir sebagai bidang besar dan panel statis, sedangkan CTA, label, garis, serta fokus utama tetap memakai warna brand pekat
- Sweetness menjadi premium patisserie catalogue dengan hero bundar dan kartu katalog compact; Nail's menjadi precision beauty portfolio dengan nail-tip serta garis presisi; ngelash menjadi fashion beauty lookbook dengan lash sweep; Ayodya menjadi route/operations profile horizontal; J-Land menjadi architectural dossier dengan blueprint grid
- `BrandBusinessPage` tetap membagikan kontrak props, urutan informasi, dan primitive aksesibilitas. Komposisi hero, navbar silhouette, susunan internal kartu, material glass, serta motion berbeda berdasarkan signature; keputusan lama "template seragam" tidak lagi berlaku pada lapisan visual
- Seluruh nama, deskripsi, visi, misi, layanan, harga, galeri, caption, kontak, logo, dan terjemahan Ayodya tetap memakai data yang sudah ada. Tidak ada copy client yang ditambah, dikurangi, atau ditulis ulang
- Motion baru hanya satu kali dan berbasis transform/opacity; dekorasi pastel, route line, lash sweep, blueprint grid, serta nail-tip tetap statis. Tidak ada dependency animasi, particle system, orbit, parallax, atau loop baru
- QA visual desktop dan mobile mengonfirmasi kelima hero mempunyai siluet berbeda serta tidak menghasilkan horizontal overflow. Pemeriksaan section dalam mencakup katalog Sweetness, galeri Nail's/ngelash, layanan dan kontak Ayodya, serta modul layanan J-Land
- Verifikasi akhir lulus: TypeScript, ESLint, Prettier, pemeriksaan whitespace Git, production build (`2297` modul), Laravel Pint, PHPStan (`0` error), dan full Laravel test suite (`320 test / 1300 assertion`)

**24 Agustus 2026 — Galeri ngelash, Ayodya bilingual, kontak logistik, dan tagline kartu induk**

- Galeri ngelash dikunci pada urutan Profil Layanan → Layanan & Harga → Galeri. Presentasinya mengikuti grid seragam `1:1`, label UI `Galeri`, dan headline `Hasil Kerja` dari database
- Database lokal mengaktifkan portfolio ngelash tetapi masih memiliki `0` item. Section tetap disembunyikan sampai admin mengunggah foto; tidak ada gambar, caption, atau placeholder buatan
- Ayodya kini mempunyai switch `ID / EN` di samping WhatsApp pada navbar desktop dan di area aksi navbar mobile. Pilihan Inggris tersimpan pada URL `?lang=en` tanpa reload atau dependency baru
- Seluruh deskripsi, visi, misi, layanan, kontak, label UI, footer, dan pesan pembuka WhatsApp Ayodya memiliki versi Inggris yang setara; data Indonesia di database tidak diubah dan tetap menjadi sumber utama
- Catatan kontak Ayodya dipecah menjadi tiga baris route-style agar PIC/WhatsApp, telepon/fax, serta email/situs mudah dipindai. Semua karakter data client tetap utuh
- Kartu induk yang sebelumnya kosong kini mendapat tiga tagline Inggris yang disetujui: Sweetness Things, Nail's by Me, dan J-Land Property. Implementasinya fallback khusus homepage, sehingga tagline admin tetap menang dan hero anak usaha tidak berubah
- QA browser desktop `1280×720` mengonfirmasi switch memiliki target `44×44px`, `?lang=en` bertahan setelah reload, seluruh label/navigation/content/contact/CTA WhatsApp berubah ke Inggris, overflow horizontal `0px`, tidak ada Vite overlay, dan semua gambar non-lazy termuat
- QA homepage mengonfirmasi kelima kartu memiliki tagline serta ukuran seragam `373.3×432px`. Mobile diverifikasi dari aturan CSS mobile-first: nav utama dan CTA desktop hilang di bawah `70rem`, switch tetap tersedia, identitas boleh menyusut tanpa overflow, dan menu hamburger mempertahankan target sentuh
- Verifikasi lulus: TypeScript, ESLint, Prettier, production build (`2297` modul), serta `HomePageTest + PublicProfileTest` (`59 test / 500 assertion`)

**24 Agustus 2026 — One Family, Five Signatures diterapkan ke empat anak usaha lainnya**

- Sweetness Things, ngelash.id, PT. Ayodya Utama Logistic, dan J-Land Property kini memakai satu family shell React yang sama dengan variant visual per brand; route, controller, model, serta kontrak props tidak diduplikasi
- Sweetness memakai kurva emas hangat dan susunan menu editorial; ngelash memakai sweep emas gelap dan daftar harga terstruktur; Ayodya memakai garis rute merah serta kartu layanan tegas; J-Land memakai grid denah biru dan kartu geometris
- Seluruh nama, tagline, deskripsi, visi, misi, layanan, harga, katalog, kontak, dan logo tetap dirender dari data client. Tidak ada label sektor, slogan, deskripsi, foto stok, atau placeholder baru
- Hero mempertahankan nama dan logo sejak frame pertama; motion hanya pada navbar, label, CTA, tagline pendukung, dan motif CSS satu kali. Tidak ada animasi loop, partikel, atau elemen bergerak di luar hero
- QA browser desktop `1280px` mengonfirmasi keempat route memakai variant yang benar, label section `18px`, seluruh gambar berhasil dimuat, seluruh grid kartu memiliki lebar seragam per section, tidak ada Vite error overlay, dan overflow horizontal `0px`
- Aturan responsif diverifikasi dari baseline CSS: hero, Tentang, Visi & Misi, layanan, katalog, serta kontak menjadi satu kolom di mobile; galeri menjadi dua kolom; navbar beralih ke hamburger di bawah `70rem`. TypeScript, ESLint, Prettier, production build, dan `PublicProfileTest` lulus (`34 test / 284 assertion`)

**24 Agustus 2026 — Galeri Nail's menjadi grid seragam**

- Perlakuan khusus foto pertama dihapus: tidak ada lagi item unggulan dua kali lebih besar, radius khusus, pemusatan item tunggal, atau pembesaran otomatis ketika galeri hanya berisi dua foto
- Semua foto kini memakai ukuran dan rasio `1:1` yang sama dalam grid empat kolom desktop/tablet dan dua kolom mobile. Urutan, caption, gambar, serta tautan backend tidak diubah
- Verifikasi localhost desktop pada empat data galeri mengonfirmasi seluruh kartu selebar `286.5px`, seluruh gambar `284.9px × 284.9px`, dan overflow horizontal `0px`; Prettier, pemeriksaan whitespace Git, serta production build lulus

**24 Agustus 2026 — Hierarki label bergaris diperbesar**

- Label bergaris pada hero dan setiap section homepage induk serta Nail's diperbesar bersama-sama agar penanda section lebih mudah terbaca
- Homepage induk kini memakai ukuran responsif `16–20px`; Nail's memakai `16px` di mobile dan `18px` di desktop. Garis dekoratif diperpanjang dari `44–56px` menjadi `60px` di mobile dan `72px` di desktop
- Perubahan hanya menyentuh tipografi, gap, dan panjang garis pada dua selector bersama; kata/kalimat label tetap berasal dari data dan komponen yang sama
- Vite dev server direstart karena masih menyajikan transform CSS lama. Computed style browser desktop kemudian mengonfirmasi seluruh label induk `16px / 72px`, seluruh label Nail's `18px / 72px`, dan overflow horizontal tetap `0px`; Prettier serta production build lulus

**24 Agustus 2026 — Tagline induk diselaraskan dengan logo terbaru**

- Tagline resmi J-Corporate Group diganti dari versi bahasa Indonesia menjadi `Growing Together, Serving All`, mengikuti tulisan pada logo terbaru
- Sumber data client, dua assertion seeder, footer dokumentasi, dan seluruh mockup referensi diperbarui; tidak ada deskripsi, visi–misi, kontak, atau data unit usaha yang diubah
- Seeder mengenali tagline resmi sebelumnya sebagai nilai legacy yang aman diganti, tetapi tetap tidak menimpa tagline lain yang ditulis admin. Dua test regresi menjaga kedua perilaku tersebut
- Database lokal diperbarui secara kondisional hanya pada baris `jcorp` yang masih memiliki tagline lama. Respons homepage `200` memuat tagline baru dan tidak lagi memuat slogan lama
- Verifikasi lulus: Laravel Pint serta `ClientContentSeederTest` + `HomePageTest` (`78 test / 362 assertion`)

**24 Agustus 2026 — Landing sequence diperlambat agar setiap komponen terbaca**

- Menindaklanjuti evaluasi visual, total sequence dinaikkan dari sekitar `0.94s / 1.27s` menjadi sekitar `2.45s` pada mobile dan `2.95s` pada desktop
- Durasi tiap komponen kini `600–1100ms`, sedangkan stagger label orbit dan nail-tip diperlebar menjadi `230ms` pada mobile serta `300ms` pada desktop. Hasilnya, perpindahan fokus antar elemen dapat terlihat jelas dan tidak lagi terasa muncul serentak
- Settle navbar induk dan Nail's diperpanjang khusus menjadi `850ms` pada mobile serta `1100ms` pada desktop; delay awal tetap pendek agar navbar langsung mulai bergerak ketika halaman dibuka
- Koreografi, easing, jarak gerak, serta batas performa tetap sama: hanya pacing yang diperpanjang. Headline, logo, vessel, dan ring tetap langsung terlihat tanpa animasi; `prefers-reduced-motion` tetap menampilkan keadaan akhir
- Computed style browser desktop `1280×720` mengonfirmasi urutan `0.10s → 0.30s → 0.60s → 0.85s / 1.15s / 1.45s / 1.75s → 2.25s` pada kedua hero, dengan overflow horizontal `0px`
- Verifikasi regresi lulus: Prettier CSS, TypeScript, ESLint, production build, serta `HomePageTest` + `PublicProfileTest` (`57 test / 480 assertion`)

**24 Agustus 2026 — Retuning landing motion: Cinematic Balanced**

- Audit terhadap versi pertama menemukan durasi `240–360ms` dan stagger `25–40ms` membuat mayoritas elemen terbaca muncul hampir bersamaan. Motion tidak hanya diperlambat: easing, urutan, jarak masuk, opacity pendukung, serta koreografi Nail's semuanya direvisi
- Easing landing kini memakai `cubic-bezier(.2, .65, .25, 1)`. Mobile tetap menjadi baseline dan menyelesaikan sequence sekitar `930–940ms`; breakpoint desktop memperpanjang sequence secara terkendali sampai sekitar `1.26–1.27s`
- J-Corporate: label orbit muncul dengan stagger searah jarum jam dalam urutan data `Kuliner → Kecantikan → Logistik → Properti`, kemudian `05 Unit Usaha` menjadi penutup. Logo, headline, dan ring tidak dianimasikan
- Nail's by Me: empat nail-tip kini memakai tiga fase `gathered → open → settle` dengan overshoot halus; garis signature baru mulai setelah fan hampir terbentuk. Logo, headline, dan vessel tetap statis
- Opacity `0.45–0.55` hanya diterapkan pada elemen pendukung selama landing. Computed style browser memastikan headline dan logo kedua hero tetap `animation-name: none` serta `opacity: 1`
- QA browser production build desktop `1280×720`: easing dan durasi baru aktif pada kedua hero, keadaan akhir rapi, serta overflow horizontal tetap `0px`. Cabang mobile tetap menggunakan nilai baseline yang lebih pendek dan perubahan hanya menyentuh compositor properties (`opacity`, `translate`, `rotate`, `scale`), bukan ukuran atau alur layout
- Verifikasi regresi lulus: Prettier CSS, TypeScript, ESLint, production build, serta `HomePageTest` + `PublicProfileTest` (`57 test / 480 assertion`)

**24 Agustus 2026 — Landing motion hero: Editorial Settle + Brand Signature**

- Hero induk dan Nail's by Me kini punya animasi masuk satu kali tanpa menambahkan library atau state React: semuanya CSS-only dan hanya memakai `translate`, `rotate`, serta `scale`
- Headline dan logo utama tetap terlihat sejak frame pertama (`opacity: 1`, `animation-name: none`) agar kandidat LCP tidak menunggu animasi selesai
- J-Corporate: navbar settle, garis label memanjang, CTA naik pendek, lalu empat label bidang bergerak dari arah luar menuju posisi orbit; ring dan logo tetap diam
- Nail's by Me: navbar settle, garis label memanjang, CTA naik pendek, empat bentuk nail-tip membuka sekali, lalu garis signature tergambar; vessel dan logo tetap diam
- Aturan motion ditulis mobile-first: jarak `4–6px` dan urutan selesai maksimal sekitar `435ms`; breakpoint desktop hanya memperpanjang gerak sampai maksimal sekitar `630ms`, tanpa mengubah ukuran atau alur layout
- `prefers-reduced-motion: reduce` mematikan seluruh landing animation dan langsung memakai keadaan akhir. Tidak ada opacity nol, blur animation, parallax, rotasi berulang, atau properti layout yang dianimasikan
- QA browser production build desktop `1280×720`: kedua hero berakhir rapi dengan overflow horizontal `0px`; computed style mengonfirmasi hanya elemen pendukung yang memiliki animation name, sedangkan headline dan logo tetap `none`
- QA frame awal mengonfirmasi motion benar-benar berjalan: label orbit induk mulai dari `translate(-8px, 4px)`, sedangkan nail-tip serta garis signature bergerak menuju keadaan akhir tanpa menggeser logo
- Verifikasi kode: TypeScript, ESLint, Prettier, dan production build lulus; `HomePageTest` + `PublicProfileTest` lulus `57 test / 480 assertion`

**24 Agustus 2026 — Nail's by Me: One Family, Five Signatures**

- `/nails-by-me` mendapat presentasi khusus pertama untuk sistem **One Family, Five Signatures**: putih terang, aksen hijau brand `#2A4104`, editorial serif, dan glassmorphism selektif
- Implementasi diisolasi dengan `data-presentation="nails-signature"`, class `nails-*`/`signature-*`, dan komponen `components/public/signature/`; Sweetness Things, ngelash.id, Ayodya, serta J-Land tetap memakai halaman A+B sebelumnya
- Urutan konten mengikuti data yang tersedia: hero, Tentang, Visi & Misi, keunggulan, Profil Layanan, Galeri, Katalog, lalu Pesan & Kunjungi. Section yang datanya kosong tetap dilewati
- **Content lock dipertahankan.** Deskripsi, visi, tiga misi, lima keunggulan, tiga layanan, catatan pemesanan, nomor WhatsApp, Instagram, dan TikTok diambil langsung dari props Laravel tanpa copy pemasaran, harga, atau placeholder baru
- Hero dibuat statis; logo utama diberi `fetchpriority="high"`. Reveal hanya memakai transform/opacity di bawah fold, tanpa partikel, orbit, floating WhatsApp, atau CTA fixed di bawah layar
- Glassmorphism dibatasi pada navbar, vessel logo, story panel, kartu misi, panel harga jika kelak tersedia, dan panel kontak; kartu berulang tidak memakai backdrop blur agar tetap ringan ketika data bertambah
- Galeri sekarang berada tepat setelah Profil Layanan. Label antarmukanya `Galeri`, judul besarnya memakai `portfolio_label` database (`Hasil Kerja`), foto pertama menjadi fokus editorial, dan foto berikutnya masuk grid pendamping. Caption, thumbnail, serta tautan gambar penuh seluruhnya berasal dari backend portfolio yang sudah ada
- Database lokal terverifikasi dengan sakelar portfolio aktif tetapi `0` foto. Karena itu Galeri belum muncul di localhost sampai admin mengunggah foto; tidak ada gambar contoh atau empty card yang disisipkan
- Layout katalog, daftar harga, Galeri, dan panel kontak khusus Nail's sudah future-ready, tetapi tetap tidak menampilkan section kosong
- Navbar tiga zona desktop dan menu mobile aksesibel sudah aktif; tautan section dibangun hanya dari data yang benar-benar tersedia
- QA browser production build: desktop `1440×900` dan mobile `390×844`, overflow horizontal `0px`, hero `animation: none`, tidak ada elemen `position: fixed`, menu mobile buka/tutup dan navigasi anchor berfungsi, serta tidak ada error/warning console
- QA keadaan Galeri kosong: `#galeri` dan tautan `Galeri` sama-sama tidak dirender pada desktop maupun mobile, tidak meninggalkan ruang kosong, dan overflow horizontal tetap `0px`
- Regression QA: `/sweetness-things`, `/ngelash`, `/ayodya-logistic`, dan `/j-land-property` terverifikasi tidak memuat varian `nails-signature`
- Verifikasi kode: TypeScript, ESLint, dan production build lulus; `ClientContentSeederTest` 53 test / 164 assertion serta `PublicProfileTest` 34 test / 284 assertion lulus

**24 Agustus 2026 — Ejaan nama induk dibetulkan: J-Corporette → J-Corporate**

- Ejaan resmi dari client. Kode sudah memakai "J-Corporate"; yang tertinggal `.env`, dua dokumen, dan **isi database**
- `isSafeToReplace()` kini menerima DAFTAR nama lama per kolom, bukan satu nilai — nama induk berganti dua kali ("J Corp" → "J-Corporette Group" → "J-Corporate Group") dan keduanya harus bisa ditimpa
- Teks Tentang Kami juga memuat ejaan lama. Tidak bisa dicocokkan lewat daftar nilai — teks panjang akan membengkakkan daftarnya setiap kali ada perbaikan kata. Diselesaikan dengan **membandingkan setelah ejaannya dibetulkan**: kalau hasilnya sama persis dengan materi client, yang tersimpan memang teks lama, bukan tulisan admin
- 2 test baru; total 316 lulus

**Yang hampir terlewat:** teks itu menyebut nama induk dalam DUA bentuk — lengkap di awal ("J-Corporette Group"), singkat di kalimat penutup ("satu keluarga besar J-Corporette."). Mengganti frasa lengkapnya saja menyisakan yang kedua, dan itu baru ketahuan setelah membandingkan karakter per karakter.

**24 Agustus 2026 — Logo J-Corporate diganti**

- Client mengirim berkas baru; logo lama (21 Agustus) ditimpa
- **Nama berkas keluarannya sengaja sama persis** — `jcorp-logo-600.webp` dan seterusnya. Tidak ada satu baris kode pun yang perlu disunting: `ClientContent::JCORP_LOGO` tetap menunjuk ke jalur yang sama, favicon tetap diturunkan dengan pola yang sama
- Sumber baru jauh lebih bersih: JPEG 1064×1006 berlatar putih rata, tidak perlu penyaringan komponen seperti yang lama
- Kontras tinta **15,12:1** di permukaan kartu

**Dua hal yang perlu penanganan, keduanya akibat sumber JPEG:**

- **Berkasnya sempat 56 KB** — tiga kali lipat logo lama. Penyebabnya 1.202 warna berbeda, padahal sampel sumbernya semua abu-abu netral (rgb 15–61): itu noise kompresi, bukan gradasi yang disengaja. Warna tinta diseragamkan ke `rgb(34,34,34)`, turun ke **33 KB** tanpa perbedaan yang terlihat mata
- **17 bintik nyasar** sisa riak JPEG. Dibersihkan dengan membuang piksel pekat yang seluruh tetangganya transparan — di gambar sebersih ini, garis logo selalu punya tetangga

**23 Agustus 2026 — Homepage induk: Luminous Editorial Glass**

- Homepage induk diubah ke arah yang disetujui: putih terang, editorial asimetris, glassmorphism selektif, dan motion halus
- Pass fidelity kedua menyamakan komposisi implementasi dengan prototype: navbar tiga zona + CTA hitam, hero headline hitam–kuningan + panggung logo, Tentang dua kolom + metrik, Visi & Misi quote + kartu, Hubungi Kami editorial, dan footer terpusat
- Label orbit `Kuliner`, `Kecantikan`, `Logistik`, dan `Properti` dikembalikan sesuai revisi user; label datang dari backend dan warna mengambil aksen unit. Dua unit kecantikan digabung menjadi satu pill
- Hero dibuat statis untuk mengurangi pekerjaan render dan menghindari penundaan LCP: reveal above-the-fold, aura/glow bergerak, morph kartu logo, ring berputar, dan pill mengambang dihapus
- Lima satelit warna serta dua titik dekoratif pada ring dihapus; lingkaran kecil kini hanya tampil sebagai penanda warna di dalam label orbit `Kuliner`, `Kecantikan`, `Logistik`, dan `Properti`
- Logo induk dimuat eager dengan `fetchpriority="high"`; aset WebP yang dipakai berukuran 17.416 byte
- Logo induk juga dipreload dari HTML Blade awal agar permintaan gambar tidak menunggu React hydration
- Kalimat deskripsi induk di hero dihapus agar tidak menduplikasi isi Tentang
- Section Unit Usaha sengaja mempertahankan grid kartu compact versi localhost; tidak diubah menjadi daftar horizontal prototype
- Baris indeks bernomor `01/02/03` beserta label kanan dihapus; section sekarang langsung dimulai dari label garis yang diperbesar
- Label garis seluruh section diperbesar lagi ke rentang `14–16px` dan garisnya diperpanjang agar hierarki section lebih jelas
- Label garis `Kontak` dipindahkan secara struktural ke luar serta di atas card glassmorphism
- Headline Tentang diperkecil dan dikunci menjadi dua baris: `J-Corporate` lalu `Group`
- Container editorial dan container Unit Usaha dipisahkan agar section mockup bisa lebar tanpa membuat kartu unit melebar dan boros ruang
- Urutan section sekarang persis: Beranda, Tentang, Visi & Misi, Unit Usaha, Hubungi Kami
- Visi hanya tampil di section Visi & Misi; section Tentang hanya berisi deskripsi client
- Seluruh copy/data buatan pada versi sebelumnya dihapus: label lokasi, ringkasan pemasaran, statistik bidang industri, dan kalimat tambahan unit usaha
- Content lock diterapkan: komponen hanya merender props Laravel apa adanya dan menghilangkan elemen yang datanya kosong
- Implementasi di-scope dengan class `home-*` dan komponen `components/public/home/`; lima halaman anak usaha serta panel tidak ikut berubah
- Navbar mobile, fallback tanpa backdrop blur, `prefers-reduced-transparency`, dan `prefers-reduced-motion` ikut ditangani
- QA browser final: desktop `1920×867` dan mobile `390×844`; semua baris indeks section sudah hilang, label garis langsung tampil pada Tentang/Visi/Misi/Unit Usaha/Kontak, headline Tentang tetap tepat dua baris, 5 kartu unit tetap tampil, dan overflow horizontal `0px`
- QA label section dan Kontak: seluruh label terbaca `16px` di desktop dan `14px` di mobile dengan garis `56px`; label Kontak terverifikasi bukan anak panel kaca, memiliki jarak `40px`/`28px` sebelum card, dan overflow horizontal tetap `0px`
- QA hero statis: desktop `1920×867` dan mobile `390×844`; `0` satelit, `0` titik dekoratif ring, `0` reveal di section top, empat penanda warna label tetap tampil, semua animasi hero `none`, dan overflow horizontal `0px`
- Lighthouse lokal pada build production desktop tanpa throttling: skor performa `88`, FCP `1,565 detik`, LCP `1,565 detik`, TBT `0 ms`, dan CLS `0`. Angka lokal ini bukan jaminan hasil hosting/field data, tetapi kandidat LCP serta hint aset sudah terverifikasi
- Preload mengubah request logo menjadi `High` dan lolos audit lazy-load, prioritas LCP, serta discovery; pada dua run lokal sejenis LCP bergerak dari `1,635` ke `1,565 detik`
- Verifikasi kode: 314 test / 1.275 assertion lulus; Pint, PHPStan (0 error), Prettier, TypeScript, ESLint, dan production build lulus

**23 Agustus 2026 — Ikon tab per anak usaha**

- Tiap halaman memakai lencana usahanya sendiri di tab browser; panel admin tetap ikon bawaan
- Jalur lencana **diturunkan dari jalur logo** (`ngelash-logo-600.webp` → `ngelash-badge-48.png`), bukan disimpan di kolom baru — kolom kedua berarti dua daftar yang harus dijaga tetap seiring
- Keberadaan berkasnya diperiksa: nama yang cocok pola tapi berkasnya hilang membuat browser menampilkan ikon kosong, lebih buruk daripada memakai ikon induk
- 9 test baru; total 312 lulus

**Cacat yang ditemukan saat memeriksa HTML:** percobaan pertama memasangnya lewat `<Head>` Inertia menghasilkan **dua** `<link rel="icon">` — satu dari Blade, satu dari React. Inertia memang menggantinya setelah JavaScript jalan, tapi browser sudah membaca yang pertama dan ikonnya sempat berkedip berganti. Dipindah ke Blade, yang membaca `favicon_url` langsung dari props dan menulis satu tag benar sejak respons pertama. Ada test yang menghitung jumlah tag ikon per halaman.

Sekalian dibereskan: warna latar di `app.blade.php` masih krem `#fdfbf6` — sisa sebelum pindah ke putih, yang membuat halaman berkedip krem sepersekian detik sebelum CSS termuat.

**23 Agustus 2026 — Kartu anak usaha membuka tab baru**

- Kelima kartu memakai `target="_blank"` + `rel="noopener"`; footer kembali ke induk tetap di tab yang sama
- Routing **tidak berubah** — tetap satu domain berslash (`namaweb.com/sweetness-things`), sesuai rencana produksi
- `<Link>` Inertia **mengabaikan** `target="_blank"` karena mengambil alih klik untuk perpindahan tanpa muat ulang; diganti `<a>` biasa
- `rel="noopener"` wajib: tanpa itu halaman yang dibuka bisa mengakses `window.opener` dan mengarahkan tab induk ke alamat lain
- Pembaca layar mendengar "Lihat profil Sweetness Things, membuka tab baru" — tanpa itu, orang yang tidak melihat layar kehilangan konteks saat fokusnya berpindah tab

**23 Agustus 2026 — Navbar halaman induk**

- Home / About Us / Support Company / Contact, memakai komponen `SiteNav` yang sama dengan halaman anak usaha — bukan navbar kedua yang harus dirawat terpisah
- Bonusnya: navbar induk otomatis dapat panel geser di HP yang sebelumnya tidak ada
- Tanpa tombol WhatsApp; induk belum punya nomor, dan parameter itu memang opsional

**Masalah lama yang ikut ketahuan:** tautan jangkar tertutup navbar yang menempel. Halaman anak usaha punya masalah yang sama **sejak awal** — belum pernah ketahuan karena tidak ada yang mengetesnya. Diperbaiki di CSS untuk semua halaman: `scroll-margin-top: 5.5rem` pada `section[id]`, plus `scroll-behavior: smooth` yang dimatikan di bawah `prefers-reduced-motion`.

**23 Agustus 2026 — Logo di kartu anak usaha + hapus foto katalog**

- Logo dikembalikan ke kartu bidang usaha. Rasionya berjauhan (Ayodya 5,50:1, J-Corporate 0,99:1), ditampung dengan **area bertinggi tetap 68px** + `object-contain` — baris teks di bawahnya selalu mulai di ketinggian sama
- Tombol "Lihat profil" bergaris bawah, bukan berlatar warna: lima tombol pekat berjajar saling berebut perhatian
- **Hanya tombolnya yang bisa diklik**, bukan seluruh kartu — kartu yang seluruhnya jadi tautan membuat teksnya tidak bisa diseleksi, dan pembaca layar membacakan seluruh isi kartu sebagai satu tautan panjang
- Panah `↗` diganti SVG: karakter itu punya penyajian emoji bawaan dan tampil berwarna biru-ungu di Windows
- **Tombol hapus foto** di form katalog — sebelumnya satu-satunya cara adalah menghapus item lalu membuatnya lagi dari nol. Server membedakan tiga keadaan: ada berkas (ganti), `remove_image=1` (kosongkan), tidak keduanya (**pertahankan**). Yang ketiga paling sering dan sebelumnya tidak pernah diuji
- Portfolio sengaja tidak dapat tombol ini: foto portfolio wajib, menghapusnya sama dengan menghapus itemnya

**23 Agustus 2026 — Panel admin & halaman auth ikut arah A+B**

- Latar putih, permukaan padat, garis rambut — sama dengan halaman publik. Krem, aurora, dan glassmorphism **dipensiunkan sepenuhnya** dari aplikasi
- **Tipografi panel sengaja TIDAK ikut.** Tetap Fraunces + Jost: Instrument Serif cuma punya berat 400, sedangkan tabel panel butuh tebal untuk membedakan judul kolom dari isinya; Inter Tight juga lebih rapat, melelahkan untuk tabel yang dibaca berjam-jam
- Tata letak **tidak disentuh sama sekali** — tidak ada yang perlu dibiasakan ulang
- Hook `use-legacy-surface` dihapus; diganti class `.font-panel` yang cuma mengurus huruf
- Gradasi hero + motif bintang dibuang dari halaman auth dan halaman error
- 298 test lulus (naik dari 296)

**Sebagian besar selesai lewat token, bukan menyunting berkas.** Komponen shadcn/ui memakai `--card`, `--border`, `--radius` dan seterusnya — jadi mengubah 12 token menyelesaikan puluhan komponen sekaligus. Kelas `.glass-*` juga: definisinya diubah di CSS, 13 pemakaiannya di 7 berkas tidak disentuh.

**Nol test yang perlu disunting** — 296 test yang ada semuanya menguji perilaku, bukan tampilan, dan semuanya langsung lulus. Itu justru buktinya perubahan ini murni permukaan.

Ditambahkan `TemaPanelSmokeTest`: memastikan keenam halaman panel dan dua halaman auth masih terbuka. Test panel yang ada menguji siapa boleh mengubah apa — tidak satu pun akan gagal kalau sebuah halaman rusak karena kesalahan tampilan.

**Utang yang diakui:** kelas bernama `glass-*` yang tidak lagi berkaca. Dicatat di CSS dan DESIGN_SYSTEM §0; bisa diganti jadi `surface-*` kapan saja dalam satu pass tersendiri.

**23 Agustus 2026 — Halaman publik pindah ke arah visual A+B**

- Latar **putih bersih**; krem, aurora, dan glassmorphism dipensiunkan dari halaman publik
- Tipografi baru: **Instrument Serif** (display) + **Inter Tight** (antarmuka), menggantikan Fraunces + Jost
- Kartu **garis rambut** menggantikan kartu kaca — tanpa bayangan, sudut siku, dirapatkan jadi satu kisi
- **Penomoran bab** `01 / 02 / 03` di tiap section, dihitung di halaman supaya tidak melompat saat ada section yang datanya kosong
- Animasi masuk saat digulir: naik 18px + memudar, jeda berurutan 60ms, satu `IntersectionObserver` untuk seluruh halaman
- **`prefers-reduced-motion` ditangani** — sebelumnya tidak ada sama sekali di CSS produksi
- 296 test lulus (naik dari 285), 11 di antaranya baru untuk warna aksen

**Masalah lama yang akhirnya diselesaikan:** palet emas dirancang untuk Sweetness Things, sebuah butik dessert — tapi dipakai juga oleh **PT. Ayodya Utama Logistic** (partner Maersk, NYK Line) dan **J-Land Property**. DESIGN_SYSTEM §8.3 sudah menuliskan aturannya sejak Fase 2 (_"Palet Sweetness hanya untuk Sweetness"_), dan baru sekarang dijalankan.

Sekarang tiap unit punya aksennya sendiri, diambil dari logonya lalu **ditua kan sampai lolos AA sebagai teks**: Sweetness emas, ngelash emas tua, Nail's hijau, Ayodya merah, J-Land biru. Induk sengaja paling tenang — putih dan kuningan saja.

**Keamanan:** `accent_color` masuk ke atribut `style`, jadi dijaga **dua lapis** — panjang kolom dibatasi 7 karakter di database, dan bentuknya diperiksa regex `#RRGGBB` di `Business::safeAccentColor()`. Yang salah bentuk diganti kuningan induk, bukan "diperbaiki".

**Panel admin belum ikut pindah** (25 berkas) — keputusan sadar: hanya admin yang melihatnya, dan kebutuhannya berbeda (kepadatan tinggi, form panjang). Sementara ini ia memakai kelas `.surface-legacy` di `<body>` supaya tetap mendapat krem + aurora; tanpa itu panel kacanya melayang di atas putih polos.

**Panel hampir diam-diam berganti huruf.** Token `--font-display` dan `--font-sans` kini menunjuk ke Instrument Serif + Inter Tight, dan `<body>` memakai class `font-sans` — jadi seluruh panel ikut berganti tanpa satu pun error. Instrument Serif cuma punya berat 400, jadi enam `font-semibold` di sana akan dipalsukan browser. Ditutup dengan token `--font-legacy-*` dan `.surface-legacy` yang sekalian mengembalikan hurufnya.

**Referensi kartu anak usaha** (screenshot StatsMe dari user) diterapkan dengan tiga penyesuaian: hanya satu tombol (bukan tiga — "More" dan "Download" tidak punya tujuan), tombolnya bergaris bawah bukan berlatar warna (lima tombol pekat berjajar saling berebut perhatian), dan **hanya tombolnya yang bisa diklik** — kartu yang seluruhnya jadi tautan membuat teksnya tidak bisa diseleksi dan pembaca layar membacakan seluruh isi kartu sebagai satu tautan panjang.

Logo kelima unit rasionya berjauhan (Ayodya 5,50:1, J-Corporate 0,99:1), jadi ditampung dalam **area bertinggi tetap 68px** dengan `object-contain` — baris teks di bawahnya selalu mulai di ketinggian yang sama. Panah `↗` diganti SVG: karakter itu punya penyajian emoji bawaan dan tampil berwarna di Windows.

**22 Agustus 2026 — PT. Ayodya Utama Logistic masuk — SELURUH ENAM ENTITAS KINI PUNYA MATERI ASLI**

- Profil lengkap: Tentang (3 paragraf), visi, 3 misi, 4 mode transportasi, 3 Profil Layanan, tagline "International Freight Forwarding & Global Logistic"
- **Logo dipisahkan dari latarnya.** Sumbernya kop surat 746×171: lambang bulat merah berlatar putih, lalu tulisan "PT. AYODYA UTAMA LOGISTIC" — juga merah — di atas latar biru muda bergradasi. Yang dibuang latar birunya; logonya utuh 734×133
- Pemisahan memakai **keunggulan merah atas kanal lain** (`R - max(G,B)`), bukan luminansi — satu ukuran itu sekaligus membedakan tulisan dari latar putih dan latar biru. Hasilnya nol piksel biru tersisa
- Lencana navigasi & favicon memakai **lambang bulatnya saja**: logo lengkap berasio 5,5:1, dimampatkan ke kotak 96×96 tulisannya jadi pita dua piksel yang tidak terbaca
- Kontras merah `rgb(227,43,43)`: **3,95:1** — lolos AA untuk grafis besar, tidak untuk teks. Cukup karena dipakai sebagai gambar

**Logo sempat terpotong dan ketahuan dari layar, bukan dari test.** Percobaan pertama memotong di x=138 karena cacah piksel biru melonjak di situ — saya membacanya sebagai batas logo, padahal itu latar di belakang tulisan yang masih berlanjut sampai x≈710. Hasilnya hanya lambang bulat tanpa nama perusahaan. Semua pemeriksaan lolos; yang menangkapnya mata pemilik project saat membuka halamannya.

- Telepon kantor, fax, dua email, dan alamat web tidak punya kolom sendiri di skema — digabung ke catatan kontak supaya tidak ada informasi client yang hilang
- **Dua ejaan client dipertahankan apa adanya** atas keputusan pemilik project: "AYODYA" untuk nama perusahaan, "ayudyalogistic" untuk web dan email. Membetulkan ejaan alamat justru merusak — tautannya jadi mengarah ke domain yang tidak ada
- Tiga test penjaga baru: setiap entitas punya materi asli, setiap logo yang dikirim terpasang, tidak ada satu pun item contoh tersisa
- 285 test lulus (naik dari 277)

**Konsekuensi yang perlu diketahui:** jalur pembersih teks di `jcorp:clear-samples` kini **tidak punya sasaran** — keenam entitas ada di ClientContent, dan perintah itu sengaja melewati semuanya. Satu-satunya slug tersisa (`lumintu-property`) sudah tidak ada di database. Empat test yang memakai Ayodya sebagai contoh dipindah ke slug itu.

**22 Agustus 2026 — Materi asli Nail's by Me masuk**

- Profil lengkap: Tentang, visi, 3 misi, 3 Profil Layanan, 5 keunggulan, logo
- Logo diproses dari berkas desain bersih. **Warna hijau tua aslinya `rgb(42,65,4)` dipertahankan** — beda dari ngelash yang terpaksa dibalik karena berlatar gelap. Kontras diukur, bukan dikira: **9,88:1** di atas titik gradasi terpucat halaman, jauh melampaui syarat AA
- Materi menomori misi 1, 3, 4 — nomor 2 memang tidak ada. Disalin apa adanya sebagai tiga poin; penomorannya dibuat ulang saat dirender
- Enam tarif karangan (Manicure Rp60.000, Gel Polish Rp90.000, dst) dan empat foto placeholder tersapu
- 277 test lulus (naik dari 271)

**Dua cacat yang ditemukan saat memeriksa hasil, bukan saat menulis kode:**

- **"Start From 30K" tidak akan pernah terlihat pengunjung.** Sempat ditaruh di `catalog_note` — tapi katalog Nail's by Me kosong, dan section yang kosong tidak dirender (spec §3), jadi catatan harganya ikut hilang bersamanya. Satu-satunya petunjuk harga dari client lenyap tanpa satu pun error. Dipindah ke `contact_note`, dan test-nya sekarang memeriksa lewat HTTP sungguhan bahwa "Rp 30.000" benar-benar sampai ke halaman
- Ada **baris teks kecil di bawah tulisan logo** yang jauh lebih pucat. Dengan ambang ketat, baris itu terpotong separuh dan tersisa seperti noda. Ambang PAPER dinaikkan ke 235 supaya ikut terbawa utuh

**Perlu dikonfirmasi ke client:** nomor WhatsApp Nail's by Me `0812-2553-9182` **sama persis** dengan nomor Sekar di J-Land Property. Bisa jadi satu orang mengurus dua unit, bisa jadi salah salin.

**22 Agustus 2026 — Lumintu Property jadi J-Land Property**

- Nama dan **slug** berganti: `/lumintu-property` → `/j-land-property`. Alamat lama dialihkan **301 permanen**, jadi link yang terlanjur dibagikan tetap sampai
- Slug diganti lewat migrasi, bukan hanya di `BusinessSeeder` — kalau hanya di seeder, database yang sudah ada tidak terpengaruh dan halamannya tetap di alamat lama
- Profil lengkap: Tentang, visi, 5 misi, 3 Layanan Unggulan, 4 keunggulan, 3 Layanan Kami
- **Dua daftar layanan dipisah jadi dua kolom**: `featured_services` (APA yang dijual — Sewa Kost, Sewa Rumah, Jual Rumah) dan `services` (BAGAIMANA prosesnya — survei → sewa/beli → akad). Bentuknya sama tapi menjawab pertanyaan berbeda, dan client memberi keduanya
- Kolom `services_label` — J-Land menyebutnya "Layanan Kami", bukan "Cara Pemesanan"
- Kolom `whatsapp_label` dan `whatsapp_alt_label` — dua nomor J-Land punya nama pemiliknya, jadi baris kontaknya "WhatsApp (Sekar)" dan "WhatsApp (Bu Agung)"
- **Instagram J-Land akhirnya pasti: `magerpindah.id`.** Materi induk dulu menulis `@j-landproperty` yang saya tolak karena tanda hubung tidak diterima Instagram — ternyata memang akunnya bernama lain. Daftar Instagram di halaman induk kini empat, tinggal Ayodya yang belum
- Lima "Unit Tersedia" karangan beserta tarif bulanannya dihapus; client belum mengirim daftar unit
- 271 test lulus (naik dari 262)

**Cacat yang ditemukan test:**

- `isSafeToReplace` mencocokkan teks contoh hanya terhadap `SampleContent[$slug]`. Begitu slug berganti, pencarian itu tidak menemukan apa pun — nomor `6281277778888`, Instagram `lumintuproperty`, dan alamat "Jl. Contoh Damai No. 3" **bertahan di halaman yang sudah berganti nama**, tanpa satu pun error. Sekarang dicocokkan terhadap teks contoh seluruh anak usaha
- Entri `lumintu-property` di `SampleContent` **wajib dipertahankan** walau slug-nya sudah tidak ada — itu yang dipakai mengenali teks karangannya. Sudah ditandai di berkasnya

**22 Agustus 2026 — Daftar harga ngelash + tampilan daftar harga**

- 15 layanan dalam 6 kategori (Single/Double, 2D Lash, 3D Lash, Special Lash, Remove, Retouch) beserta harganya
- **Katalog punya dua bentuk tampilan sekarang.** Kalau tidak ada satu pun item berfoto → daftar harga berkelompok; kalau ada → grid kartu seperti biasa. Ditentukan dari data, jadi begitu foto diunggah lewat panel tampilannya kembali sendiri tanpa menyentuh kode
- Alasannya: kartu katalog punya area gambar 4:3, dan 15 layanan tanpa foto berarti 15 kotak inisial memenuhi layar sementara harganya justru tenggelam. Bentuk daftar juga yang dipakai client di dokumennya
- Kolom baru `catalog_note` — "Biaya sudah termasuk Primer + Lash Bound + Free Spoolie", tampil di bawah daftar harga dan bisa disunting admin
- 262 test lulus (naik dari 257)

**Cacat yang hampir lolos:**

- `updateOrCreate` mencocokkan item dari NAMA saja. Daftar ngelash memuat "Natural", "Medium", dan "Volume" di 2D LASH maupun 3D LASH dengan harga berbeda — jadi tarif 3D menimpa tarif 2D, dan dari 15 layanan hanya 12 yang tersimpan. Tidak ada yang gagal; tiga harga diam-diam salah. Kuncinya sekarang nama **dan** kategori

**22 Agustus 2026 — Materi asli ngelash.id masuk**

- Profil lengkap: 4 paragraf Tentang, visi, 7 misi, 7 keunggulan, tagline "Enhance Your Beauty, Elevate Your Confidence."
- Logo diproses — **warnanya dibalik**: sumbernya putih-emas di atas latar gelap, tidak terbaca di atas krem halaman. Aksen emasnya dipertahankan; bentuk hurufnya tidak berubah sedikit pun
- Grid keunggulan kini menyesuaikan jumlah: habis dibagi 4 → 4 kolom, selain itu 3 kolom. Tujuh butir jadi 3+3+1, bukan 4+3 yang menggantung
- **Enam tarif karangan dihapus** (Classic Rp150rb, Volume Rp220rb, dst). Client belum mengirim daftar harga, sementara keunggulan ke-7 justru menjanjikan "Harga Transparan" — tarif karangan di bawah janji itu merusak kepercayaan begitu pelanggan menanyakannya
- **Tiga foto portfolio contoh dihapus** — gambar abstrak yang tampil sebagai "Hasil Kerja"
- TikTok karangan dihapus; Instagram dipertahankan karena disebut di materi induk
- Sakelar `has_portfolio` pindah tanggung jawab ke `ClientContent::withPortfolio()` — `SampleContentSeeder` kini melewati anak usaha yang materinya sudah masuk, jadi tidak lagi bisa mengurusnya
- 257 test lulus (naik dari 251)

**Cacat yang ditemukan test, bukan saat ditinjau:**

- `replaceCatalog()` berhenti lebih awal saat daftar produk aslinya kosong (`if ($items === []) return;`) — akibatnya enam tarif karangan ngelash TIDAK tersapu. Justru kasus inilah yang paling berbahaya: profil sudah asli, harganya masih palsu
- Sakelar portfolio ngelash mati setelah seeder jalan, karena tidak ada seeder yang lagi mengurusnya

**21 Agustus 2026 — Hubungi Kami + footer induk**

- Section "Hubungi Kami" di halaman induk: kalimat pembuka soal peluang kerja sama, lalu daftar Instagram unit usaha sebagai kartu yang bisa diklik
- Judul section kontak kini bisa disesuaikan — "Pesan & Kunjungi" untuk anak usaha, "Hubungi Kami" untuk induk yang tidak berjualan dan tidak menerima kunjungan
- Footer induk: nama besar, slogan "Growing Together, Serving All", garis emas, lalu baris hak cipta
- Slogan juga tampil di hero — sebelumnya hero induk lengang karena tidak ada tagline
- Kolom baru `unit_socials` (json). **Daftarnya dari materi induk, bukan dikumpulkan otomatis** dari kolom `instagram` tiap anak usaha — tiga dari lima unit masih berisi username contoh, dan mengumpulkannya otomatis membuat akun karangan tampil sebagai tautan
- `order_note` diganti nama jadi `contact_note`: isinya cara memesan pada anak usaha, tapi peluang kerja sama pada induk. Diubah sekarang selagi migrasinya belum sampai ke server
- **`@j-landproperty` tidak dipasang** — Instagram tidak menerima tanda hubung pada username, jadi tautannya pasti mati; namanya juga tidak cocok dengan unit usaha mana pun yang terdaftar. Ada test yang menjaga supaya tidak masuk tanpa diperiksa
- 251 test lulus (naik dari 245)

**21 Agustus 2026 — Materi asli induk + ganti nama jadi J-Corporate Group**

- Nama resmi induk diganti di seluruh website: judul tab, hero, footer kelima anak usaha, `APP_NAME`, halaman undangan admin
- **Nama induk di footer diambil dari database**, tidak lagi ditulis di komponen — sebelumnya nama yang sama tertanam di lima halaman dan tidak ada yang mengingatkan saat berubah
- Logo diproses dari foto di atas kertas: latar dibuang, bayangan tepi foto disingkirkan lewat penyaringan komponen, warna tinta diseragamkan. Empat berkas di `public/images/brand/`, cara pembuatannya di `docs/scripts/proses-logo-jcorp.php`
- Tentang, Visi, dan Misi induk masuk lewat `ClientContentSeeder`; halaman induk kini merender Visi & Misi dengan komponen yang sama seperti anak usaha
- **Kontak induk sengaja dikosongkan.** Nomor yang diberikan client `081176265` hanya sembilan digit — nomor Indonesia paling pendek pun sebelas, jadi hampir pasti terpotong. Dipasang sebagai link wa.me, nomor keliru mengarah ke akun orang lain tanpa ada yang tahu. Email dan alamat juga belum diisi client
- Kontak karangan lama di induk (nomor `6281200000000`, alamat "Jl. Contoh Utama No. 1") dibersihkan
- 245 test lulus (naik dari 237)

**21 Agustus 2026 — Materi asli Sweetness Things masuk**

- Materi dari client (dua tangkapan layar: profil + kontak) dimasukkan lewat `ClientContentSeeder`
- **Dipisah dari `SampleContentSeeder`** — `jcorp:clear-samples` membaca `SampleContent` untuk menentukan apa yang dihapus, jadi materi asli akan ikut terhapus kalau ditaruh di sana
- Enam kolom baru di `businesses`: `vision`, `mission`, `services`, `highlights` (json), `whatsapp_alt`, `order_note`. Semua nullable, jadi anak usaha lain tidak berubah
- Tiga section baru: Visi & Misi (panel), Keunggulan (4 kartu), Cara Pemesanan (2 kartu). Ritmenya sengaja dibedakan — DESIGN_SYSTEM §5.1 dan §10 melarang satu permukaan seragam untuk semua section
- **Hanya yang ada di materi client.** Tagline dan jam buka dikosongkan karena client tidak memberikannya — bukan diisi kalimat karangan supaya halaman terlihat penuh
- Harga produk masih `null` + "hubungi kami"; daftar harga belum ada dari client
- `whatsapp_alt` dan `order_note` bisa disunting admin lewat panel; visi/misi/layanan/keunggulan **tidak fillable**, dan ada test yang membuktikan keempatnya tidak bisa disusupkan lewat request
- 237 test lulus (naik dari 197)

**Tiga cacat yang ditemukan saat mengerjakan, dan ditutup:**

- Pola `??=` warisan `SampleContentSeeder` **tidak menggantikan apa pun** — Sweetness sudah terisi teks karangan, jadi materi asli akan masuk database tanpa pernah muncul di halaman, dan nomor palsu tetap tayang. Diganti perbandingan isi per kolom: kosong → isi, sama persis teks contoh → timpa, isinya lain → biarkan (tulisan admin)
- `SampleContentSeeder` bisa **mengembalikan produk karangan** kalau dijalankan setelahnya. Sekarang melewati anak usaha yang ada di `ClientContent`
- `jcorp:clear-samples` **mengembalikan label "Menu Kami" jadi "Katalog"** — nilai itu kebetulan sama di kedua berkas, jadi terbaca sebagai teks contoh. Ditemukan test, bukan saat ditinjau

**14 Agustus 2026 — Kelola akun lewat panel**

- Super-admin bisa mengundang admin baru tanpa SSH: akun dibuat tanpa password, tautan sekali pakai dikirim lewat WhatsApp, yang diundang membuat passwordnya sendiri
- Token undangan disimpan sebagai hash, berlaku 7 hari, sekali pakai
- Nonaktifkan akun: akses dicabut **seketika**, diperiksa di setiap permintaan — bukan menunggu sesi kedaluwarsa
- Ditolak juga sejak login lewat `Fortify::authenticateUsing`, supaya akun nonaktif tidak "berhasil" masuk lalu langsung dilempar keluar
- Aturan satu-anak-usaha-satu-admin kini menghitung hanya admin **aktif**, jadi penggantian admin tidak perlu menghapus akun lama
- `jcorp:make-admin` dipertahankan untuk super-admin pertama dan jalan keluar darurat
- **Tidak** dikerjakan: hapus akun (tidak bisa dipulihkan) dan pindah anak usaha (rumit untuk kasus langka)
- 197 test lulus (naik dari 162)

**13 Agustus 2026 — Terjemahan pesan validasi**

- Ditemukan saat membuat akun admin di server: pesan tampil sebagai kunci mentah (`validation.password.mixed`, `auth.failed`)
- Penyebabnya `APP_LOCALE` dan `APP_FALLBACK_LOCALE` sama-sama `id`, sementara Laravel hanya membawa terjemahan `en` — tidak ada tempat mundur
- Lolos dari 145 test yang ada karena test memeriksa ADA tidaknya error, bukan bunyinya

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
- Mockup desktop + mobile di `public/preview/` (dihapus sebelum deploy — lihat riwayat git)

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
