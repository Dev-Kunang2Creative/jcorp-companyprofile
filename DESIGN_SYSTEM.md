# DESIGN SYSTEM — J Corp Company Profile

**Dibuat:** 11 Agustus 2026 · **Fase 2**
**Arah terpilih:** Butik Pâtisserie dengan panel kaca — gabungan arah A dan B
**Mockup:** [`public/preview/mockup.html`](public/preview/mockup.html)

> Dokumen ini **mengikat**. Saat menulis kode UI di Fase 3 dan seterusnya, ikuti angka di sini — jangan menentukan warna, ukuran huruf, atau jarak baru secara mandiri. Kalau ada yang terasa kurang pas saat diterapkan, ubah dokumen ini lebih dulu, baru kodenya.

---

## 1. Kepribadian

**Butik pâtisserie yang percaya diri** — manis tapi punya harga diri, bukan manis yang murahan.

Tiga kata kunci yang memandu setiap keputusan: **hangat, tenang, rapi.**

Yang membedakannya dari template dessert kebanyakan:

- Gradasi menempel di **sudut**, tidak jadi latar penuh — terang tanpa terasa seperti template
- Serif tebal berkarakter (Fraunces), bukan serif netral
- Panel kaca **dibatasi tiga per layar** — bukan ditempel di mana-mana
- Motif bintang dari logo dipakai sangat samar sebagai benang merah

---

## 2. Warna

### Token lengkap

```css
:root {
  /* Latar & permukaan */
  --cream:         #FDFBF6;   /* latar utama halaman */
  --cream-warm:    #FBF7EF;   /* permukaan alternatif */
  --white:         #FFFFFF;   /* kartu katalog, section tentang */

  /* Titik gradasi */
  --grad-gold:     #F7E7C4;   /* emas muda — sudut terang */
  --grad-blush:    #F3E8DC;   /* krem merah muda */
  --grad-deep:     #EADCC0;   /* titik TERGELAP — acuan uji kontras */

  /* Emas — TIGA PERAN BERBEDA, jangan ditukar (lihat §2.1) */
  --gold:          #B28C27;   /* logo, garis, ikon besar. TIDAK untuk teks */
  --gold-mid:      #8A6A15;   /* judul >=24px saja */
  --gold-deep:     #6B4F0F;   /* semua teks emas & tombol */
  --gold-hover:    #563F0B;   /* tombol saat disentuh */

  /* Teks */
  --ink:           #2E2A24;   /* teks utama & judul */
  --ink-soft:      #5B5348;   /* teks sekunder & deskripsi */
  --ink-invert:    #FFFFFF;   /* teks di atas tombol emas */

  /* Garis */
  --line:          #EDE6D8;   /* garis kartu & pemisah */
  --line-glass:    rgba(255,255,255,.85);
}
```

### 2.1 Aturan emas — paling sering dilanggar

Logo Sweetness berwarna `#B28C27`. Warna itu **gagal WCAG AA sebagai teks** — rasionya hanya 3,15 di atas putih dan **2,32** di titik gradasi tergelap, sedangkan syaratnya 4,5.

Jadi emasnya dipecah tiga, dengan nada yang sama tapi ketuaan berbeda:

| Token | Boleh dipakai untuk | Dilarang untuk |
|---|---|---|
| `--gold` `#B28C27` | Logo, garis dekoratif, ikon ukuran besar, titik motif bintang | **Teks apa pun**, latar tombol |
| `--gold-mid` `#8A6A15` | Judul ≥24px di atas putih/krem | Teks kecil, teks di atas gradasi |
| `--gold-deep` `#6B4F0F` | Semua teks emas, latar tombol, garis tepi tombol | — |

`--gold-deep` sudah diuji di **seluruh** titik gradasi: terburuk 5,63, di atas putih 7,64 (AAA).

### 2.2 Hasil pemeriksaan kontras

Semua pasangan yang benar-benar dipakai, diuji pada kondisi terburuk (panel kaca 0,68 di atas titik gradasi tergelap):

| Pasangan | Rasio | Syarat | |
|---|---|---|---|
| Judul & teks utama di panel kaca | 12,99 | 4,5 | AAA |
| Teks sekunder di panel kaca | 6,89 | 4,5 | AA |
| Eyebrow emas di panel kaca | 6,96 | 4,5 | AA |
| Teks di kartu katalog | 14,26 | 4,5 | AAA |
| Deskripsi kartu | 7,57 | 4,5 | AAA |
| Harga & kategori di kartu | 7,64 | 4,5 | AAA |
| Teks putih di tombol emas | 7,64 | 4,5 | AAA |
| Teks putih di tombol saat hover | 9,95 | 4,5 | AAA |
| Teks footer | 8,50 | 4,5 | AAA |
| Tautan footer | 10,48 | 4,5 | AAA |
| Inisial di kotak gambar gagal | 6,78 | 3,0 | lulus |

**Satu pengecualian yang disengaja:** garis tepi panel kaca (`--line-glass`) rasionya hanya 1,11 terhadap gradasi. Dibiarkan samar dengan sadar — garis itu murni dekoratif, tidak membawa informasi, dan batas panel sudah jelas dari perbedaan warna latarnya sendiri. Menaikkan kontrasnya akan membuat garisnya keras dan merusak kesan kaca.

### 2.3 Anak usaha lain

Struktur dan komponen **identik**; yang berbeda hanya warna aksen dan titik gradasi. Saat menyusun palet anak usaha berikutnya, ulangi aturan §2.1: warna merek untuk elemen besar, varian lebih tua yang lolos AA untuk teks dan tombol.

Palet Sweetness (emas) **hanya untuk Sweetness**.

---

## 3. Tipografi

### Pasangan

| Peran | Font | Alasan |
|---|---|---|
| Display & judul | **Fraunces** 400/600/700 | Serif dengan karakter kuat — hangat dan sedikit nakal, cocok untuk dessert tanpa jadi kekanakan |
| Antarmuka & isi | **Jost** 400/500/600 | Geometris bersih. Logo sudah sangat ornamen; huruf antarmuka harus menahan diri (spec §7) |

Keduanya **SIL Open Font License** — bebas untuk penggunaan komersial. Disediakan lewat Bunny Fonts (`@fonts` di Blade, diunduh ke server sendiri — tidak ada permintaan ke pihak ketiga saat pengunjung membuka halaman).

**Jangan** memakai Inter atau system-ui sebagai font utama — itu salah satu penanda tampilan generik.

### Skala

| Peran | Desktop | Mobile | Font & tebal |
|---|---|---|---|
| Judul hero | 46px / 1,06 | 31px | Fraunces 600, `letter-spacing: -.015em` |
| Judul section | 30px / 1,15 | 24px | Fraunces 600 |
| Judul kartu | 17px | 15px | Fraunces 600 |
| Isi hero | 16,5px / 1,65 | 15px | Jost 400 |
| Isi umum | 15,5px / 1,75 | 15px | Jost 400 |
| Deskripsi kartu | 13,5px / 1,6 | *disembunyikan* | Jost 400 |
| Eyebrow | 11px, `letter-spacing:.22em`, HURUF BESAR | sama | Jost 500 |
| Kategori kartu | 10,5px, `letter-spacing:.16em`, HURUF BESAR | sama | Jost 500 |
| Tombol | 15px | 15px | Jost 500 |
| Harga | 14,5px | 14,5px | Jost 500 |

Lebar baris teks panjang dibatasi **`max-width: 42ch`** (hero) dan **`52ch`** (isi) supaya nyaman dibaca.

---

## 4. Jarak & sudut

Kelipatan 4. Jangan memakai angka di luar daftar ini.

```css
--s1:4px;  --s2:8px;  --s3:12px; --s4:16px; --s5:24px;
--s6:32px; --s7:48px; --s8:64px; --s9:96px;
```

| Penggunaan | Desktop | Mobile |
|---|---|---|
| Padding section | `--s8` `--s6` | `--s7` `--s4` |
| Padding hero | `--s8` `--s6` `--s7` | `--s6` `--s4` `--s7` |
| Jarak antar kartu | `--s4` | `--s3` |
| Padding kartu | `--s4` | `--s3` |

### Sudut membulat

```css
--r-sm:8px;    /* kotak kecil, tombol menu */
--r-md:12px;   /* kartu katalog & portfolio */
--r-lg:20px;   /* panel kaca */
--r-full:999px;/* tombol */
```

Sudutnya **tidak seragam** — panel kaca lebih membulat daripada kartu, dan tombol berbentuk kapsul penuh. Keseragaman sudut di semua elemen adalah salah satu penanda tampilan generik.

---

## 5. Glassmorphism — empat batasan mengikat

Spec §7 menetapkan empat batasan teknis. Berikut penerapannya.

### 5.1 Jumlah panel kaca dibatasi

**Maksimal tiga panel kaca per layar.** `backdrop-filter: blur()` dihitung ulang saat halaman digulir dan berat bagi HP kelas bawah.

| Elemen | Kaca? |
|---|---|
| Navigasi | Ya |
| Panel hero | Ya |
| Panel kontak | Ya |
| **Kartu katalog & portfolio** | **Tidak** — latar padat `#FFFFFF` + garis `--line` |

Kartu dalam grid nyaris tidak terbedakan secara visual dari versi kaca, tapi jauh berbeda bebannya — terutama saat digulir dengan 20 kartu di layar.

### 5.2 Cadangan wajib

Setiap panel kaca **wajib** menuliskan warna latar padat lebih dulu, baru menambahkan blur di dalam `@supports`. Tanpa ini, browser lama merender panel transparan penuh dan teksnya tidak terbaca.

```css
.panel {
  background: var(--cream);              /* cadangan — ditulis LEBIH DULU */
  border: 1px solid var(--line-glass);
  box-shadow: 0 8px 32px rgba(90,72,40,.10);
}

@supports (backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px)) {
  .panel {
    background: rgba(255,255,255,.68);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
  }
}
```

Nilai kaca:

```css
--glass-bg:       rgba(255,255,255,.68);
--glass-blur:     16px;
--glass-fallback: #FDFBF6;
--glass-shadow:   0 8px 32px rgba(90,72,40,.10);
```

Opasitas boleh disetel antara **0,55–0,78** — seluruh rentang itu sudah diuji dan lolos AA di semua titik gradasi.

### 5.3 Kontras diuji terhadap seluruh gradasi

Sudah dikerjakan — lihat §2.2. **Setiap warna teks baru wajib diuji ulang terhadap `--grad-deep` `#EADCC0`**, bukan terhadap putih. Itu titik tergelap gradasi dan penentu sebenarnya.

### 5.4 Mobile dirancang sendiri

Lihat §7. Mobile bukan hasil pengecilan desktop.

---

## 6. Komponen

### Tombol

| Jenis | Gaya |
|---|---|
| Utama | Latar `--gold-deep`, teks putih, `--r-full`, padding `14px 28px`. Hover → `--gold-hover` |
| Sekunder | Transparan, teks `--gold-deep`, garis 1px `--gold`, `--r-full`. Hover → latar `--gold-deep`, teks putih |
| Navigasi WA | Sama seperti utama, padding lebih kecil `9px 18px` |

Semua tombol dan tautan wajib punya **target sentuh minimal 44×44px** di mobile.

### Kartu katalog

```
[gambar 4:3]
[kategori]          10,5px HURUF BESAR, --gold-deep
[nama]              Fraunces 600 17px
[deskripsi]         13,5px --ink-soft  (disembunyikan di mobile)
[harga] [tombol]    baris terpisah di mobile
```

Latar putih padat, garis `--line`, sudut `--r-md`. Hover: naik 2px + bayangan halus.

**Gambar gagal dimuat** → kotak `#F7F1E4` berisi inisial nama item, Fraunces 600 26px warna `--gold-deep` (spec §10). Bukan ikon rusak.

**Harga** → keterangan ("mulai dari") ditaruh di baris kecil **di atas** angkanya, bukan disambung sebaris — supaya angkanya tetap yang paling menonjol.

### Panel kaca

Sudut `--r-lg`, padding `--s6` (desktop) / `--s5 --s4` (mobile), garis `--line-glass`, bayangan `--glass-shadow`.

### Garis aksen

Batang 52×2px warna `--gold` di bawah judul hero. Elemen kecil yang mengikat halaman ke warna logo.

### Motif bintang

Titik-titik `radial-gradient` sangat samar (opasitas 0,18–0,30) di latar hero — benang merah dari taburan bintang di logo (spec §7 poin 4). **Syaratnya tidak boleh mengganggu keterbacaan**: jangan menaikkan opasitas, dan jangan menaruhnya di belakang blok teks yang padat.

---

## 7. Perilaku responsif

Titik ubah: **768px** (mobile ↔ desktop).

| Elemen | Desktop | Mobile |
|---|---|---|
| Navigasi | Tautan mendatar + tombol WA | Logo + tombol menu; tautan jadi panel geser |
| Grid katalog | 3 kolom | 2 kolom |
| Deskripsi kartu | Tampil | Disembunyikan — di kolom sempit jadi terlalu padat |
| Baris harga+tombol | Sebaris | Bertumpuk, tombol selebar kartu |
| Bagian Tentang | 2 kolom (teks + foto) | 1 kolom, foto di bawah |
| Baris kontak | 2 kolom | 1 kolom |
| Tombol hero | Selebar isinya | **Selebar layar** |
| Tombol WA melayang | Tidak ada | **Ada** — menempel di bawah saat digulir |
| Judul hero | 46px | 31px |

**Tombol WhatsApp melayang hanya ada di mobile.** Mayoritas pengunjung datang dari tautan Instagram di HP, dan tujuan utamanya menghubungi lewat WA — tombol itu tidak boleh sampai hilang saat halaman digulir. Di desktop tidak perlu karena navigasi sudah menempel di atas.

---

## 8. Gambar

- Format **WebP** dengan cadangan PNG/JPEG
- Rasio: kartu katalog **4:3**, foto tentang **4:5**, portfolio **1:1**
- Semua gambar wajib punya `alt` yang bermakna — bukan "gambar" atau nama berkas
- Ukuran: web maksimal 1200px sisi terpanjang, thumbnail 400×400
- `loading="lazy"` untuk gambar di bawah lipatan layar

### Berkas logo Sweetness

Ada di `public/images/brand/`:

| Berkas | Ukuran | Penggunaan |
|---|---|---|
| `sweetness-logo-600.webp` | 600px | Hero desktop |
| `sweetness-logo-300.webp` | 300px | Hero mobile, umum |
| `sweetness-badge-96.png` | 96px | Navigasi |
| `sweetness-badge-48.png` | 48px | Favicon |

Semuanya PNG/WebP latar transparan, sudah dibersihkan dari halo putih — aman ditempatkan di atas gradasi berwarna.

---

## 9. Nada tulisan

Bahasa Indonesia, **sopan tapi tidak kaku**. Menyebut pembaca "Anda".

Yang dihindari:

- Kalimat pemasaran kosong — "solusi terbaik untuk Anda", "kualitas premium terjamin"
- Huruf besar semua untuk penekanan
- Tanda seru berlebihan

Yang dicari: kalimat spesifik yang terasa ditulis orang sungguhan.

> Contoh dari mockup: *"Dibuat sedikit-sedikit tiap hari, supaya yang sampai ke meja Anda masih sehangat waktu keluar oven."*
> Bukan: *"Kue premium berkualitas tinggi dengan cita rasa terbaik!"*

**Semua teks di mockup adalah contoh, bukan final** — menunggu materi dari client.

---

## 10. Yang harus dihindari

Diperiksa ulang sebelum setiap deliverable:

- Hero rata tengah dengan gradasi tanpa identitas lain
- Tema bawaan shadcn/ui dibiarkan apa adanya
- Inter atau system-ui sebagai font utama
- Sudut, bayangan, dan jarak seragam di semua elemen tanpa hierarki
- Bola/blob 3D melayang, gradasi ungu-biru sebagai penanda "teknologi"
- Teks berkesan placeholder
- Komposisi terlalu simetris tanpa titik fokus

---

## 11. Untuk Fase 3

Token di §2, §3, dan §4 dituangkan ke `resources/css/app.css` sebagai CSS custom properties dengan nama yang sama persis, lalu dipetakan ke Tailwind lewat `@theme`.

Yang perlu diingat saat menulis kode:

1. **Jangan** menaruh `dark:` di komponen baru — project ini bertema terang saja. Baris `@custom-variant dark` di `app.css` wajib dipertahankan (alasannya ada di komentar berkas itu).
2. Cadangan panel kaca ditulis **sebelum** blok `@supports`, selalu.
3. Warna teks emas selalu `--gold-deep`, tidak pernah `--gold`.
4. Maksimal tiga panel kaca per layar.
5. Mobile dikerjakan sebagai pass tersendiri, bukan hasil pengecilan.
