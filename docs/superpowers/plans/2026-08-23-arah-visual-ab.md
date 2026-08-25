# Rencana — Penerapan Arah Visual A+B

**Tanggal:** 23 Agustus 2026
**Acuan:** [DESIGN_SYSTEM.md](../../../DESIGN_SYSTEM.md) · [mockup](../../mockups/arah-visual-2026-08-22.html)
**Arah terpilih:** A+B — struktur kartu dari A, tipografi jurnal dari B, latar putih

Menerapkan arah visual baru ke seluruh halaman publik, beserta aksen warna per anak usaha dan animasi masuk saat digulir.

---

## Kenapa ini dikerjakan

Dua alasan, dan yang kedua lebih penting dari yang pertama.

**1. Website belum punya gerak sama sekali.** Tidak ada satu pun `@keyframes` atau transisi di luar hover tombol. Itu yang membuatnya terasa datar.

**2. Satu palet dessert dipakai enam usaha berbeda bidang.** Palet emas dirancang untuk Sweetness Things — butik dessert. Palet yang sama sekarang dipakai **PT. Ayodya Utama Logistic** (perusahaan logistik internasional dengan Maersk dan NYK Line sebagai partner) dan **J-Land Property**.

DESIGN_SYSTEM §2.3 sudah menuliskan aturannya sejak awal: *"Palet Sweetness (emas) hanya untuk Sweetness."* Aturan itu belum pernah dijalankan — dan sekaranglah waktunya.

---

## Cakupan

**Yang dikerjakan:** 13 berkas halaman publik.

**Yang TIDAK dikerjakan sekarang:** panel admin dan halaman auth (25 berkas). Keputusan user — hanya admin yang melihatnya, tampilannya sudah berfungsi, dan panel punya kebutuhan berbeda (kepadatan tinggi, form panjang) yang belum dirancang di mockup. Bisa menyusul kapan saja.

Konsekuensinya disadari: untuk sementara panel admin dan halaman publik akan terlihat berbeda. Itu wajar — keduanya memang untuk orang yang berbeda.

---

## 1. Token — `DESIGN_SYSTEM.md` dan `app.css`

### Warna dasar (menggantikan krem + aurora)

| Token | Nilai | Peran |
|---|---|---|
| `--color-paper` | `#FFFFFF` | Latar halaman |
| `--color-wash` | `#FAF9F7` | Permukaan kartu — putih sedikit hangat |
| `--color-ink` | `#1A1A1A` | Teks utama (17,40 di putih — AAA) |
| `--color-ink-soft` | `#57534B` | Teks sekunder (7,65 — AA) |
| `--color-brass` | `#7C5F33` | Aksen induk (5,93 — AA) |
| `--color-hair` | `rgb(26 26 26 / .12)` | Garis rambut |
| `--color-hair-soft` | `rgb(26 26 26 / .07)` | Garis kisi kartu |

**Kuningan `#8A6A3B` yang dipakai di mockup pertama GAGAL** — 4,43 di bone, di bawah syarat 4,5. Diganti `#7C5F33`.

### Aksen per anak usaha

Diambil dari logo masing-masing, versi yang lolos AA sebagai teks:

| Slug | Aksen | Rasio di `--wash` |
|---|---|---|
| `jcorp` | `#7C5F33` kuningan | 5,64 |
| `sweetness-things` | `#6B4F0F` emas | 7,26 |
| `ngelash` | `#7A5C12` emas tua | 5,93 |
| `nails-by-me` | `#2A4104` hijau tua | 10,75 |
| `ayodya-logistic` | `#9E1B21` merah | 7,58 |
| `j-land-property` | `#14488C` biru | 8,55 |

**Nama tokennya `--unit-accent`, bukan `--accent`** — `accent` sudah dipakai shadcn/ui di 14 tempat, dan menabraknya akan merusak panel admin.

### Tipografi

| Peran | Font | Alasan |
|---|---|---|
| Display | **Instrument Serif** 400 | Serif editorial bertekanan tinggi. Fraunces terlalu hangat/manis untuk induk yang menaungi logistik |
| Antarmuka | **Inter Tight** 400/500/600 | Grotesk padat. Bukan Inter biasa — versi rapatnya punya karakter lebih |

Keduanya SIL Open Font License, tersedia di Bunny Fonts (server sendiri, tanpa permintaan ke pihak ketiga).

**Fraunces dan Jost dipertahankan sebagai token** — panel admin masih memakainya, dan menghapusnya akan merusak 25 berkas yang belum tersentuh.

### Motion

```css
--ease-brand: cubic-bezier(.22, 1, .36, 1);   /* expo-out */
--dur-enter: 620ms;
--stagger: 60ms;
```

Jarak masuk **18px** — bukan 60px. Yang membuat gerakan terasa mahal bukan jaraknya, tapi perlambatan di ujungnya.

---

## 2. Aksen dari database, bukan ditanam di kode

Migrasi `2026_08_23_000001_add_accent_color_to_businesses_table.php`:

```php
$table->string('accent_color', 7)->nullable()->after('logo_path');
```

**Kenapa dari database:** admin bisa menyesuaikan warnanya sendiri kalau client mengganti identitas, tanpa menyentuh kode. Nilainya dikirim controller sebagai CSS custom property di elemen pembungkus halaman.

**Yang tidak fillable** — sama dengan `vision` dan `mission`: warna menentukan tampilan seluruh halaman, dan salah ketik di sana langsung terlihat publik. Diisi lewat seeder.

**Validasi wajib.** Nilai ini masuk ke atribut `style`, jadi harus dipastikan berbentuk hex 6 digit sebelum dipakai — kalau tidak, kolom itu jadi jalan masuk untuk menyuntikkan CSS. Ada test khusus untuk ini.

---

## 3. Komponen — 13 berkas

| Berkas | Perubahan |
|---|---|
| `app.css` | Token baru, kelas `.hairline-card`, `.chapter`, animasi masuk, `prefers-reduced-motion` |
| `app.blade.php` | Muat Instrument Serif + Inter Tight |
| `use-reveal.ts` **(baru)** | Hook `IntersectionObserver` untuk animasi masuk |
| `chapter-heading.tsx` **(baru)** | Judul bab bernomor `01 / 02 / 03` |
| `site-nav.tsx` | Kaca → putih 88% + garis rambut |
| `hero-section.tsx` | Panel kaca dibuang; judul langsung di latar, asimetris |
| `about-section.tsx` | Panel → dua kolom teks dengan judul bab |
| `vision-mission-section.tsx` | Panel → kutipan bergaris kiri + daftar misi |
| `highlights-section.tsx` | Kartu kaca → kartu garis rambut |
| `services-section.tsx` | Sama |
| `catalog-card.tsx` | Sama |
| `price-list.tsx` | Panel → tabel bergaris |
| `contact-section.tsx` | Panel → baris bergaris |
| `subsidiary-card.tsx` | Kaca → kartu garis rambut dengan pita aksen |
| `portfolio-section.tsx` | Bingkai kaca → garis rambut |
| `home.tsx` | Judul bab, statistik berhitung, footer bertipografi |
| `business.tsx` | Judul bab, footer |

---

## 4. Animasi

Satu hook `useReveal()` dipakai seluruh komponen — bukan tiap komponen membuat pengamat sendiri.

- Masuk saat **12%** elemen terlihat, dengan `rootMargin: -6%` — selesai bergerak sebelum mata sampai ke situ
- `unobserve` setelah masuk: tidak berulang saat digulir naik-turun
- Hanya `transform`, `opacity`, `filter` — tidak ada yang memaksa hitung ulang tata letak

**`prefers-reduced-motion` wajib.** Belum ditangani sama sekali di CSS produksi sekarang — dan karena rencana ini menambah animasi, itu jadi keharusan, bukan pelengkap. Sebagian orang mematikan animasi di tingkat sistem karena membuat mereka pusing atau mual.

Di layar sentuh, efek hover dimatikan dan aksen warna **selalu tampil** — tanpa itu pengguna HP tidak akan pernah melihat warna unit usahanya.

---

## 5. Test

| Berkas | Isi |
|---|---|
| `AccentColorTest.php` **(baru)** | **Nilai bukan-hex ditolak** — penjaga terpenting: kolom ini masuk ke atribut `style` |
| ⇡ | Setiap anak usaha punya aksen; halaman tanpa aksen tetap tampil (jatuh ke kuningan) |
| `ClientContentSeederTest.php` | Aksen terpasang untuk keenam entitas |
| `PublicProfileTest.php` | `accent_color` sampai ke halaman |

Sekitar 8 test baru.

---

## 6. Pemeriksaan

`php artisan test` · Pint · PHPStan · `tsc` · ESLint · Prettier · `npm run build`

Lalu **lewat HTTP sungguhan**: keenam halaman dibuka, payload diperiksa, dan tampilannya dilihat dengan mata — pelajaran dari logo Ayodya yang terpotong dan lolos semua pemeriksaan otomatis.

---

## 7. Dokumen

- `DESIGN_SYSTEM.md` — §2 warna ditulis ulang, §3 tipografi, **§5 baru: motion**, §11 aksen per unit
- `PROGRESS.md` — catatan 23 Agustus

---

## Risiko yang disadari

**Panel admin akan terlihat berbeda dari halaman publik** sampai ikut dikerjakan. Disadari dan diterima.

**Aurora dan glassmorphism hilang dari halaman publik.** Itu keputusan sadar — tapi berarti pekerjaan rekan yang merevisi glassmorphism 12 Agustus tidak lagi terpakai di halaman publik. Kelasnya tetap ada dan masih dipakai panel admin.

**Fraunces dan Jost masih dimuat** untuk panel admin. Berarti empat keluarga font terpasang sampai panel ikut dikerjakan — sekitar 40 KB tambahan. Bisa dipangkas begitu panel menyusul.

**Kontras aksen di kartu putih sudah diuji, tapi belum di setiap kombinasi.** Kalau nanti ada aksen baru ditambahkan lewat panel, tidak ada yang otomatis memeriksa kontrasnya. Dicatat di DESIGN_SYSTEM sebagai aturan tertulis.
