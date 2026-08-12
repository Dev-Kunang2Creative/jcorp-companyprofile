# DESIGN SYSTEM — J Corp Company Profile

**Dibuat:** 11 Agustus 2026 · **Fase 2**
**Direvisi:** 12 Agustus 2026 — glassmorphism diperluas ke seluruh aplikasi (§5 ditulis ulang)
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
- Kaca punya **tiga tingkat ketebalan**, bukan satu opasitas yang ditempel rata di mana-mana
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
  --grad-rose:     #F6E4E0;   /* merah muda pucat — sudut kanan bawah */
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

  /* Garis — tembus pandang sejak revisi 12 Agu. Garis PADAT memotong
     permukaan kaca dan membuatnya terbaca sebagai kotak biasa yang
     kebetulan buram. */
  --line:          rgba(160,132,74,.18);  /* garis kartu & pemisah */
  --line-strong:   rgba(160,132,74,.28);  /* garis tombol & kotak isian */
  --line-glass:    rgba(255,255,255,.70); /* tepi sorot panel kaca */

  /* Merah — untuk pesan galat & tombol hapus. Merah bata, bukan merah
     layar bawaan: yang terakhir menabrak seluruh palet hangat. */
  --danger:        #9B2C1F;
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

Sejak seluruh aplikasi berpermukaan kaca, ujinya bukan lagi satu opasitas melainkan **tiap tingkat kaca × tiap titik gradasi**. Angka di bawah adalah **yang terburuk** dari seluruh kombinasi itu — termasuk `--grad-deep`, titik tergelap.

Diuji ulang 12 Agustus 2026 dengan tingkat kaca di §5.1:

| Teks | Kaca tipis (0,42) | Kartu (0,52) | Bar (0,55) | Panel (0,62) | Menonjol (0,80) | Syarat | |
|---|---|---|---|---|---|---|---|
| `--ink` — judul & teks utama | 12,00 | 12,37 | 12,48 | 12,75 | 13,45 | 4,5 | AAA |
| `--ink-soft` — teks sekunder | 6,37 | 6,56 | 6,62 | 6,76 | 7,14 | 4,5 | AA |
| `--gold-deep` — eyebrow, harga, tautan | 6,42 | 6,62 | 6,68 | 6,83 | 7,20 | 4,5 | AA |
| `--danger` — pesan galat | 6,37 | 6,57 | 6,63 | 6,77 | 7,14 | 4,5 | AA |
| `--gold-mid` — judul ≥24px saja | 4,25 | 4,39 | 4,43 | 4,52 | 4,77 | **3,0** | lulus |

Pasangan yang tidak bergantung pada kaca:

| Pasangan | Rasio | Syarat | |
|---|---|---|---|
| Teks putih di tombol emas | 7,64 | 4,5 | AAA |
| Teks putih di tombol saat hover | 9,95 | 4,5 | AAA |
| Teks putih di tombol hapus | 7,57 | 4,5 | AAA |
| Inisial di kotak gambar gagal | 6,71 | 3,0 | lulus |
| `--ink-soft` di krem polos | 7,32 | 4,5 | AAA |

**Perhatikan baris `--gold-mid`.** Di permukaan kaca angkanya turun ke 4,25 — **di bawah 4,5**. Itu masih benar karena §2.1 sudah membatasinya untuk judul ≥24px, yang syaratnya 3,0. Tapi artinya batasan itu sekarang **mengikat secara teknis, bukan sekadar kerapian**: memakai `--gold-mid` untuk teks kecil di atas kaca akan gagal AA. Untuk teks apa pun di bawah 24px, pakai `--gold-deep`.

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
--r-sm:10px;   /* kotak kecil, tombol menu, kotak isian */
--r-md:16px;   /* kartu katalog & portfolio */
--r-lg:24px;   /* panel kaca */
--r-full:999px;/* tombol — SEMUA tombol, termasuk di panel admin */
```

Sudutnya **tidak seragam** — panel kaca lebih membulat daripada kartu, dan tombol berbentuk kapsul penuh. Keseragaman sudut di semua elemen adalah salah satu penanda tampilan generik.

Ketiga angka pertama naik 2–4px pada revisi 12 Agustus. Alasannya bukan selera: sudut yang lebih membulat membuat tepi sorot `--glass-highlight` terbaca melengkung mengelilingi panel, dan itu yang memberi kesan lembaran kaca. Pada sudut 12px, sorot yang sama terlihat seperti garis lurus yang terpotong.

---

## 5. Glassmorphism — empat batasan mengikat

Spec §7 menetapkan empat batasan teknis. Berikut penerapannya.

> **Revisi 12 Agustus 2026.** Sebelumnya kaca dibatasi tiga panel per layar dan hanya dipakai di halaman publik. Sekarang kaca adalah bahasa visual **seluruh aplikasi** — termasuk panel admin, halaman masuk, dan halaman error. Yang menahan biayanya bukan lagi jumlah panelnya, tapi **besar blur-nya** (§5.1). Alasan perubahannya ada di §5.5.
>
> Ini membatalkan catatan lama di berkas panel admin yang menyebut panel "sengaja tidak memakai gaya kaca". Yang tetap berlaku dari catatan itu: **panel admin tidak ikut ruang kosong lega halaman publik.** Admin mengisi data berjam-jam di sana; kepadatan barisnya tetap rapat, hanya permukaannya yang berubah.

### 5.1 Lima tingkat kaca, dibedakan menurut peran

Satu opasitas untuk semua permukaan membuat tumpukan panel terlihat seperti satu bidang datar yang kotor. Permukaan yang saling menumpuk harus bisa dibedakan mata.

| Tingkat | Opasitas | Blur | Dipakai untuk |
|---|---|---|---|
| `.glass-veil` | 0,55 | 18px | Bar navigasi, header panel, footer |
| `.glass-card` | 0,52 | **12px** | Kartu dalam grid — katalog, portfolio, anak usaha, kotak panel admin |
| `.glass-panel` | 0,62 | 22px | Permukaan utama — hero, kontak, tentang, kotak masuk |
| `.glass-raised` | 0,80 | 28px | Yang menumpuk di atas konten lain — dialog, dropdown, sheet |
| `.glass-field` | 0,55 | — | Kotak isian. Bayangan ke **dalam**, bukan melayang |

**Blur kartu grid sengaja lebih kecil** — 12px, dan turun lagi ke **8px di bawah 768px**. Di situlah biaya sebenarnya: dengan 20 kartu di layar, blur 22px terasa saat digulir di HP kelas bawah. Perbedaan 12px vs 22px nyaris tak terlihat pada kotak sekecil kartu, tapi bebannya jauh berbeda.

Semua nilai saturasi dinaikkan ke `1.5` — tanpa itu, warna gradasi yang lewat di belakang kaca terlihat pudar setelah di-blur.

### 5.2 Cadangan wajib

Setiap permukaan kaca **wajib** menuliskan warna latar padat lebih dulu, baru menambahkan blur di dalam `@supports`. Tanpa ini, browser lama merender panel transparan penuh dan teksnya tidak terbaca.

```css
.glass-panel {
  background: var(--glass-fallback);     /* cadangan — ditulis LEBIH DULU */
  border: 1px solid var(--line-glass);
  box-shadow: var(--glass-shadow), var(--glass-highlight);
}

@supports (backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px)) {
  .glass-panel {
    background: rgba(255,255,255,.62);
    backdrop-filter: blur(22px) saturate(1.5);
    -webkit-backdrop-filter: blur(22px) saturate(1.5);
  }
}
```

Nilai kaca:

```css
--glass-panel-bg:   rgba(255,255,255,.62);
--glass-card-bg:    rgba(255,255,255,.52);
--glass-veil-bg:    rgba(255,255,255,.55);
--glass-panel-blur: 22px;
--glass-card-blur:  12px;   /* 8px di bawah 768px */
--glass-veil-blur:  18px;
--glass-saturate:   1.5;
--glass-fallback:   #FDFBF6;
--glass-shadow:     0 10px 34px rgba(90,72,40,.10), 0 2px 8px rgba(90,72,40,.05);
--glass-highlight:  inset 0 1px 0 rgba(255,255,255,.75);
```

Opasitas boleh disetel antara **0,42–0,82** — seluruh rentang itu sudah diuji dan lolos AA di semua titik gradasi (§2.2).

**Bayangan kaca punya dua bagian:** bayangan jatuh yang lembut, **dan** garis sorot 1px di tepi atas (`--glass-highlight`). Garis sorot itu yang membuat permukaannya terbaca sebagai lembaran kaca, bukan kotak transparan. Jangan menghilangkannya.

### 5.3 Kontras diuji terhadap seluruh gradasi

Sudah dikerjakan — lihat §2.2. **Setiap warna teks baru wajib diuji terhadap seluruh titik gradasi pada tingkat kaca TERTIPIS yang akan memuatnya** (0,42), bukan terhadap putih. Menguji di atas putih saja akan meloloskan warna yang gagal di lapangan.

### 5.4 Mobile dirancang sendiri

Lihat §7. Mobile bukan hasil pengecilan desktop. Khusus kaca: blur kartu turun ke 8px di bawah 768px (§5.1).

### 5.5 Latar halaman wajib bergradasi

Kaca perlu sesuatu untuk dibiaskan. **Di atas latar putih polos, `backdrop-filter` tidak menghasilkan apa pun** — panelnya hanya terlihat seperti kotak abu-abu, dan seluruh efeknya sia-sia sambil tetap membayar biaya render.

Karena itu `<body>` membawa `--page-aurora`: empat titik gradasi di sudut, dipaku ke viewport dengan `background-attachment: fixed`. Kalau ikut menggulir, warna di belakang tiap panel berubah terus dan efeknya berkedip di layar panjang.

Konsekuensinya, **section tidak boleh punya latar padat sendiri.** Yang dulu `bg-white` sekarang transparan, dan panel kaca di dalamnyalah yang jadi permukaan bacanya. Satu blok padat di tengah halaman memotong aurora dan membuat temanya terbaca setengah jadi — itu juga alasan footer tidak lagi `bg-ink`.

### 5.6 Menghormati setelan sistem

Sebagian pengguna mematikan efek tembus pandang di tingkat OS karena membuat teks sulit dibaca. Di bawah `prefers-reduced-transparency: reduce`, **seluruh** permukaan jadi padat `--glass-fallback` dan blur dimatikan. Tata letaknya tidak berubah — hanya permukaannya.

---

## 6. Komponen

### Tombol

| Jenis | Gaya |
|---|---|
| Utama | Latar `--gold-deep`, teks putih, `--r-full`, padding `14px 28px`. Hover → `--gold-hover` |
| Sekunder | Transparan, teks `--gold-deep`, garis 1px `--gold`, `--r-full`. Hover → latar `--gold-deep`, teks putih |
| Garis (panel admin) | `.glass-field` + garis `--line-strong`, teks `--ink`, `--r-full`. Hover → latar `--accent` |
| Navigasi WA | Sama seperti utama, padding lebih kecil `9px 18px` |
| Hapus | Latar `--danger`, teks putih, `--r-full` |

Semua tombol dan tautan wajib punya **target sentuh minimal 44×44px** di mobile.

**Semua tombol berbentuk kapsul**, termasuk di panel admin. Tombol bersudut `rounded-md` adalah bawaan shadcn/ui yang dibiarkan apa adanya — §10 melarangnya.

### Kartu katalog

```
[gambar 4:3]
[kategori]          10,5px HURUF BESAR, --gold-deep
[nama]              Fraunces 600 17px
[deskripsi]         13,5px --ink-soft  (disembunyikan di mobile)
[harga] [tombol]    baris terpisah di mobile
```

Permukaan `.glass-card` (§5.1), sudut `--r-md`. Hover: naik 2px + bayangan `--glass-shadow`.

**Gambar gagal dimuat** → kotak `#F7F1E4` berisi inisial nama item, Fraunces 600 26px warna `--gold-deep` (spec §10). Bukan ikon rusak.

**Harga** → keterangan ("mulai dari") ditaruh di baris kecil **di atas** angkanya, bukan disambung sebaris — supaya angkanya tetap yang paling menonjol.

### Panel kaca

Sudut `--r-lg`, padding `--s6` (desktop) / `--s5 --s4` (mobile), garis `--line-glass`, bayangan `--glass-shadow` **beserta** sorot tepi `--glass-highlight` (§5.2).

### Kotak isian

`.glass-field` (§5.1), sudut `--r-sm`. Bedanya dari kartu: bayangannya mengarah ke **dalam**, sehingga permukaan yang bisa diketik terasa cekung di antara kartu yang melayang. Saat difokus, opasitasnya naik ke 0,78 — bukan berganti warna garis saja.

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
- **Kaca di atas latar putih polos** — tidak ada yang dibiaskan, biayanya tetap dibayar (§5.5)
- **Satu opasitas kaca untuk semua permukaan** — tumpukan panel jadi terlihat datar (§5.1)
- **Blok padat gelap** di halaman bertema kaca — memotong aurora dan membuat temanya terbaca setengah jadi

---

## 11. Untuk Fase 3

Token di §2, §3, dan §4 dituangkan ke `resources/css/app.css` sebagai CSS custom properties dengan nama yang sama persis, lalu dipetakan ke Tailwind lewat `@theme`.

Yang perlu diingat saat menulis kode:

1. **Jangan** menaruh `dark:` di komponen baru — project ini bertema terang saja. Baris `@custom-variant dark` di `app.css` wajib dipertahankan (alasannya ada di komentar berkas itu).
2. Cadangan permukaan kaca ditulis **sebelum** blok `@supports`, selalu.
3. Warna teks emas selalu `--gold-deep`, tidak pernah `--gold`. Di atas kaca, `--gold-mid` pun hanya untuk judul ≥24px — lihat peringatan di §2.2.
4. Pilih tingkat kaca menurut **peran** permukaannya (§5.1), jangan memakai `.glass-panel` untuk semuanya.
5. Section **tidak** membawa latar padat sendiri — aurora di `<body>` yang jadi latarnya (§5.5).
6. Mobile dikerjakan sebagai pass tersendiri, bukan hasil pengecilan.

### Menempelkan kaca ke komponen shadcn/ui

Blur untuk komponen shadcn/ui dipasang di `app.css` lewat selector `[data-slot='…']`, **bukan** dengan menyunting satu per satu berkas komponennya. Warna permukaannya sudah tembus pandang dari token di `:root`.

Alasannya: komponen di `resources/js/components/ui/` disalin dari upstream dan sesekali perlu disalin ulang. Kalau blur ditulis di dalam berkasnya, setiap penyalinan ulang menghapus temanya diam-diam. Dengan cara ini, komponen boleh ditimpa kapan saja dan tetap ikut tema.

Pengecualiannya yang disengaja: **tooltip tetap padat emas.** Kotaknya terlalu kecil dan terlalu sebentar tampil untuk dibaca kalau tembus pandang.
