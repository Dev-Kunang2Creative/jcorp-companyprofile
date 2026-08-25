# J Corp Company Profile — Design Spec

**Tanggal:** 10 Agustus 2026
**Status:** Disetujui, siap masuk tahap desain visual
**Metode:** Hasil `superpowers:brainstorming`

---

## 1. Ringkasan

Satu website berisi enam company profile: J Corp sebagai induk, dan lima anak usaha di bawahnya. Dilengkapi panel admin tersembunyi untuk mengelola katalog, portfolio, dan informasi kontak.

### Enam entitas

| Slug | Nama | Bidang |
|---|---|---|
| `jcorp` | J Corp | Induk usaha |
| `sweetness-things` | Sweetness Things | Dessert box, cookies |
| `nails-by-me` | Nail's by Me | Nail art |
| `ngelash` | ngelash.id | Eyelash extension |
| `ayodya-logistic` | PT. Ayodya Utama Logistic | Logistik |
| `j-land-property` | J-Land Property | Property & kos |

> **Perubahan 22 Agustus 2026.** Unit properti semula bernama **Lumintu Property** dengan slug `lumintu-property`. Berganti jadi J-Land Property beserta alamat halamannya; alamat lama dialihkan permanen (301) di `routes/web.php`. Nama induk juga berganti dari "J Corp" jadi **J-Corporate Group** pada 21 Agustus.

### Kondisi konten saat spec ditulis

Dari enam entitas, **hanya Sweetness Things yang punya logo**. Seluruh logo lain, semua foto produk, semua foto portfolio, dan seluruh teks belum tersedia.

Konsekuensi yang mengikat seluruh rancangan: website harus tetap tampil rapi saat konten baru terisi sebagian. Ini bukan preferensi, melainkan syarat kelayakan tayang.

---

## 2. Tech Stack

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 13 |
| Penghubung | Inertia.js |
| Frontend | React |
| Database | MySQL 8.4 |

**Inertia bukan API.** Controller Laravel mengirim data langsung ke komponen React sebagai props. Tidak ada endpoint JSON, tidak ada `fetch` di frontend, tidak ada state management untuk data server. Satu sumber kebenaran di Laravel; React murni mengurus tampilan.

### Lingkungan pengembangan (terverifikasi 10 Agustus 2026)

| Komponen | Versi |
|---|---|
| PHP | 8.3.30 (Laragon) |
| MySQL | 8.4.3 (Laragon) |
| Node | v22 |
| Composer | 2.8.6 |

Ekstensi PHP aktif: `zip`, `gd`, `intl`, `pdo_mysql`, `fileinfo`, `exif`, `mbstring`, `openssl`, `curl`, `bcmath`.

`gd` dipakai untuk pemrosesan gambar. `intl` dipakai untuk format Rupiah.

**Catatan PATH:** XAMPP sudah dikeluarkan dari System PATH. PHP dipanggil dari Laragon. Jalankan perintah `php`/`composer`/`artisan` lewat Laragon Terminal, atau pastikan PATH mengarah ke `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64`.

**Versi paket dikunci saat instalasi** dan dicatat di `README.md`. Library pemrosesan gambar ditentukan dan diverifikasi di Fase 1 — belum ditetapkan di spec ini.

---

## 3. Arsitektur & Routing

### Route publik

```
GET  /                    → halaman induk (etalase J Corp)
GET  /{slug}              → profil anak usaha
```

Satu route berparameter untuk kelima anak usaha, bukan lima route terpisah. `slug` dicocokkan ke kolom di tabel `businesses`. Menambah anak usaha keenam nanti cukup menambah baris di database — tanpa menyentuh kode routing.

### Struktur halaman anak usaha

```
[Hero]        nama, tagline, logo
[Tentang]     cerita singkat
[Katalog]     grid item — label menyesuaikan
[Portfolio]   grid foto hasil kerja
[Kontak]      WhatsApp, Instagram, alamat, jam buka
```

**Aturan section kosong:** section tanpa isi tidak dirender sama sekali. Bukan ditampilkan kosong, bukan diisi "coming soon". Ini yang membuat website tetap layak tayang saat konten terisi sebagian.

Berlaku per section, dengan penilaian berdasarkan apa yang benar-benar tampil di halaman publik:

| Section | Hilang bila |
|---|---|
| Tentang | `description` kosong |
| Katalog | Tidak ada item dengan `is_available: true` — termasuk saat semua item disembunyikan sementara |
| Portfolio | Tidak ada `portfolio_items` |
| Kontak | Semua kolom kontak kosong |

Hero selalu dirender — minimal memuat nama usaha.

### Label yang menyesuaikan

Kolom `catalog_label` dan `portfolio_label` menentukan sebutan di layar. Nilai awal (dapat diubah admin):

| Anak usaha | catalog_label | portfolio_label |
|---|---|---|
| Sweetness Things | "Menu Kami" | *(tidak dipakai)* |
| Nail's by Me | "Layanan & Harga" | "Hasil Kerja" |
| ngelash.id | "Layanan & Harga" | "Hasil Kerja" |
| Ayodya Utama Logistic | "Layanan Kami" | *(tidak dipakai)* |
| Lumintu Property | "Unit Tersedia" | *(tidak dipakai)* |

### Halaman induk

Route dan komponen terpisah karena bentuknya berbeda: hero J Corp, profil singkat, lalu lima kartu anak usaha dari database, diurutkan berdasarkan `sort_order`.

Peran induk: **etalase + profil singkat.** Tidak punya katalog sendiri. Data lengkap induk belum tersedia, jadi bagian profil dibuat ringkas dan bisa diperpanjang nanti tanpa merombak struktur.

---

## 4. Struktur Database

### `businesses`

```
id
slug              unique
name
tagline
description
logo_path
catalog_label
portfolio_label
whatsapp
instagram
tiktok
address
business_hours
is_parent         boolean — true hanya untuk J Corp
is_published      boolean — sakelar terbit
sort_order        integer
timestamps
softDeletes
```

Kolom kontak dipisah-pisah, bukan satu kolom serbaguna — agar nomor WhatsApp bisa divalidasi formatnya dan link `wa.me` dibentuk dengan benar.

**`is_published`** menentukan apakah profil muncul sebagai kartu di halaman induk dan bisa diakses. Yang bernilai false mengembalikan 404 — bukan pesan "belum tersedia", supaya keberadaannya tidak terungkap.

**`is_published` hanya membatasi halaman publik, bukan panel admin.** Admin anak usaha yang belum diterbitkan tetap bisa masuk dan mengisi katalog, portfolio, serta info kontaknya. Justru itu gunanya: konten disiapkan sampai lengkap, baru diterbitkan dengan satu sakelar — tidak ada halaman setengah jadi yang terlihat publik.

Keenam baris ada di database sejak Fase 1, termasuk yang belum diterbitkan.

### `catalog_items`

```
id
business_id       FK → businesses
name
description
image_path
price             decimal, nullable
price_note        string, nullable — "mulai dari", "per bulan", "nego"
category          nullable
is_available      boolean
sort_order        integer
timestamps
softDeletes
```

**Harga dipisah dua kolom.** `price` sebagai angka, `price_note` sebagai keterangan. Harga yang disimpan sebagai teks bebas ("150rb", "Rp 150.000", "150000") akan berantakan karena beberapa admin menulis dengan gaya berbeda, dan tidak bisa diurutkan.

Admin mengisi `150000` dan memilih keterangan → tampil **"mulai dari Rp 150.000"** dengan format konsisten.

`price` boleh kosong — item tanpa harga tampil tanpa baris harga sama sekali, bukan "Rp 0".

**`is_available` berbeda dari soft delete.** `is_available: false` menyembunyikan item sementara dari halaman publik (stok habis, layanan sedang tidak dilayani) — datanya utuh dan tetap terlihat di panel admin, tinggal dinyalakan lagi. Soft delete untuk item yang memang dihapus; tidak muncul di panel admin, dan hanya bisa dipulihkan lewat database.

### `portfolio_items`

```
id
business_id       FK → businesses
image_path
caption
sort_order
timestamps
softDeletes
```

### `users`

```
id
name
email             unique
password          hashed
role              enum: super_admin | business_admin
business_id       FK → businesses, nullable (hanya untuk business_admin)
timestamps
```

**Satu admin memegang satu anak usaha.** Kolom `business_id` tunggal, bukan tabel penghubung. Susunan akun: satu super-admin plus lima business_admin. Halaman induk J Corp dikelola super-admin.

### Soft delete

Data yang dihapus admin ditandai terhapus, bukan dimusnahkan. Dengan beberapa orang berbeda yang mengelola, salah hapus adalah soal waktu — dan foto produk yang hilang tidak bisa dikembalikan. Biayanya satu kolom tambahan.

---

## 5. Penanganan Gambar

Foto dari HP berukuran 3–5 MB. Dengan 20 item, halaman jadi berat puluhan MB dan lambat di jaringan seluler — kondisi mayoritas pengunjung.

**Setiap gambar yang diunggah:**
- Diperkecil otomatis ke ukuran wajar untuk web
- Dibuatkan versi kecil (thumbnail) untuk grid
- Dikonversi ke format yang lebih ringan

Admin mengunggah foto apa adanya dari HP; pengecilan terjadi di server.

**Validasi unggahan:**
- Hanya berkas gambar sungguhan — diperiksa isinya, bukan akhiran nama berkas
- Ada batas ukuran
- Nama berkas dibuat ulang oleh sistem; nama asli dari pengguna tidak pernah dipakai langsung

---

## 6. Panel Admin & Keamanan

### Alamat masuk

```
GET  /jcorp-panel          → halaman login
     /jcorp-panel/*        → halaman admin
```

Tidak ada tombol atau tautan ke panel di seluruh website publik.

### Lapisan pengamanan

**URL tersembunyi adalah penyamaran, bukan kunci.** Alamat bocor lewat history browser, header `Referer`, log server, ekstensi browser, atau sekali salah kirim link. Yang melindungi adalah lapisan di baliknya:

- Halaman admin diberi tag `noindex` — tidak masuk hasil pencarian
- Login memakai sistem bawaan Laravel: password ter-hash, proteksi CSRF di setiap form, sesi kedaluwarsa
- Pembatasan percobaan login — setelah beberapa kali gagal, alamat dikunci sementara
- Tidak ada halaman pendaftaran; akun dibuat lewat perintah artisan

### Pengecekan kepemilikan

**Aturan yang tidak boleh dilanggar: setiap aksi simpan, ubah, dan hapus diperiksa di sisi Laravel, bukan hanya disembunyikan di tampilan.**

Menyembunyikan tombol hapus milik anak usaha lain tidak cukup — permintaan hapus tetap bisa dikirim langsung ke server dengan mengubah angka di alamat. Setiap kali admin menyentuh sebuah item, Laravel memastikan item itu memang miliknya. Kalau bukan, permintaan ditolak.

Berlaku juga untuk data yang ditampilkan: business_admin hanya menerima data miliknya sendiri dari server — data anak usaha lain tidak pernah dikirim ke browsernya.

### Isi panel

```
/jcorp-panel                  Dashboard — ringkasan jumlah item
/jcorp-panel/catalog          Kelola katalog
/jcorp-panel/portfolio        Kelola portfolio
/jcorp-panel/profile          Info kontak, tagline, label section
/jcorp-panel/users            Kelola akun — super-admin saja
/jcorp-panel/businesses       Kelola anak usaha & sakelar terbit — super-admin saja
```

Business_admin melihat empat menu pertama, semuanya terikat ke anak usahanya. Super-admin melihat semua, plus pemilih anak usaha untuk berpindah konteks.

### Pengalaman pemakaian

- Form yang gagal validasi mengembalikan isian yang sudah diketik
- Konfirmasi hapus menyebut nama item, bukan sekadar "yakin?"
- Unggah gambar menampilkan pratinjau sebelum disimpan
- Urutan item diatur dengan kolom angka, bukan seret-lepas — lebih jelas dan tidak bermasalah di layar HP

### Cakupan yang bisa diubah admin

**Bisa:** item katalog, foto portfolio, nomor WhatsApp, Instagram/TikTok, alamat, jam buka, label section.

**Di kode (jarang berubah):** nama, tagline, cerita perusahaan, foto hero.

---

## 7. Arah Visual

Permintaan client: **terang, profesional, glassmorphism.**

Latar yang dipilih: **putih kebiruan dengan gradasi warna lembut.** Glassmorphism bekerja dengan menumpuk panel semi-transparan di atas latar berwarna — di atas putih polos, panel kaca terlihat seperti kotak abu-abu biasa dan efeknya hilang.

### Empat batasan teknis yang mengikat

**1. Jumlah panel kaca dibatasi per layar.** `backdrop-filter: blur()` dihitung ulang saat halaman digulir dan berat bagi HP kelas bawah. Efek kaca dipakai untuk elemen menonjol — navigasi, panel hero, kartu yang disorot. Kartu dalam grid memakai latar padat dengan transparansi ringan tanpa blur. Nyaris tidak terbedakan secara visual, jauh berbeda bebannya.

**2. Wajib ada cadangan kalau blur tidak didukung.** Sebagian browser lama tidak mengenal `backdrop-filter`; tanpa cadangan, panel jadi transparan penuh dan teks tidak terbaca. Setiap panel kaca wajib punya warna latar cadangan.

**3. Kontras teks diperiksa sungguhan.** Kelemahan bawaan glassmorphism di tema terang: teks gelap di atas panel semi-transparan yang latar belakangnya berubah warna. Warna teks dan tingkat transparansi diuji terhadap area tergelap dan tercerah dari gradasi, bukan satu titik saja.

**4. Mobile dirancang sendiri, bukan hasil pengecilan.** Mayoritas pengunjung datang dari HP, sering lewat tautan Instagram. Layout mobile dirancang sebagai pass tersendiri — termasuk tombol WhatsApp yang cukup besar untuk jempol, dan gradasi yang tidak terpotong aneh di layar sempit.

### Warna per entitas

Tiap anak usaha punya nuansa berbeda sesuai karakter bisnisnya. Struktur dan komponen tetap identik; yang berbeda hanya warna aksen dan gradasi latar.

**Sweetness Things** — palet diturunkan dari logonya.

### Logo Sweetness Things

Berkas: `Logo-Sweetness.jpeg`, 828×750 px.

Warna dominan terbaca dari piksel:

| Porsi | Hex | Keterangan |
|---|---|---|
| 85% | `#FFFFFE` | Latar putih |
| 3,5% | `#B28C27` | Emas tua — warna utama |
| 1,7% | `#B6B5B6` | Abu netral |
| ~5% | `#EAE3CD`, `#DFD3B0`, `#C7AE6B` | Gradasi emas muda ke tua |

**Bentuk:** monogram "ST" kaligrafis dengan huruf sambung menjalin, batang T memanjang sebagai sapuan lengkung, ukiran melingkar di bawah kiri. Dikelilingi bingkai lingkaran berupa sabit tipis yang tidak menutup penuh, dengan taburan bintang kecil beragam ukuran plus dua bintang besar bersudut delapan (kanan atas dan bawah tengah). Seluruhnya emas di atas putih, komposisi melingkar simetris.

**Implikasi untuk desain:**

1. **Logo tidak memuat nama usaha** — tulisan "Sweetness Things" harus muncul sebagai teks. Pilihan huruf ikut membentuk identitas merek.
2. **Huruf antarmuka harus menahan diri** — logo sudah sangat ornamen. Huruf sambung yang ramai akan berebut perhatian.
3. **Komposisi melingkar menguntungkan** — cocok jadi lencana bulat untuk navigasi, foto profil, dan favicon tanpa digambar ulang.
4. **Motif bintang bisa jadi benang merah** — dipakai sangat samar di latar gradasi Sweetness Things, dengan syarat tidak mengganggu keterbacaan.

**Masalah teknis:** berkasnya JPEG, yang tidak bisa punya latar transparan. Di atas panel kaca atau gradasi akan muncul kotak putih. **Keputusan:** latar putih dihapus lewat proses otomatis di Fase 2, menghasilkan PNG transparan plus versi lencana bulat. Kalau tepi kurva kaligrafi ternyata bergerigi, itu jadi alasan konkret meminta berkas SVG/PNG asli ke pembuat logo.

### Yang diputuskan `professional-designer` di Fase 2

Palet dasar dan warna khas per entitas, pasangan tipografi, skala jarak dan sudut membulat, tingkat transparansi dan blur, bentuk kartu dan tombol. Dituangkan ke `DESIGN_SYSTEM.md` di root project, lalu diwujudkan jadi mockup Sweetness Things (desktop + mobile) untuk disetujui sebelum kode UI ditulis.

---

## 8. Kontak

**WhatsApp untuk semua entitas.** Tombol dengan pesan pembuka terisi otomatis yang menyebut nama usaha dan item yang dilihat — misalnya "Halo Sweetness Things, saya mau tanya soal Dessert Box Coklat".

Alasan: paling cepat direspons, sesuai kebiasaan pasar Indonesia, tidak perlu mengurus email server, dan tidak menyimpan data pengunjung sehingga tidak ada beban kewajiban perlindungan data.

**Siap ditambah form nanti.** Client kemungkinan meminta form tersimpan di kemudian hari. Karena itu: nomor WA disimpan di database (bukan ditanam di kode), dan komponen kontak dibuat terpisah — menambah form nanti berarti menambah, bukan mengganti.

---

## 9. Bahasa

**Bahasa Indonesia.**

Empat dari lima anak usaha melayani konsumen Indonesia langsung (dessert, nail art, eyelash, kos) — Bahasa Inggris tidak memberi keuntungan. Yang perlu dipertimbangkan hanya Ayodya Logistic, dan itu bergantung pada apakah melayani pengiriman internasional atau klien asing — belum dipastikan.

Struktur database tetap disiapkan agar penambahan bahasa kedua nanti tidak perlu membongkar tabel.

---

## 10. Penanganan Kegagalan

| Kejadian | Penanganan |
|---|---|
| Slug salah / belum diterbitkan | 404 bergaya website, tautan ke halaman induk. Yang belum terbit tetap 404 — bukan "belum tersedia" |
| Gambar gagal dimuat | Kotak berisi inisial nama item, bukan ikon rusak |
| Unggahan ditolak | Pesan menyebut alasan dan batas yang berlaku; isian form tetap utuh |
| Kesalahan server | Halaman 500 bergaya sama, tanpa detail teknis. Detail masuk log server |
| Sesi admin kedaluwarsa | Diarahkan ke login dengan keterangan, kembali ke halaman semula setelah masuk |

---

## 11. Pengujian

Sesuai `CLAUDE.md`: pengujian untuk logika nyata, tidak untuk tampilan dan konten statis.

**Diuji:**

- **Pengecekan kepemilikan** — bukti bahwa admin satu anak usaha tidak bisa mengubah atau menghapus item milik anak usaha lain, termasuk saat permintaan dikirim langsung ke server dengan mengubah angka di alamat. Ini yang membuktikan sistem peran berfungsi.
- Pembatasan akses halaman admin bagi yang belum login
- Pembatasan menu super-admin
- Format harga (`150000` + "mulai dari" → "mulai dari Rp 150.000")
- Validasi unggahan gambar
- Aturan section kosong tidak dirender — termasuk katalog yang seluruh itemnya `is_available: false`
- Anak usaha belum terbit mengembalikan 404 di halaman publik, tapi adminnya tetap bisa mengelola kontennya

**Tidak diuji:** tampilan halaman, susunan CSS, isi teks, berkas konfigurasi. Verifikasi lewat mata di browser.

---

## 12. Fase Pengerjaan

Pendekatan: **bertahap, satu profil dulu sampai tuntas.**

Alasannya: Sweetness Things adalah satu-satunya yang punya logo, dan profil yang sudah jadi menjadi contoh konkret saat meminta materi ke lima unit lain. Kalau ada yang perlu diperbaiki di desain, ketahuan saat baru satu profil.

| Fase | Isi |
|---|---|
| **1 — Fondasi** | Instalasi Laravel + Inertia + React + MySQL. Empat tabel dan data awal enam entitas. Sistem login, peran, pengecekan kepemilikan beserta pengujiannya. Belum ada tampilan publik. |
| **2 — Desain** | `professional-designer` menyusun `DESIGN_SYSTEM.md`, memproses logo Sweetness, membuat mockup Sweetness Things (desktop + mobile). **Berhenti menunggu persetujuan.** |
| **3 — Sweetness Things** | Halaman profil lengkap sesuai design system, plus panel admin yang bisa dipakai sungguhan untuk mengisi kontennya. |
| **4 — Halaman induk** | Etalase J Corp dengan kartu anak usaha. Setelah fase ini website bisa diakses publik. |
| **5 — Empat profil sisanya** | Menyusul seiring konten terkumpul, satu per satu diterbitkan lewat sakelar `is_published`. |

Tiap fase berakhir dengan verifikasi nyata — dijalankan dan diperiksa, bukan diasumsikan.

---

## 13. Di Luar Lingkup

Tidak dikerjakan sekarang, bisa ditambahkan nanti tanpa membongkar yang sudah ada:

- Form kontak tersimpan (WhatsApp dulu; struktur disiapkan)
- Dua bahasa
- Keranjang belanja / pembayaran
- Blog / artikel
- Pencarian

---

## 14. Catatan Operasional

### Lokasi project

**Lokasi aktif:** `D:\Kuliah\Bahan Kuliah\Matkul\Vscode\jcorp-company-profile`

Dipindahkan 10 Agustus 2026 dari `C:\Users\Mario Sianturi\.claude\J Corp-Company Profile`. Lokasi lama berada di dalam folder aplikasi Claude Code — berisiko karena dibersihkan otomatis oleh aplikasi, dan menyimpan `.credentials.json` di direktori yang sama.

Nama folder diubah jadi `jcorp-company-profile` (tanpa spasi) agar path tidak perlu diapit tanda kutip di terminal.

Folder lama masih ada sebagai cadangan, bisa dihapus setelah lokasi baru dipastikan berjalan normal.

**Riwayat chat sesi brainstorming tidak ikut pindah.** Claude Code menyimpan riwayat di `~/.claude/projects/` dengan nama folder yang diturunkan dari path project — begitu path berubah, sesi baru tidak menemukan riwayat lama. Datanya tidak hilang, hanya tidak terbaca. **Spec ini adalah pembawa seluruh keputusan ke sesi berikutnya.**

### Catatan Laragon

Laragon otomatis membuatkan alamat `.test` hanya untuk project di dalam `C:\laragon\www`. Karena project ini berada di drive D, server dijalankan manual dengan `php artisan serve`.

Kalau nanti ingin memakai alamat `.test`, project bisa dipindah ke `C:\laragon\www` atau host virtual dikonfigurasi manual lewat Laragon.

### Git

Repo sudah diinisialisasi di branch `main`. Belum ada commit — sesuai aturan di `CLAUDE.md`, commit hanya dilakukan atas permintaan eksplisit.

### Pembagian skill

Sesuai bagian 4 `CLAUDE.md`:

| Tahap | Pemilik |
|---|---|
| Spec | `superpowers:brainstorming` (selesai) |
| Design system & mockup | `professional-designer` |
| Rencana implementasi | `superpowers:writing-plans` |
| Kode | `professional-programmer` |
| Verifikasi | `superpowers:verification-before-completion` |
| Bug | `superpowers:systematic-debugging` |

### Keterbatasan sesi

Membaca gambar lewat path file **gagal** di sesi ini; gambar yang di-paste langsung ke percakapan **berhasil**. Ini kebalikan dari catatan di `CLAUDE.md`. Untuk verifikasi visual mockup nanti, gunakan paste langsung.

---

## 15. Materi yang Perlu Dikumpulkan

Per anak usaha:

- **Logo** — SVG atau PNG latar transparan (Sweetness sudah ada, tapi JPEG)
- **Foto katalog** — per item, pencahayaan konsisten
- **Foto portfolio** — hasil kerja (Nail's by Me, ngelash.id)
- **Teks** — tagline satu kalimat, cerita singkat 2–3 paragraf
- **Kontak** — nomor WhatsApp, akun Instagram/TikTok, alamat, jam buka
- **Daftar item** — nama, harga, keterangan harga, deskripsi singkat

Untuk J Corp: logo, tagline, profil singkat, kontak.

**Catatan foto:** arah visual yang disetujui menuntut foto bersih dan konsisten. Foto dengan pencahayaan seadanya akan terlihat janggal di tengah tampilan yang rapi.
