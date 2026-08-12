# Rencana Fase 3 — Sweetness Things

**Tanggal:** 11 Agustus 2026
**Acuan:** [spec](../specs/2026-08-10-jcorp-company-profile-design.md) · [`DESIGN_SYSTEM.md`](../../../DESIGN_SYSTEM.md)
**Cakupan:** Halaman profil Sweetness Things yang bisa diakses publik, plus panel admin yang benar-benar bisa dipakai mengisi kontennya.

---

## Keputusan yang diambil di sesi ini

| Hal | Pilihan | Konsekuensi |
|---|---|---|
| Akses publik selama pengerjaan | Sweetness diterbitkan sejak awal | Anda bisa membuka `/sweetness-things` kapan saja untuk memantau |
| Gaya panel admin | Bersih & netral, bukan glassmorphism | Panel dan halaman publik terlihat berbeda — disengaja |
| Konten | Data contoh dulu, ditandai jelas | Perlu dibersihkan sebelum tayang sungguhan |

### Kenapa panel tidak ikut bergaya kaca

Admin membuka panel berjam-jam untuk mengisi data; etalase dilihat pengunjung selama dua menit. Efek kaca dan ruang kosong yang membuat etalase terasa mewah justru memperlambat kerja di panel — form jadi kurang terbaca, dan `backdrop-filter` berat saat menggulir tabel panjang.

Yang tetap sama: warna merek, huruf, dan bahasa. Panel memakai aksen emas `--gold-deep` di tombol utama, jadi masih terasa satu keluarga tanpa meniru gaya etalase.

---

## Yang sudah diverifikasi

Diperiksa sebelum rencana ini ditulis, bukan diasumsikan:

- **Font Fraunces + Jost benar-benar bisa di-host sendiri.** Diuji dengan build sungguhan: 6 berkas `.woff2` terunduh ke `public/build/assets/`, dan CSS-nya **tidak memuat satu pun URL eksternal**. Tidak ada permintaan ke pihak ketiga saat pengunjung membuka halaman.
- **`Rule::imageFile()` menolak SVG secara bawaan** — penting karena SVG bisa memuat skrip.
- **Inertia 3 punya dukungan halaman error bawaan** (`ExceptionResponse`), jadi 404 bergaya website bisa dibuat tanpa akal-akalan.
- **GD mendukung WebP dan AVIF**, dan pemrosesan gambar sudah terbukti bekerja di Fase 1.
- Batas unggah PHP di Laragon ini 2 GB — jadi batas sesungguhnya kita yang tentukan di validasi, bukan PHP.

---

## Langkah 3A — Fondasi visual

Menuangkan `DESIGN_SYSTEM.md` ke kode.

1. **Font** — `vite.config.ts` diganti dari Instrument Sans ke **Fraunces** (400/600/700) dan **Jost** (400/500/600).
2. **Token** — isi [`docs/design-tokens.css`](../../design-tokens.css) disalin ke `resources/css/app.css` sebagai `@theme`, dengan nama yang sama persis.
3. **Kelas `.glass-panel`** — pola kaca beserta cadangannya, ditulis sekali dan dipakai ulang. Cadangan **wajib** ditulis sebelum blok `@supports`.

**Verifikasi:** `npm run build` menghasilkan berkas font Fraunces/Jost, dan CSS tidak memuat URL eksternal.

---

## Langkah 3B — Unggah gambar

Ini yang belum ada sama sekali (spec §5). Ditulis sebagai satu kelas `ImageService` supaya tidak tersebar di banyak controller.

**Alur setiap unggahan:**

1. Validasi — `Rule::imageFile()` memeriksa **isi** berkas, bukan akhiran nama; batas **4 MB**; hanya JPEG/PNG/WebP
2. Nama berkas **dibuat ulang sistem** (UUID) — nama asli dari pengguna tidak pernah dipakai
3. Diperkecil: sisi terpanjang maksimal **1200px** untuk web, **400×400** untuk thumbnail
4. Dikonversi ke **WebP** kualitas 85
5. Disimpan di `storage/app/public/{slug}/`, diakses lewat `php artisan storage:link`

**Batas 4 MB dipilih** karena foto HP biasanya 3–5 MB. Kalau ditolak, pesannya menyebut alasan dan batasnya, dan isian form lain tetap utuh (spec §10).

**Saat gambar diganti atau item dihapus permanen**, berkas lama ikut dihapus — kalau tidak, folder penyimpanan membengkak oleh berkas yatim.

**Catatan soft delete:** item yang di-soft-delete **tidak** menghapus berkasnya, karena datanya masih bisa dipulihkan. Berkas baru dihapus kalau item benar-benar dimusnahkan dari database.

---

## Langkah 3C — Halaman publik

Route publik `GET /{slug}` didaftarkan **paling akhir** di `routes/web.php` — tempatnya sudah disiapkan di Fase 1 lengkap dengan penjelasannya.

### Struktur (spec §3)

```
[Hero]       logo, nama, tagline, tombol WA          <- selalu dirender
[Tentang]    cerita singkat                          <- hilang bila description kosong
[Katalog]    grid item, label menyesuaikan            <- hilang bila tak ada item tersedia
[Portfolio]  grid foto                                <- hilang bila tak ada foto
[Kontak]     WA, Instagram, alamat, jam buka          <- hilang bila semua kolom kosong
```

**Aturan section kosong ditegakkan di controller**, bukan di React — kalau sectionnya tidak dirender, datanya juga tidak dikirim ke browser.

### Komponen

Dipecah kecil supaya Fase 5 tinggal memakai ulang untuk empat anak usaha lain:

```
resources/js/pages/public/business.tsx      halaman
resources/js/components/public/
├── site-nav.tsx          navigasi kaca + menu geser di mobile
├── hero-section.tsx      panel kaca + motif bintang
├── about-section.tsx
├── catalog-section.tsx   grid kartu padat (bukan kaca)
├── catalog-card.tsx      + kotak inisial saat gambar gagal
├── portfolio-section.tsx
├── contact-section.tsx   panel kaca
├── whatsapp-button.tsx   pesan pembuka terisi otomatis
└── floating-wa.tsx       hanya mobile
```

### Tombol WhatsApp

Pesan pembuka terisi otomatis menyebut nama usaha dan item yang dilihat (spec §8) — misalnya *"Halo Sweetness Things, saya mau tanya soal Dessert Box Coklat"*.

Nomor diambil dari database, bukan ditanam di kode, dan komponennya terpisah — supaya menambah form tersimpan nanti berarti **menambah**, bukan mengganti.

### Halaman error (spec §10)

- **404** — slug salah atau belum terbit. Bergaya website, ada tautan ke halaman induk. Yang belum terbit tetap 404, bukan "belum tersedia", supaya keberadaannya tidak terungkap.
- **500** — bergaya sama, tanpa detail teknis. Detail masuk log server.

---

## Langkah 3D — Panel admin yang bisa dipakai sungguhan

Halaman panel yang ada sekarang polos (655 baris) — ditulis ulang.

| Halaman | Isi |
|---|---|
| Dashboard | Ringkasan jumlah, status terbit, pintasan |
| Katalog | Tabel item, form tambah/ubah, unggah gambar + pratinjau, sakelar tersedia, urutan |
| Portfolio | Grid foto, unggah, keterangan, urutan |
| Info Kontak | WA, Instagram, TikTok, alamat, jam buka, label section |
| Kelola Akun | Daftar akun (super-admin) |
| Kelola Anak Usaha | Sakelar terbit (super-admin) |

**Pengalaman pemakaian (spec §6):**

- Form yang gagal validasi mengembalikan isian yang sudah diketik
- Konfirmasi hapus menyebut **nama item**, bukan sekadar "yakin?" — memakai dialog, bukan `confirm()` bawaan browser
- Unggah gambar menampilkan **pratinjau sebelum disimpan**
- Urutan diatur dengan kolom angka, bukan seret-lepas — lebih jelas dan tidak bermasalah di layar HP

**Yang tidak berubah:** controller, policy, dan pengecekan kepemilikan dari Fase 1. Semuanya sudah terbukti lewat 76 test — yang ditulis ulang hanya lapisan tampilannya.

---

## Langkah 3E — Data contoh

`SweetnessSampleSeeder` mengisi beberapa item katalog contoh supaya halamannya bisa dilihat berisi.

Setiap baris ditandai jelas sebagai contoh, dan dibuatkan perintah pembersih:

```
php artisan jcorp:clear-samples
```

Perintah ini **hanya** menghapus baris bertanda contoh, tidak menyentuh data sungguhan. Ditulis sekarang juga supaya tidak ada data karangan yang tertinggal saat website tayang.

Sweetness juga diterbitkan (`is_published: true`) sesuai pilihan Anda, supaya bisa dipantau selama pengerjaan.

---

## Langkah 3F — Test

Menambah, bukan mengganti yang sudah ada. Sesuai spec §11:

| Berkas | Membuktikan |
|---|---|
| `PublicProfileTest` | Section kosong **tidak dirender** — termasuk katalog yang seluruh itemnya `is_available: false` |
| `PublicProfileTest` | Anak usaha belum terbit mengembalikan **404**, bukan halaman kosong |
| `PublicProfileTest` | Slug tak dikenal mengembalikan 404 |
| `PublicProfileTest` | `/jcorp-panel` **tidak** tertangkap route `/{slug}` |
| `ImageUploadTest` | Berkas bukan gambar ditolak walau namanya `.jpg` |
| `ImageUploadTest` | Berkas melebihi batas ditolak dengan pesan yang menyebut batasnya |
| `ImageUploadTest` | Nama berkas dibuat ulang — nama asli pengguna tidak dipakai |
| `ImageUploadTest` | Gambar diperkecil dan dikonversi ke WebP |
| `ImageUploadTest` | Berkas lama dihapus saat gambar diganti |

Test kepemilikan dari Fase 1 harus **tetap lulus** — kalau ada yang pecah, berarti ada yang rusak.

---

## Langkah 3G — Verifikasi

Dijalankan sungguhan:

```
php artisan test          seluruh test, termasuk 76 dari Fase 1
vendor/bin/pint --test
vendor/bin/phpstan analyse
npm run types:check
npm run lint:check
npm run format:check
npm run build
```

Plus pemeriksaan manual yang **butuh mata Anda**:

- Buka `/sweetness-things` di desktop dan HP
- Bandingkan dengan mockup — apakah hasilnya setia?
- Unggah foto sungguhan lewat panel, pastikan hasilnya rapi
- Matikan sementara dukungan `backdrop-filter` untuk memastikan cadangan kaca bekerja

---

## Yang tidak dikerjakan di fase ini

- Halaman induk J Corp — Fase 4
- Empat anak usaha lain — Fase 5
- Form kontak tersimpan, dua bahasa, keranjang belanja — di luar lingkup (spec §13)

---

## Perkiraan berkas

```
app/
├── Console/Commands/ClearSamplesCommand.php    baru
├── Http/Controllers/PublicProfileController.php baru
├── Http/Requests/Panel/                         + validasi gambar
└── Services/ImageService.php                    baru

resources/
├── css/app.css                                  token design system
└── js/
    ├── components/public/                       9 komponen baru
    ├── components/panel/                        form & tabel
    └── pages/
        ├── public/business.tsx                  baru
        ├── errors/                              404 & 500
        └── panel/                               ditulis ulang

database/seeders/SweetnessSampleSeeder.php       baru
routes/web.php                                   + route /{slug}
tests/Feature/PublicProfileTest.php              baru
tests/Feature/ImageUploadTest.php                baru
vite.config.ts                                   font diganti
```

Tidak ada commit — sesuai `CLAUDE.md`.
