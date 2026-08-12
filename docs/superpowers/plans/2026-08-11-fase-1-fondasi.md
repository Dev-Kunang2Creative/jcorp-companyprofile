# Rencana Fase 1 — Fondasi

**Tanggal:** 11 Agustus 2026
**Spec acuan:** [`docs/superpowers/specs/2026-08-10-jcorp-company-profile-design.md`](../specs/2026-08-10-jcorp-company-profile-design.md)
**Cakupan:** Laravel + Inertia + React + MySQL berdiri, empat tabel terisi data awal, login & peran jalan, pengecekan kepemilikan terbukti lewat test.

**Belum termasuk:** tampilan publik, panel admin yang enak dipakai, pemrosesan gambar saat unggah. Itu Fase 3.

---

## Keputusan yang diambil di sesi ini

| Hal | Pilihan | Alasan |
|---|---|---|
| Scaffold | `laravel/react-starter-kit` lalu dipangkas | Versi Laravel 13.17 + Inertia 3 + React 19 + Tailwind 4 sudah dipastikan cocok oleh tim Laravel |
| Bahasa frontend | TypeScript | Props Inertia diketik — penting karena satu template dipakai 5 anak usaha |
| Nama database | `jcorp_company_profile` | Sama dengan nama folder |
| Library gambar | `intervention/image` v4 + driver GD | Lihat alasan di bawah |

### Kenapa intervention/image, bukan spatie/image

Spec menyisakan pilihan ini ke Fase 1. Sudah diperiksa langsung:

- `intervention/image` 4.2.1 — butuh `php ^8.3` + `ext-mbstring`, keduanya ada. Jalan di atas GD murni.
- `spatie/image` 3.9.5 — bekerja paling baik dengan **Imagick**, dan `php -m` memastikan **Imagick tidak terpasang** di Laragon ini.
- GD di sini sudah mendukung **WebP dan AVIF** (dicek lewat `gd_info()`), jadi konversi ke format ringan yang diminta spec §5 bisa dilakukan tanpa menambah ekstensi PHP.

Dipakai lewat pembungkus resminya, `intervention/image-laravel` 4.1.1 (mendukung `illuminate/support ^13`).

---

## Prasyarat sebelum mulai

**MySQL belum jalan.** Proses `laragon` ada, tapi port 3306 menolak koneksi (`ERROR 2003 ... (10061)`). Tekan **Start All** di jendela Laragon lebih dulu. Semua langkah database di bawah bergantung pada ini.

---

## Langkah 1A — Memasang scaffold

Folder project **tidak kosong** (`.git`, `.gitignore`, `PROGRESS.md`, `Logo-Sweetness.jpeg`, `docs/`), sedangkan `composer create-project` menolak menulis ke folder berisi. Jadi dipasang ke subfolder sementara lalu isinya dipindah ke atas.

Starter kit menjalankan **Chisel** — wizard interaktif yang menghapus fitur yang tidak dipilih. Wizard ini butuh terminal sungguhan; kalau dijalankan dari tool saya, promptnya menggantung.

**Yang saya minta Anda lakukan** — satu perintah di **Laragon Terminal**, di dalam folder project:

```
composer create-project laravel/react-starter-kit _scaffold
```

Saat muncul pertanyaan *"Which authentication features would you like to enable?"*, **matikan semuanya** (tekan spasi di tiap item sampai tidak ada tanda centang, lalu Enter):

```
[ ] Email verification        ← spec: tidak ada alur verifikasi email
[ ] Registration              ← spec §6: akun dibuat lewat perintah artisan
[ ] Two-factor authentication ← berlebihan untuk 6 akun internal
[ ] Passkeys                  ← berlebihan, dan menambah tabel
[ ] Password confirmation     ← menambah friksi tanpa diminta spec
```

Setelah selesai, kabari saya — sisanya saya kerjakan.

**Kalau Anda lebih suka saya yang jalankan:** bisa, dengan `--no-scripts` supaya wizard tidak muncul, lalu saya hapus fitur-fitur itu manual. Hasil akhirnya sama dan bisa Anda periksa lewat `git diff`, tapi jalurnya bukan jalur resmi yang sudah diuji Laravel. Sebut saja kalau mau lewat jalur ini.

Lalu saya:
- Pindahkan isi `_scaffold/` ke root, hapus foldernya
- **Gabungkan** `.gitignore` — yang sekarang ditambah aturan Laravel (`/vendor`, `/public/build`, `/public/storage`, `/storage/*.key`, `/.phpunit.cache`), bukan ditimpa
- Pastikan `Logo-Sweetness.jpeg`, `PROGRESS.md`, dan `docs/` tidak tersentuh

**Verifikasi:** `php artisan --version` menyebut Laravel 13.x.

---

## Langkah 1B — Memangkas sisa bawaan

Yang tidak dihapus Chisel tapi tidak kita perlukan:

| Dihapus | Alasan |
|---|---|
| Halaman `settings/appearance` + `use-appearance.tsx` + `appearance-tabs.tsx` | Spec §7: tema terang saja, tidak ada mode gelap |
| Kelas `dark:` yang ikut terbawa di komponen | Konsekuensi dari poin di atas |
| `resources/js/pages/welcome.tsx` | Diganti halaman induk J Corp di Fase 4 |
| `resources/js/pages/dashboard.tsx` + `DashboardTest.php` | Diganti dashboard panel |
| `tests/Feature/ExampleTest.php` | Test bawaan tanpa isi |

**Dipertahankan:** login + throttle, reset password, ganti password sendiri, `app-layout`/`auth-layout`, komponen shadcn/ui, ESLint + Prettier + TypeScript.

Kalau ternyata ada file lain yang ikut menyebut fitur yang sudah dibuang, saya laporkan alih-alih diam-diam menambal.

---

## Langkah 1C — Database & konfigurasi

`.env` (tidak pernah di-commit — sudah ada di `.gitignore`) dan `.env.example` (di-commit, tanpa nilai rahasia):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jcorp_company_profile
DB_USERNAME=root
DB_PASSWORD=
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta
```

Starter kit defaultnya SQLite, jadi ini penggantian sungguhan, bukan sekadar isi kolom kosong.

`APP_KEY` dibuat lewat `php artisan key:generate` — tidak pernah ditulis tangan, tidak pernah masuk git.

Database dibuat: `jcorp_company_profile` dan `jcorp_company_profile_test`.

**Soal database test:** test memakai MySQL sungguhan, bukan SQLite di memori. Alasannya kolom `enum` untuk `role` dan perilaku foreign key berbeda antara keduanya — test yang lulus di SQLite tapi gagal di MySQL tidak membuktikan apa-apa. Diarahkan lewat `phpunit.xml`, dan `RefreshDatabase` membersihkan tiap test.

**Verifikasi:** `php artisan migrate` pada migrasi bawaan berhasil menyentuh MySQL.

---

## Langkah 1D — Empat tabel

Urutan migrasi mengikuti ketergantungan foreign key: `businesses` lebih dulu, karena tiga tabel lain menunjuk ke sana.

1. `create_businesses_table`
2. `create_catalog_items_table` — FK `business_id`
3. `create_portfolio_items_table` — FK `business_id`
4. `add_role_and_business_id_to_users_table` — mengubah tabel `users` bawaan

Kolomnya persis seperti spec §4. Catatan penerapan:

- **`price`** → `decimal(12, 2)` nullable. Cukup untuk unit property Lumintu (miliaran) sekaligus dessert box.
- **`role`** → kolom `enum('super_admin', 'business_admin')`, dipetakan ke PHP backed enum `App\Enums\UserRole`. Nilai peran jadi terkunci di dua tempat sekaligus; salah ketik `'superadmin'` gagal, bukan diam-diam lolos.
- **`business_id` di `users`** → nullable, `nullOnDelete`. Super-admin tidak punya anak usaha.
- **`business_id` di dua tabel item** → `cascadeOnDelete`.
- **Index:** `slug` unique; `(business_id, sort_order)` di kedua tabel item, karena setiap tampilan katalog dan portfolio menyaring per anak usaha lalu mengurutkan — tanpa ini jadi full table scan.
- **`softDeletes`** di `businesses`, `catalog_items`, `portfolio_items`. Tidak di `users` (spec tidak memintanya).

**Perlu penegasan Anda:** spec §4 mendaftar `is_published` di `businesses`, tapi tidak menyebut kapan tepatnya J Corp sendiri diterbitkan. Asumsi saya: **J Corp `is_published: true` sejak awal, lima anak usaha `false`** — halaman induk baru ada di Fase 4, jadi sampai saat itu tidak ada yang bisa dilihat siapa pun. Koreksi kalau bukan itu maksudnya.

### Model Eloquent

`Business`, `CatalogItem`, `PortfolioItem`, dan penyesuaian `User`.

- Relasi dua arah lengkap, dengan `$casts` untuk boolean, decimal, dan enum peran
- **`$fillable` ditulis eksplisit**, tidak pakai `$guarded = []` — supaya `is_published`, `role`, dan `business_id` tidak bisa ikut terisi lewat mass assignment dari form
- Scope `published()` dan `available()` untuk penyaringan yang berulang di halaman publik

### Seeder

`BusinessSeeder` — enam baris sesuai spec §1 dan §3, termasuk `catalog_label`/`portfolio_label` awal dan `sort_order`.

`UserSeeder` — **tidak membuat akun berpassword tetap.** Akun dibuat lewat perintah artisan di Langkah 1E supaya tidak ada password yang tertulis di dalam repo. Yang di-seed hanya di lingkungan test, lewat factory.

**Verifikasi:** `migrate:fresh --seed`, lalu enam baris dicek isinya lewat tinker.

---

## Langkah 1E — Login, peran, kepemilikan

### Alamat panel

Fortify punya opsi `prefix` di `config/fortify.php`, jadi rutenya digeser tanpa menulis ulang:

```
/jcorp-panel           → dialihkan ke /jcorp-panel/login
/jcorp-panel/login     → halaman login
/jcorp-panel/dashboard → setelah berhasil masuk
```

`'home'` di `config/fortify.php` diubah dari `/home` ke `/jcorp-panel/dashboard`.

### Satu titik rawan yang perlu ditangani sejak sekarang

Spec §3 memakai `GET /{slug}` untuk profil anak usaha. Pola itu **cocok dengan hampir semua alamat satu segmen** — termasuk `/jcorp-panel` dan `/login`. Kalau urutannya salah, alamat panel akan ditangkap oleh route profil dan mengembalikan 404.

Tiga lapis penanganan:

1. Route publik `/{slug}` didaftarkan **paling akhir** di `routes/web.php` — Laravel memakai yang cocok pertama
2. Dibatasi pola: `->where('slug', '[a-z][a-z0-9-]*')`
3. Ada daftar slug terlarang yang divalidasi saat super-admin membuat anak usaha baru, supaya tidak ada yang tidak sengaja membuat slug `jcorp-panel`

Fase 1 belum menulis route publiknya, tapi urutan file disiapkan sekarang supaya Fase 4 tidak perlu membongkar.

### Pembatasan halaman admin dari mesin pencari

Middleware `NoIndexPanel` menambahkan `X-Robots-Tag: noindex, nofollow` di seluruh respons `/jcorp-panel/*` (spec §6). Dikirim sebagai header HTTP, bukan meta tag — meta tag di aplikasi Inertia rawan tidak terbaca crawler karena ditulis setelah JavaScript jalan.

### Pembatasan percobaan login

Sudah bawaan Fortify (`limiters.login`). Yang saya lakukan hanya menyalakan dan memastikan batasnya masuk akal — bukan menulis ulang.

### Perintah pembuatan akun

```
php artisan jcorp:make-admin
```

Interaktif, menanyakan nama, email, peran, dan anak usaha (kalau `business_admin`). **Password diminta dalam mode tersembunyi**, tidak pernah lewat argumen perintah — argumen tersimpan di riwayat shell.

Validasi yang ditegakkan: `business_admin` **wajib** punya `business_id`; `super_admin` **wajib** tidak punya. Satu anak usaha hanya boleh dipegang satu admin (spec §4).

### Pengecekan kepemilikan

Inti Fase 1. Diterapkan lewat **Policy** Laravel — `CatalogItemPolicy`, `PortfolioItemPolicy`, `BusinessPolicy` — yang dipanggil di controller lewat `authorize()`.

Aturannya satu kalimat: **super-admin boleh apa saja; business_admin hanya boleh menyentuh baris yang `business_id`-nya sama dengan miliknya.**

Dua sisi yang dijaga, sesuai spec §6:

- **Sisi tulis** — setiap simpan/ubah/hapus lewat `authorize()`. Menyembunyikan tombol di tampilan tidak dianggap pengamanan.
- **Sisi baca** — business_admin hanya menerima data miliknya. Query di controller disaring lewat scope, bukan mengambil semua lalu memfilter di React. Data anak usaha lain tidak pernah sampai ke browsernya.

### Soal cakupan yang perlu Anda putuskan

Untuk membuktikan kepemilikan sungguhan lewat test, harus ada alamat HTTP nyata yang bisa ditembak. Kalau controller-nya baru ada di Fase 3, test kepemilikan di Fase 1 cuma bisa menguji fungsi terpisah — bukan bukti bahwa permintaan HTTP sungguhan ditolak.

Jadi saya usulkan Fase 1 mencakup **lapisan controller-nya saja**: route, controller, dan form request untuk katalog, portfolio, dan profil — plus halaman React **polos tanpa gaya** sekadar agar alurnya bisa dijalankan. Tampilan sungguhannya ditulis ulang di Fase 3 di atas design system.

Ini melebar sedikit dari kalimat spec *"Belum ada tampilan publik"* — tapi spec juga menuntut *"pengecekan kepemilikan beserta pengujiannya"* di Fase 1, dan keduanya tidak bisa dipenuhi sekaligus tanpa lapisan ini. Halaman polos itu memang dibuang di Fase 3; yang bertahan adalah controller, policy, dan test-nya.

Kalau Anda lebih suka Fase 1 benar-benar berhenti di model dan policy, bilang saja — konsekuensinya test kepemilikan bergeser ke Fase 3.

---

## Langkah 1F — Test

Memakai PHPUnit (bawaan starter kit), di `tests/Feature/`.

**Yang diuji** (spec §11, bagian yang relevan dengan Fase 1):

| Berkas | Membuktikan |
|---|---|
| `OwnershipTest.php` | Admin A **tidak bisa** mengubah/menghapus item milik B — termasuk saat ID di alamat diganti manual. Berlaku untuk katalog dan portfolio |
| `OwnershipTest.php` | Admin A tidak menerima data milik B dalam props Inertia |
| `PanelAccessTest.php` | Belum login → dialihkan ke login, tidak bocor 404 vs 403 |
| `PanelAccessTest.php` | business_admin ditolak di `/users` dan `/businesses` |
| `PanelAccessTest.php` | `/jcorp-panel/*` mengirim header `noindex` |
| `MakeAdminCommandTest.php` | business_admin tanpa `business_id` ditolak; super_admin dengan `business_id` ditolak |
| `UnpublishedBusinessTest.php` | Admin anak usaha yang belum terbit tetap bisa masuk panel dan mengelola kontennya (spec §4) |

Test kepemilikan ditulis dari sisi penyerang: masuk sebagai admin Sweetness, lalu kirim `PUT` ke ID milik Nail's by Me. Harus 403. Itu yang membuktikan sistem peran bekerja — bukan sekadar tombolnya hilang.

**Tidak diuji di fase ini:** tampilan, CSS, isi teks, berkas konfigurasi.

Format harga dan validasi unggahan gambar ada di daftar uji spec §11, tapi keduanya milik Fase 3 — belum ada yang bisa diuji sebelum fiturnya ditulis.

---

## Langkah 1G — Verifikasi & dokumentasi

Dijalankan sungguhan, bukan diasumsikan:

```
php artisan migrate:fresh --seed     enam baris masuk
php artisan test                     seluruh test lulus
npm run build                        Vite berhasil build
npm run types:check                  TypeScript bersih
vendor/bin/pint --test               gaya kode PHP bersih
npm run lint:check                   ESLint bersih
```

Ditambah pemeriksaan manual di browser: buka `/jcorp-panel`, masuk pakai akun yang dibuat lewat artisan, pastikan sampai di dashboard.

Plus satu pemeriksaan kecil: `intervention/image` dipakai memperkecil `Logo-Sweetness.jpeg` yang sudah ada, dan hasilnya diperiksa. Ini menutup kalimat spec §2 *"library pemrosesan gambar ditentukan dan diverifikasi di Fase 1"* dengan bukti, bukan klaim.

**`README.md`** ditulis: apa project ini, cara pasang, cara jalankan, struktur folder, dan **versi paket yang terkunci** (spec §2 memintanya). Termasuk catatan Laragon: PHP tidak ada di PATH, jalankan lewat Laragon Terminal.

**`PROGRESS.md`** diperbarui: Fase 1 selesai, nama database tidak lagi menggantung, dan catatan keterbatasan sesi diperbaiki — di sesi ini membaca gambar lewat path file **berhasil**, kebalikan dari yang tercatat.

**Tidak ada commit.** Sesuai `CLAUDE.md` dan spec §14, repo dibiarkan tanpa commit sampai Anda meminta.

---

## Yang berubah di repo

```
jcorp-company-profile/
├── app/
│   ├── Console/Commands/MakeAdminCommand.php
│   ├── Enums/UserRole.php
│   ├── Http/
│   │   ├── Controllers/Panel/{Dashboard,CatalogItem,PortfolioItem,BusinessProfile}Controller.php
│   │   ├── Middleware/NoIndexPanel.php
│   │   └── Requests/Panel/…
│   ├── Models/{Business,CatalogItem,PortfolioItem,User}.php
│   └── Policies/{Business,CatalogItem,PortfolioItem}Policy.php
├── database/
│   ├── factories/{Business,CatalogItem,PortfolioItem,User}Factory.php
│   ├── migrations/  (4 tambahan)
│   └── seeders/BusinessSeeder.php
├── resources/js/pages/panel/   (polos, ditulis ulang di Fase 3)
├── routes/{web,panel}.php
├── tests/Feature/  (4 berkas)
├── README.md                    ← baru
├── PROGRESS.md                  ← diperbarui
└── .gitignore                   ← digabung, bukan ditimpa
```

Berkas yang sudah ada — `Logo-Sweetness.jpeg`, `docs/`, spec — tidak disentuh.

---

## Ringkasan hal yang menunggu jawaban Anda

1. **Jalankan `composer create-project` sendiri di Laragon Terminal** (dengan kelima fitur auth dimatikan), atau saya yang jalankan dengan `--no-scripts` lalu pangkas manual?
2. **J Corp `is_published: true` sejak awal**, lima anak usaha `false` — benar?
3. **Lapisan controller ikut di Fase 1** supaya test kepemilikan jadi bukti nyata, atau Fase 1 berhenti di model + policy saja?

Ketiganya boleh dijawab singkat. Nomor 1 yang memblokir langkah pertama; dua lainnya bisa menyusul sambil jalan.
