# Rencana — Data Asli Sweetness Things

**Tanggal:** 21 Agustus 2026
**Acuan:** [spec §3, §6, §15](../specs/2026-08-10-jcorp-company-profile-design.md) · [DESIGN_SYSTEM §5, §9, §10](../../../DESIGN_SYSTEM.md)
**Sumber data:** dua screenshot dari client (profil + informasi kontak), 21 Agustus 2026

Memasukkan data asli client untuk Sweetness Things, beserta bagian halaman baru yang menampungnya.

**J Corp dan empat anak usaha lain tidak disentuh di rencana ini** — datanya menyusul, dikerjakan terpisah.

---

## Temuan yang mengubah rancangan

Satu-satunya seeder yang mengisi konten sekarang bernama `SampleContentSeeder`, dan ada perintah `php artisan jcorp:clear-samples` yang tugasnya menghapus isinya — dikenali lewat perbandingan isi terhadap `SampleContent`.

**Kalau data asli client dimasukkan ke sana, suatu hari perintah itu akan ikut menghapusnya.** Jadi data asli dipisah ke berkas dan seeder sendiri yang tidak pernah disentuh perintah pembersih.

Ini juga alasan halaman-halaman di server terlihat kosong: server hanya menjalankan `BusinessSeeder`, yang memang sengaja tidak mengisi teks apa pun (lihat docblock-nya — keputusan sadar, bukan kelalaian).

---

## 1. Migrasi — enam kolom baru di `businesses`

`2026_08_21_000001_add_profile_fields_to_businesses_table.php`

| Kolom | Tipe | Isi untuk Sweetness |
|---|---|---|
| `vision` | `text` nullable | Satu kalimat visi |
| `mission` | `json` nullable | 5 poin misi |
| `services` | `json` nullable | Open pre-order, Custom Hampers (judul + keterangan) |
| `highlights` | `json` nullable | 4 keunggulan (judul + keterangan) |
| `whatsapp_alt` | `string` nullable | Nomor WhatsApp kedua |
| `order_note` | `string` nullable | "Pemesanan hanya lewat chat WhatsApp atau DM Instagram" |

**Kenapa `json`, bukan tabel terpisah.** Isinya daftar teks pendek yang selalu dibaca sekaligus dengan profilnya — tidak pernah dicari, disaring, atau diurutkan sendiri. Tabel terpisah menambah tiga tabel, tiga model, dan tiga policy tanpa manfaat yang sepadan. MariaDB 11.8 di server dan MySQL 8.4.3 lokal dua-duanya mendukungnya.

**Semua nullable**, jadi empat anak usaha lain tidak terpengaruh sama sekali.

**Kenapa kolom baru, bukan digabung ke `description`.** Misi lima poin dan empat keunggulan punya bentuk daftar. Dijejalkan ke satu textarea, keduanya kehilangan bentuknya dan terbaca sebagai satu blok teks panjang — terutama sulit di HP. Template spreadsheet client juga sama untuk kelima anak usaha, jadi skema ini akan langsung terpakai saat data mereka masuk.

## 2. Model `Business`

- `casts()`: `mission`, `services`, `highlights` → `'array'`
- Docblock `@property` untuk keenam kolom
- **`whatsapp_alt` dan `order_note` masuk `#[Fillable]`** — admin boleh mengubahnya lewat panel
- **`vision`, `mission`, `services`, `highlights` TIDAK fillable** — sama perlakuannya dengan `description` yang sudah ada: teks panjang yang jarang berubah, diisi lewat seeder, bukan form

## 3. Data asli — `database/seeders/ClientContent.php`

Berkas baru, sejajar `SampleContent.php` tapi isinya **asli, bukan karangan**. Docblock-nya menyatakan tegas bahwa isinya tidak boleh ikut dihapus `jcorp:clear-samples`.

Isi untuk `sweetness-things`, verbatim dari screenshot kecuali dua koreksi ejaan pada nama varian (lihat bagian katalog di bawah):

**Aturan yang dipegang: hanya yang ada di screenshot.** Tidak ada satu kalimat pun karangan. Yang tidak diberikan client tidak muncul di website.

- **description** — paragraf About us, dipecah jadi 2 paragraf di batas kalimat yang wajar ("…riuhnya hari." / "Setiap produk…"). Di screenshot batas itu tertulis tanpa spasi (`…riuhnya hari.Setiap produk…`), yang justru menandakan di situ memang batas paragrafnya
- **vision, mission (5), services (2), highlights (4)** — sesuai screenshot
- **whatsapp** `6281938020815`, **whatsapp_alt** `628813742352` — dinormalkan dari `0819-3802-0815` dan `0881-3742-352`
- **instagram** `sweetnessthings._`, **tiktok** `sweetnessthings` — tanpa `@`, sesuai bentuk yang dipakai `ContactSection`
- **address** `Semarang` — screenshot hanya menyebut kota
- **order_note** — pre-order lewat chat WA / DM Instagram
- **catalog_label** `Menu Kami`
- **tagline** → **`null`.** Tidak ada di data client. Hero berisi logo, nama, dan tombol WhatsApp saja
- **business_hours** → **`null`.** Tidak ada di data client. Baris "Jam Buka" tidak dirender

Katalog — **2 kartu**, sesuai keputusan user untuk mengikuti tulisan client apa adanya. Keduanya `price = null` dan `price_note = 'hubungi kami'` karena client belum memberikan daftar harga:

1. **Dessert Box Series** — deskripsinya menyebut keempat varian (Pistachio Kunafa, Chocolate, Oreo, Strawberry Cheese)
2. **Soft Cookies**

Ejaan dua varian dibetulkan dari sumber atas persetujuan user: `kunnaffa` → **Kunafa**, `chesse` → **Cheese**. Selain dua itu, seluruh teks produk verbatim.

Tanpa `SAMPLE_MARKER` — ini produk asli, bukan contoh.

## 4. Seeder — `ClientContentSeeder`

### Masalah yang harus diselesaikan seeder ini

Sweetness Things di database **sudah terisi data karangan** dari `SampleContentSeeder`: tagline, cerita, nomor `6281234567890`, alamat "Jl. Contoh Raya No. 12, Bekasi", jam buka "Senin–Sabtu, 09.00–18.00".

Pola `??=` yang dipakai `SampleContentSeeder` hanya mengisi kolom yang **masih kosong**. Kalau dipakai di sini, **tidak satu pun data karangan itu tergantikan** — data asli client masuk ke seeder tapi tidak pernah muncul di halaman, dan nomor palsu tetap tayang.

### Tiga perlakuan per kolom

Memakai teknik yang sudah terbukti di `ClearSamplesCommand`: **membandingkan isi** terhadap `SampleContent`.

| Kondisi kolom sekarang | Tindakan |
|---|---|
| Kosong (`null`) | Isi dengan data client |
| **Persis sama** dengan teks di `SampleContent` | **Timpa** dengan data client — itu data contoh |
| Isinya sesuatu yang lain | **Biarkan** — admin sudah menulisnya sendiri lewat panel |

Baris ketiga itu yang membuat seeder aman dijalankan berulang di server tanpa merusak suntingan admin.

Untuk **tagline** dan **business_hours** yang justru harus jadi `null`: kalau isinya masih teks karangan → dikosongkan, dan bagian itu hilang dari halaman. Kalau admin sudah menulis sendiri → dibiarkan.

### Selebihnya

- Menerbitkan Sweetness (`is_published = true`)
- Menempelkan logo yang sudah diproses di Fase 2
- **Menghapus item katalog ber-`SAMPLE_MARKER` milik Sweetness** — kalau tidak, 6 produk karangan lama akan berdampingan dengan 2 produk asli di halaman
- `updateOrCreate` untuk katalog, sehingga aman dijalankan berulang

Didaftarkan ke `DatabaseSeeder` setelah `BusinessSeeder`, jadi `php artisan db:seed` biasa langsung membawa data asli. `SampleContentSeeder` tetap terpisah dan tidak berubah.

## 5. Halaman publik

**Controller** — kirim keenam field baru; `contact` menerima `whatsapp_alt` dan `order_note`.

**Tiga komponen baru.** Urutan section di halaman:

```
Hero → Tentang → Visi & Misi → Keunggulan → Layanan → Menu Kami → Kontak
```

| Komponen | Bentuk |
|---|---|
| `vision-mission-section` | `.glass-panel`. Visi sebagai kutipan bergaris emas; misi jadi daftar bernomor |
| `highlights-section` | 4 `.glass-card` dalam grid (2 kolom mobile, 4 desktop) |
| `services-section` | 2 `.glass-card`, judul + keterangan |

**Ritmenya sengaja dibedakan.** DESIGN_SYSTEM §10 melarang "sudut, bayangan, dan jarak seragam di semua elemen tanpa hierarki", dan §5.1 melarang satu opasitas kaca untuk semua permukaan. Jadi Visi/Misi memakai panel (permukaan baca tenang untuk teks agak panjang), sedangkan Keunggulan dan Layanan memakai kartu (potongan pendek yang dibaca sekilas). Ini juga yang menjawab keluhan user sebelumnya soal tampilan yang "masih terlalu biasa aja" — halaman jadi punya irama, bukan blok-blok yang sama.

Semua section tunduk aturan section kosong (spec §3): yang datanya null tidak dirender. Empat anak usaha lain karena itu **tidak berubah tampilannya sama sekali**.

**Kontak** — baris "WhatsApp (2)" untuk nomor kedua, dan `order_note` sebagai kalimat pembuka menggantikan kalimat generik yang sekarang ("Paling cepat lewat WhatsApp…").

**Navigasi** — tautan section baru ikut masuk hanya kalau datanya ada, mengikuti pola `navLinks` yang sudah berjalan.

## 6. Panel admin

Field `whatsapp_alt` dan `order_note` ditambahkan ke form profil, plus aturan validasinya di `BusinessProfileRequest` — `whatsapp_alt` memakai regex yang sama dengan `whatsapp` (`/^62[0-9]{8,13}$/`) karena dipakai membentuk link wa.me, bukan sekadar ditampilkan.

## 7. Test

| Berkas | Isi |
|---|---|
| `tests/Feature/ClientContentSeederTest.php` (baru) | Sweetness terbit dengan visi/misi/keunggulan/layanan terisi; 2 produk asli; **kedua nomor WA lolos regex yang dipakai panel**; produk contoh lama tersapu; dijalankan dua kali tidak menggandakan; tulisan admin tidak ditimpa |
| ⇡ **yang paling penting di berkas itu** | **Data karangan benar-benar tergantikan** — jalankan `SampleContentSeeder` dulu, lalu `ClientContentSeeder`, lalu pastikan nomor `6281234567890` dan alamat "Jl. Contoh Raya" sudah tidak ada lagi. Tanpa test ini, seluruh pekerjaan bisa lolos semua pemeriksaan tapi halamannya tetap menampilkan data palsu |
| ⇡ | **tagline dan business_hours jadi `null`** setelah seeder jalan di atas data contoh — dua kolom ini tidak diisi client, jadi harus benar-benar hilang, bukan tertinggal berisi karangan |
| `tests/Feature/PublicProfileTest.php` | Empat section baru hilang saat datanya null; muncul saat terisi; `whatsapp_alt` & `order_note` ikut terkirim |
| `tests/Feature/Console/ClearSamplesTest.php` | **Penjaga terpenting: `jcorp:clear-samples` tidak menghapus data asli client** |
| `tests/Feature/Panel/…` | Dua field baru bisa disimpan; format nomor kedua divalidasi |

Test regex nomor WA ditulis eksplisit karena nomor dari client bentuknya `0819-…` dan `0881-…` — normalisasinya dikerjakan tangan, dan salah satu bisa saja meleset tanpa ketahuan sampai ada pengunjung yang menekan tombol WhatsApp.

## 8. Pemeriksaan sebelum diserahkan

`php artisan test` · Pint · PHPStan · `tsc` · ESLint · Prettier · `npm run build`

## 9. Dokumen

- `PROGRESS.md` — catatan tanggal 21 Agustus
- `deploy/CARA-DEPLOY-HOSTINGER.md` — perintah `db:seed --class=ClientContentSeeder --force` di bagian update, beserta penjelasan kenapa terpisah dari `SampleContentSeeder`

---

## Yang TIDAK dikerjakan

- **J Corp** — teks Tentang dan kontaknya menyusul (keputusan user: "untuk jcorp nanti aja")
- **Empat anak usaha lain** — datanya sudah ada di tangan user, dikerjakan terpisah
- **Commit dan push** — tidak dilakukan tanpa diminta
- **Harga produk** — belum ada dari client; tampil "hubungi kami"

## Risiko yang disadari

**Nomor WhatsApp kedua hanya 11 digit** (`0881-3742-352`), sedangkan nomor Indonesia umumnya 12–13. Bisa jadi memang begitu, bisa jadi ada digit yang terpotong saat client mengetiknya. Angkanya saya salin apa adanya, tapi **sebaiknya dikonfirmasi ke client** — nomor yang kurang satu digit menghasilkan tombol WhatsApp yang mengarah ke akun tidak dikenal. (Panjangnya masih lolos regex `62` + 8–13 digit, jadi validasi tidak akan menangkapnya.)

**Alamat hanya berisi "Semarang"** karena screenshot memang hanya menyebut kota, dan posisinya di sana sebagai penanda kota di bawah nama usaha — bukan di kolom alamat. Kalau ternyata usahanya pre-order saja dan tidak menerima kunjungan, baris "Alamat" di halaman kontak justru menyesatkan. Perlu ditinjau bersama client.

**Halaman jadi tanpa tagline.** Konsekuensi dari aturan "hanya yang ada di screenshot" — hero berisi logo, nama, dan tombol WhatsApp saja. Kalau nanti terasa terlalu lengang saat dilihat, satu kalimat pendek dari client akan menyelesaikannya.
