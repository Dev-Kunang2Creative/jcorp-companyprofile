# Deploy ke Hostinger — Shared Hosting dengan SSH

Panduan ini disesuaikan dengan situasi Anda:

- Paket **shared hosting** (Premium / Business)
- **Ada akses SSH** — jadi tidak perlu unggah zip sama sekali
- Database **mulai bersih**, konten diisi lewat panel admin

Repo GitHub bersifat publik, jadi bisa di-clone langsung di server tanpa mengatur kunci SSH GitHub.

---

## Masalah yang harus dipahami dulu

Laravel menaruh berkas yang boleh diakses publik di folder `public/`. Sisanya — termasuk `.env` yang memuat **password database** dan **APP_KEY** — sengaja berada di luar itu.

Hostinger menyajikan `public_html/` sebagai akar website. Kalau seluruh project ditaruh di situ apa adanya, siapa pun bisa membuka:

```
namadomain.com/.env          <- password database bocor
namadomain.com/storage/logs  <- isi log server
```

Karena itu susunannya dipisah: **project di luar `public_html`, hanya isi `public/` yang di dalamnya.**

---

## Sebelum mulai

Di hPanel, siapkan tiga hal:

**1. PHP 8.3 atau lebih baru** — Advanced → PHP Configuration.
Pastikan ekstensi ini aktif: `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `gd`, `intl`, `tokenizer`, `ctype`, `dom`, `zip`.

**2. Database MySQL** — Databases → Create new database. Catat empat hal ini:

```
Nama database : uXXXXXXXX_jcorp
Pengguna      : uXXXXXXXX_jcorp
Password      : (yang Anda buat)
Host          : localhost
```

Hostinger menambahkan awalan `uXXXXXXXX_` otomatis — pakai nama lengkapnya.

**3. SSH** — Advanced → SSH Access, nyalakan. Catat host, port, dan username.

---

## Langkah 1 — Masuk lewat SSH

Dari terminal komputer Anda:

```bash
ssh -p PORT uXXXXXXXX@HOST
```

Host dan port ada di halaman SSH Access. Setelah masuk, periksa dulu:

```bash
php -v                    # harus 8.3+
composer --version        # kalau tidak ada, lihat catatan di bawah
pwd                       # biasanya /home/uXXXXXXXX
```

**Kalau `php -v` menunjukkan versi lama** (7.x atau 8.0), PHP di SSH kadang berbeda dari yang dipakai website. Coba `php8.3 -v`. Kalau ada, pakai `php8.3` menggantikan `php` di seluruh perintah berikutnya.

**Kalau Composer tidak ada:**

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar ~/composer
```

Lalu pakai `php ~/composer` menggantikan `composer`.

---

## Langkah 2 — Clone project

```bash
cd ~
git clone https://github.com/Dev-Kunang2Creative/jcorp-companyprofile.git jcorp
cd jcorp
```

Folder `jcorp` sengaja dibuat di `~` (yaitu `/home/uXXXXXXXX/`), **satu tingkat di atas** `public_html` — itu yang membuat `.env` tidak bisa diakses dari luar.

---

## Langkah 3 — Pasang dependensi

```bash
composer install --no-dev --optimize-autoloader
```

`--no-dev` melewatkan paket pengujian yang tidak dipakai di server — menghemat sekitar 85 MB.

**Node tidak perlu dipasang.** Folder `public/build/` sudah ikut di repo, jadi aset frontend sudah jadi.

---

## Langkah 4 — Buat `.env`

```bash
cp .env.example .env
nano .env
```

Ubah bagian ini:

```
APP_NAME="J Corp"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://namadomain.com

APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=uXXXXXXXX_jcorp
DB_USERNAME=uXXXXXXXX_jcorp
DB_PASSWORD=password-database-anda

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log

PHP_BINARY=
```

Simpan dengan `Ctrl+O`, `Enter`, lalu `Ctrl+X`.

**Tiga kesalahan yang akibatnya fatal:**

| Salah | Akibat |
|---|---|
| `APP_DEBUG=true` | Halaman error menampilkan isi `.env` ke pengunjung — password bocor |
| `APP_ENV=local` | Pengoptimalan produksi tidak aktif |
| `APP_URL` masih `localhost` | Tautan dan gambar mengarah ke alamat salah |

`PHP_BINARY` dikosongkan — itu hanya untuk Laragon di Windows.

---

## Langkah 5 — Kunci aplikasi dan database

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

`--force` wajib di produksi; tanpa itu artisan menolak jalan karena takut merusak data.

Yang ikut jalan hanya dua seeder, keduanya aman di server:

| Seeder | Isinya |
|---|---|
| `BusinessSeeder` | Enam entitas (induk + lima anak usaha), tanpa teks apa pun |
| `ClientContentSeeder` | **Materi asli dari client** — profil, kontak, produk, logo, warna aksen |

`SampleContentSeeder` **tidak** ikut, dan memang tidak boleh: isinya teks karangan beserta nomor telepon palsu. Jangan menjalankannya di server yang sudah tayang.

Sejak 23 Agustus 2026 keenam entitas sudah punya materi asli, jadi seluruhnya langsung terbit setelah perintah di atas.

---

## Langkah 6 — Akun admin

```bash
php artisan jcorp:make-admin
```

Interaktif: nama, email, peran, anak usaha. Password diketik tersembunyi.

**Pakai password baru**, jangan yang dipakai di localhost.

Buat minimal satu **super_admin** dulu. Admin per anak usaha bisa menyusul.

---

## Langkah 7 — Storage link dan izin folder

```bash
php artisan storage:link
chmod -R 755 storage bootstrap/cache
```

`storage:link` membuat jalan supaya foto yang diunggah admin bisa dilihat pengunjung. Tanpa itu, semua gambar unggahan tidak muncul.

---

## Langkah 8 — Sambungkan ke `public_html`

Ini bagian yang menentukan. Isi `public/` harus berada di `public_html/`, sementara sisanya tetap di luar.

**Kosongkan dulu `public_html`** kalau isinya masih halaman bawaan Hostinger:

```bash
rm -rf ~/public_html/*
rm -f ~/public_html/.htaccess
```

> Periksa dulu isinya dengan `ls -la ~/public_html` sebelum menghapus. Kalau ada website lain di domain ini, **jangan** dijalankan.

**Lalu salin isi `public/`:**

```bash
cp -r ~/jcorp/public/. ~/public_html/
```

Titik setelah `public/` penting — artinya "isi folder ini", bukan foldernya.

**Perbaiki symlink storage**, karena yang tersalin menunjuk ke jalur lama:

```bash
rm -f ~/public_html/storage
ln -s ~/jcorp/storage/app/public ~/public_html/storage
```

---

## Langkah 9 — Arahkan `index.php`

```bash
nano ~/public_html/index.php
```

Cari baris yang memuat `__DIR__.'/../'`, ubah jadi `__DIR__.'/../jcorp/'`:

```php
// SEBELUM
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// SESUDAH
require __DIR__.'/../jcorp/vendor/autoload.php';
$app = require_once __DIR__.'/../jcorp/bootstrap/app.php';
```

Kalau ada baris `maintenance` yang juga memuat `__DIR__.'/../'`, ubah juga.

Susunan akhirnya:

```
/home/uXXXXXXXX/
├── jcorp/              <- project, TIDAK bisa diakses publik
│   ├── app/
│   ├── vendor/
│   ├── storage/
│   ├── public/         (dibiarkan, sudah disalin isinya)
│   └── .env            <- AMAN di sini
└── public_html/        <- akar website
    ├── index.php       (diarahkan ke ../jcorp)
    ├── .htaccess
    ├── build/
    ├── images/
    └── storage -> ~/jcorp/storage/app/public
```

---

## Langkah 10 — Optimalkan

```bash
cd ~/jcorp
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ketiganya mempercepat website. **Ingat:** setelah ini, perubahan `.env` tidak terbaca sampai `php artisan config:clear` dijalankan.

---

## Langkah 11 — Periksa

| Alamat | Harus |
|---|---|
| `namadomain.com` | Halaman induk J Corp |
| `namadomain.com/jcorp-panel` | Halaman login |
| `namadomain.com/sweetness-things` | **404** — belum diterbitkan, itu benar |
| **`namadomain.com/.env`** | **403 atau 404** |

**Yang terakhir wajib diperiksa.** Kalau isi `.env` terlihat, susunannya salah — hentikan, perbaiki, lalu **ganti password database** karena sudah terlanjur terlihat.

---

## Langkah 12 — Isi konten lewat panel

Masuk ke `namadomain.com/jcorp-panel` dengan akun yang dibuat di Langkah 6.

Anak usaha yang materinya sudah masuk lewat `ClientContentSeeder` **tidak perlu diisi ulang di sini** — profil, kontak, dan produknya sudah terisi dan halamannya sudah terbit. Yang tersisa biasanya hanya menambahkan foto produk.

Untuk yang materinya belum masuk, urutan yang masuk akal:

1. **Info Kontak** — nomor WhatsApp asli, Instagram, alamat, jam buka
2. **Katalog** — item beserta foto
3. **Kelola Anak Usaha** → nyalakan **portfolio** untuk yang memerlukannya (Nail's by Me, ngelash.id)
4. **Kelola Anak Usaha** → **Terbitkan** setelah kontennya siap

> Materi yang masuk lewat seeder tetap bisa disunting lewat panel, dan suntingannya **tidak akan tertimpa** kalau seeder dijalankan lagi — seeder hanya menyentuh kolom yang masih kosong atau masih berisi teks contoh.

Anak usaha yang belum diterbitkan mengembalikan 404 dan tidak muncul di halaman induk — jadi bisa disiapkan tanpa terburu-buru.

---

## Kalau muncul error 500

`APP_DEBUG` mati, jadi pesannya tidak muncul di layar. Lihat:

```bash
tail -50 ~/jcorp/storage/logs/laravel.log
```

| Gejala | Sebab |
|---|---|
| **Halaman putih kosong, tapi judul tab muncul** | **Aset belum disalin setelah `git pull`** — lihat bagian pembaruan di bawah. Ini penyebab paling sering, dan paling membingungkan karena statusnya tetap 200 |
| 500 di semua halaman | Izin `storage/` belum 755, atau `APP_KEY` kosong |
| Halaman tampil tanpa gaya | Isi `public/build/` belum tersalin ke `public_html/build/` |
| Gambar unggahan tidak muncul | Symlink `storage` salah arah — ulangi Langkah 8 bagian akhir |
| "could not find driver" | `pdo_mysql` belum aktif di PHP Configuration |
| "Access denied for user" | Kredensial database di `.env` salah, atau lupa awalan `uXXXXXXXX_` |

**Halaman putih tidak meninggalkan jejak di log server** — kegagalannya terjadi di browser, bukan di PHP. Jadi `laravel.log` akan bersih walaupun websitenya tidak tampil. Periksa lewat Console browser (F12) atau perintah `curl` di bagian pembaruan.

---

## Kalau ada pembaruan nanti

**Jalankan seluruh rangkaian ini, jangan sebagian.** Melewatkan satu baris — terutama penyalinan aset — membuat halaman tampil kosong tanpa pesan error apa pun.

```bash
cd ~/domains/NAMA-DOMAIN/jcorp

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Materi client yang baru masuk. Aman diulang: kolom yang sudah
# disunting admin lewat panel tidak ditimpa.
php artisan db:seed --class=ClientContentSeeder --force

# WAJIB, bukan opsional. Lihat penjelasan di bawah.
cp -r public/build/. ../public_html/build/

# Logo dan gambar. Sama wajibnya, dan penyebabnya sama:
# public_html/ adalah SALINAN, bukan symlink — berkas baru di
# public/images/ tidak sampai ke sana dengan sendirinya.
#
# Gejalanya berbeda dari aset build: halaman tetap tampil, hanya
# logonya yang jadi kotak rusak. Itu justru lebih mudah terlewat.
cp -r public/images/. ../public_html/images/

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setelah itu muat ulang browser dengan **Ctrl+Shift+R** — refresh biasa bisa memakai cache lama.

### Kenapa penyalinan aset wajib, bukan "kalau berubah"

Nama berkas hasil build memuat hash dari isinya, jadi berubah setiap kali ada perubahan CSS atau komponen. `manifest.json` yang ikut `git pull` menunjuk ke nama **baru**, sementara `public_html/build/` masih berisi berkas **lama**.

Akibatnya halaman meminta berkas yang tidak ada:

```
GET /build/assets/app-D9DOzLlh.js   →  404
```

Yang terlihat di browser: **halaman putih kosong**, tanpa pesan error. Judul tab tetap muncul karena HTML-nya berhasil dimuat — yang gagal cuma JavaScript-nya.

Ini pernah terjadi. Gejalanya membingungkan karena tidak ada yang tampak salah dari sisi server: status 200, log bersih, `.env` benar.

**Cara memastikan berhasil**, tanpa menebak dari tampilan:

```bash
curl -s https://NAMA-DOMAIN | grep -oE 'assets/app-[A-Za-z0-9_-]+\.js'
curl -s -o /dev/null -w "%{http_code}\n" https://NAMA-DOMAIN/build/assets/NAMA-BERKAS-DARI-ATAS
```

Harus `200`. Kalau `404`, penyalinan asetnya terlewat.

### Sebelum push dari komputer

Jalankan `npm run build` kalau ada yang menyentuh `resources/`, lalu commit hasilnya bersama perubahan kodenya. Kalau lupa, kebalikannya yang terjadi — server memakai aset lama sementara kodenya sudah berubah, dan juga tidak ada yang memperingatkan.

---

## Catatan keamanan

Setelah website berjalan, periksa sekali lagi:

- `namadomain.com/.env` → tidak bisa dibuka
- `namadomain.com/storage/logs/laravel.log` → tidak bisa dibuka
- `namadomain.com/vendor/` → tidak bisa dibuka

Ketiganya harus 403 atau 404. Kalau salah satu bisa dibuka, ada yang salah pada susunan folder.

Alamat panel `/jcorp-panel` sengaja tidak ditautkan dari mana pun, tapi itu **penyamaran, bukan kunci**. Yang melindungi adalah password yang kuat, pembatasan percobaan login, dan pengecekan kepemilikan di setiap aksi.
