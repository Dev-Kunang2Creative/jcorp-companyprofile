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
php artisan db:seed --class=BusinessSeeder --force
```

`--force` wajib di produksi; tanpa itu artisan menolak jalan karena takut merusak data.

**`BusinessSeeder` saja**, bukan `SampleContentSeeder` — sesuai pilihan Anda mulai bersih. Yang dimasukkan hanya enam entitas (J Corp + lima anak usaha) tanpa isi karangan. Kelima anak usaha statusnya belum terbit.

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

Urutan yang masuk akal:

1. **Info Kontak** — nomor WhatsApp asli, Instagram, alamat, jam buka
2. **Katalog** — item beserta foto
3. **Kelola Anak Usaha** → nyalakan **portfolio** untuk yang memerlukannya (Nail's by Me, ngelash.id)
4. **Kelola Anak Usaha** → **Terbitkan** setelah kontennya siap

Anak usaha yang belum diterbitkan mengembalikan 404 dan tidak muncul di halaman induk — jadi bisa disiapkan tanpa terburu-buru.

---

## Kalau muncul error 500

`APP_DEBUG` mati, jadi pesannya tidak muncul di layar. Lihat:

```bash
tail -50 ~/jcorp/storage/logs/laravel.log
```

| Gejala | Sebab |
|---|---|
| 500 di semua halaman | Izin `storage/` belum 755, atau `APP_KEY` kosong |
| Halaman tampil tanpa gaya | Isi `public/build/` belum tersalin ke `public_html/build/` |
| Gambar unggahan tidak muncul | Symlink `storage` salah arah — ulangi Langkah 8 bagian akhir |
| "could not find driver" | `pdo_mysql` belum aktif di PHP Configuration |
| "Access denied for user" | Kredensial database di `.env` salah, atau lupa awalan `uXXXXXXXX_` |

---

## Kalau ada pembaruan nanti

Karena project di-clone dari git, pembaruannya singkat:

```bash
cd ~/jcorp
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Kalau public/build berubah, salin ulang asetnya
cp -r ~/jcorp/public/build/. ~/public_html/build/

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Sebelum push dari komputer**, jalankan `npm run build` kalau ada yang menyentuh `resources/`. Kalau lupa, server memakai aset lama sementara kodenya sudah berubah — dan tidak ada yang memperingatkan.

---

## Catatan keamanan

Setelah website berjalan, periksa sekali lagi:

- `namadomain.com/.env` → tidak bisa dibuka
- `namadomain.com/storage/logs/laravel.log` → tidak bisa dibuka
- `namadomain.com/vendor/` → tidak bisa dibuka

Ketiganya harus 403 atau 404. Kalau salah satu bisa dibuka, ada yang salah pada susunan folder.

Alamat panel `/jcorp-panel` sengaja tidak ditautkan dari mana pun, tapi itu **penyamaran, bukan kunci**. Yang melindungi adalah password yang kuat, pembatasan percobaan login, dan pengecekan kepemilikan di setiap aksi.
