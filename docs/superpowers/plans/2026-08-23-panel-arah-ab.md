# Rencana — Panel Admin Ikut Arah A+B

**Tanggal:** 23 Agustus 2026
**Acuan:** [DESIGN_SYSTEM.md §0–§6](../../../DESIGN_SYSTEM.md)
**Keputusan user:** rapikan **permukaan saja** (tipografi tetap), halaman auth **ikut**

Memindahkan panel admin dan halaman auth dari tema kaca+krem ke permukaan putih bergaris rambut — tanpa mengubah tata letak maupun tipografinya.

---

## Kenapa tipografi TIDAK ikut

Ini keputusan sadar, bukan pekerjaan yang ditunda.

**Instrument Serif hanya punya berat 400.** Panel butuh tebal untuk membedakan judul kolom tabel dari isinya; tanpa itu, tabel katalog berisi 15 layanan jadi satu blok abu-abu yang sulit dipindai. Fraunces punya 400/600/700.

**Inter Tight lebih rapat dari Jost.** Rapat bagus untuk judul besar di halaman publik, melelahkan untuk tabel padat yang dibaca berjam-jam.

Halaman publik dan panel memang untuk orang yang berbeda — dan tipografi adalah tempat perbedaan itu paling beralasan.

---

## Cakupan

**Yang ikut:** panel admin (dashboard, katalog, portfolio, profil, kelola akun, kelola anak usaha), halaman settings, dan empat halaman auth.

**Yang tidak berubah:** tata letak, urutan menu, letak tombol, ukuran huruf, jarak antar baris. Anda tidak perlu belajar ulang apa pun.

---

## 1. Token — sebagian besar pekerjaan selesai di sini

Komponen shadcn/ui memakai `--card`, `--border`, `--muted` dan seterusnya — bukan warna langsung. Mengubah token berarti seluruh komponen ikut tanpa disentuh satu per satu.

| Token | Sekarang | Jadi |
|---|---|---|
| `--background` | `#FDFBF6` krem | `#FFFFFF` |
| `--card` | putih 62% tembus | `#FAF9F7` padat |
| `--popover` | putih 82% tembus | `#FFFFFF` padat |
| `--secondary` | putih 55% tembus | `#FAF9F7` |
| `--muted` | krem 62% tembus | `#F4F2EE` |
| `--accent` | emas muda 55% | `#F4F2EE` |
| `--foreground` | `#2E2A24` | `#1A1A1A` |
| `--muted-foreground` | `#5B5348` | `#57534B` |
| `--primary` | `#6B4F0F` emas | `#7C5F33` kuningan |
| `--border` | kecoklatan 18% | `rgb(26 26 26 / .12)` |
| `--radius` | `0.875rem` (14px) | `0.25rem` (4px) |
| `--sidebar` | putih 50% tembus | `#FAF9F7` |

Kontras sudah diukur — semuanya lolos AA di latar putih maupun kartu:

| Warna | di putih | di `--card` |
|---|---|---|
| `#1A1A1A` teks | 17,40 | 16,54 |
| `#57534B` sekunder | 7,65 | 7,27 |
| `#7C5F33` kuningan | 5,93 | 5,64 |
| `#9B2C1F` bahaya | 7,57 | 7,20 |

## 2. Kelas kaca jadi permukaan padat

Empat kelas (`.glass-panel`, `.glass-card`, `.glass-veil`, `.glass-raised`) diubah definisinya di CSS — **bukan diganti namanya di 13 tempat**. Namanya jadi kurang tepat, tapi mengganti nama berarti menyentuh 7 berkas tanpa manfaat yang sepadan; catatan di CSS menjelaskannya.

`.glass-field` juga: bayangan ke dalam diganti garis rambut. Ini menyentuh 7 komponen sekaligus (input, select, checkbox, button, sidebar, price-input) tanpa satu pun berkas TSX disunting.

**Blur `data-slot` dihapus.** Di atas latar putih, `backdrop-filter` tidak membiaskan apa pun — efeknya nol sambil tetap membayar biaya render setiap kali halaman digulir.

## 3. Hook `use-legacy-surface` dihapus

Dibuat kemarin supaya panel tetap mendapat krem sementara halaman publik sudah putih. Begitu panel ikut pindah, ia tidak punya alasan untuk ada.

Dihapus dari `app-layout.tsx` dan `auth-layout.tsx`, beserta berkas hook dan blok `body.surface-legacy` di CSS.

**Tapi `--font-legacy-*` DIPERTAHANKAN** — itu yang menjaga panel tetap ber-Fraunces. Enam `font-legacy-display` yang dipasang kemarin tetap dipakai.

## 4. Dua belas warna langsung

| Berkas | Perubahan |
|---|---|
| `panel/dashboard.tsx` | `text-gold-deep` → `text-brass`, kotak `border-gold/40 bg-accent` → garis rambut |
| `panel/businesses/index.tsx` | `text-gold-deep` → `text-brass` |
| `panel/users/index.tsx` | Lencana status: emas → kuningan |
| `auth/login.tsx`, `forgot-password.tsx` | `text-gold-deep` → `text-brass` |
| `auth/invitation.tsx` | `bg-cream-warm border-line` → `bg-wash border-hair` |
| 3 layout auth | Logo `text-gold-deep` → `text-brass` |

## 5. Test

Panel sudah punya 60+ test yang menguji perilaku, bukan tampilan — semuanya harus tetap lulus tanpa disunting. Itu justru buktinya bahwa perubahan ini murni permukaan.

Yang perlu diperiksa lewat HTTP: keenam halaman panel, empat halaman auth, dan halaman publik (memastikan tidak ada yang bocor).

## 6. Pemeriksaan

`php artisan test` · Pint · PHPStan · `tsc` · ESLint · Prettier · `npm run build`, lalu buka setiap halaman dan **lihat dengan mata** — pelajaran dari logo Ayodya yang lolos seluruh pemeriksaan otomatis dalam keadaan terpotong.

## 7. Dokumen

- `DESIGN_SYSTEM.md` — §0 diperbarui (tidak lagi dua tema permukaan, hanya dua tipografi), §7–§14 ditandai mana yang masih berlaku
- `PROGRESS.md` — catatan 23 Agustus

---

## Risiko yang disadari

**Kelas bernama `glass-*` yang tidak lagi berkaca.** Nama yang menyesatkan adalah utang; dicatat di CSS, dan bisa diganti kapan saja dalam satu pass tersendiri.

**Empat keluarga font tetap terpasang** — konsekuensi dari keputusan mempertahankan Fraunces + Jost di panel. Sekitar 40 KB, dan itu memang harga yang dibayar untuk keterbacaan tabel.

**`prefers-reduced-transparency` jadi tidak relevan** untuk panel setelah kacanya hilang. Bloknya tetap ada karena halaman publik pun tidak lagi memakainya — akan dibersihkan saat kelas `glass-*` diganti nama.
