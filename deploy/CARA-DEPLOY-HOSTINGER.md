# Update J-Corporate di Hostinger testing

Diperbarui 28 Agustus 2026. Panduan ini menggantikan asumsi pemasangan awal/database kosong pada versi lama. **Jangan mengosongkan `public_html`, menimpa `.env`, generate APP_KEY, atau menjalankan seeder sebagai update rutin.**

Domain `lemonchiffon-crane-249931.hostingersite.com` adalah **testing**, bukan peluncuran produksi. Layout terakhir yang dilaporkan pemilik: seluruh repository di `~/domains/lemonchiffon-crane-249931.hostingersite.com/public_html`, Laravel di subfolder `public/`. Konfigurasi server sekarang perlu dicek, bukan diasumsikan masih sama.

## 1. Sebelum perubahan server

Di komputer: jalankan pengecekan yang relevan, dan `npm run build` jika mengubah frontend. `public/build` sengaja tracked; commit build bersama kode. Push dilakukan pemilik.

Di server: pilih checkout J-Corporate, bukan repository website lain. Perintah berikut hanya inspeksi:

```bash
cd "$HOME/domains/lemonchiffon-crane-249931.hostingersite.com/public_html"
pwd
git remote get-url origin
git status --short
git rev-parse HEAD
ls -ld public storage/app/public
ls -l .htaccess public/.htaccess public/index.php public/build/manifest.json
php -v
```

Jangan menampilkan isi `.env` ke chat. Bila file hilang/config berbeda/working tree kotor, tinjau dahulu. Jangan `git reset --hard` atau menghapus file untracked untuk memaksa update. Server bisa berada pada detached HEAD/shallow checkout; jangan mengasumsikan checkout lokal dan server sama.

**Buat dan verifikasi snapshot sebelum pull/migration/config change.** Gunakan [toolkit backup](backup/README.md), lalu salinan off-host. Catat SHA sebelum update. Jika toolkit belum tersedia di server, pindahkan ke checkout terpercaya privat dengan `vendor/` dan arahkan `--project` ke aplikasi aktif; jangan melewatkan backup pertama hanya karena belum ada skripnya.

## 2. Tinjau update yang akan masuk

```bash
git fetch origin main
git log --oneline HEAD..origin/main
git diff --stat HEAD..origin/main
git diff HEAD..origin/main -- database/migrations composer.json composer.lock
```

Jika tidak ada commit baru, tidak perlu memaksa deployment. Bila ada migration/konfigurasi/dependensi, baca dampaknya dan siapkan [rencana rollback](BACKUP-PEMULIHAN-ROLLBACK.md#4-rencana-rollback-saat-update-bermasalah). Sepakati maintenance sebelum perubahan yang dapat memutus layanan atau membuat schema/kode berbeda.

## 3. Terapkan update yang sudah ditinjau

Ini langkah mutasi, bukan perintah diagnosis. Jalankan hanya setelah snapshot berhasil dan perubahan memang disetujui:

```bash
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
php artisan migrate:status
```

`migrate:status` hanya memeriksa. **Hanya bila ada migration pending yang sudah ditinjau** dan target DB benar, jalankan `php artisan migrate --force`. `--force` melewati konfirmasi lingkungan, bukan menjadikan migration aman. Jangan jalankan `migrate:fresh`, `db:wipe`, atau seeder untuk update visual.

Di layout saat ini, build berada langsung di `public/build`; tidak perlu menyalin ke `../public_html/build` seperti pada panduan layout salinan lama. Jika layout server ternyata berbeda, hentikan dan cocokkan document root terlebih dahulu.

Setelah kode/konfigurasi siap:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
git log -1 --oneline
```

Jangan `key:generate`: gunakan APP_KEY yang sudah ada. Jangan `optimize:clear` untuk sekadar refresh view tanpa memahami bahwa perintah itu juga membersihkan cache aplikasi. Restart hanya worker yang memang dikelola dan diperlukan. Jika maintenance tadi diaktifkan, buka kembali sesudah smoke test yang memungkinkan dan periksa ulang setelah terbuka.

## 4. Periksa routing dan file penting

Laravel perlu disajikan dari `public/index.php`, dengan hanya direktori `public/` terbuka ke web. [Dokumentasi Laravel](https://laravel.com/framework/docs/13.x/deployment) menjelaskan document root yang dianjurkan. Jangan memindahkan `index.php` ke akar source.

Root `.htaccess` manual pernah hilang pada riwayat testing sehingga homepage 403. Jika masih menggunakan rewrite akar ke `public/`, file itu berbeda fungsi dari `public/.htaccess` dan harus tetap ada serta dibackup. Jangan otomatis menyalin aturan layout lain, atau menganggap semua 403 disebabkan Git/CSS.

Periksa storage link:

```bash
ls -ld public/storage storage/app/public
readlink public/storage
```

Jika `public/storage` **belum ada**, dan `php artisan storage:link` gagal karena PHP exec dinonaktifkan, dari root project standar bisa dibuat dengan:

```bash
ln -s ../storage/app/public public/storage
```

Jika sudah ada, jangan hapus/ganti sebelum memeriksa arah dan isi targetnya. Jangan menjalankan chmod rekursif pada seluruh project atau membuka `.env`/backup ke pengunjung untuk menyelesaikan error izin.

## 5. Pemeriksaan setelah update

```bash
curl -sS -o /dev/null -w 'Website: %{http_code}\n' 'https://lemonchiffon-crane-249931.hostingersite.com/'
curl -sS -I -o /dev/null -w 'ENV: %{http_code}\n' 'https://lemonchiffon-crane-249931.hostingersite.com/.env'
```

Homepage yang diterbitkan diharapkan 200. `.env` harus 403/404; jangan meminta body untuk ditampilkan ke terminal/chat. Periksa juga aturan akses `.git/config`, `composer.json`, log, dan folder privat. Jika ada indikasi rahasia terlayani, blok akses melalui konfigurasi hosting dahulu, lalu lakukan penanganan kredensial; jangan sekadar menghapus log.

Di browser, cek:

- Homepage dan kelima profil sesuai status terbit, termasuk Ayodya ID/EN.
- JS/CSS dari manifest tidak 404; logo, foto katalog, dan galeri muncul.
- Login panel oleh pemilik, data akun/peran, katalog, galeri, kontak, serta penerbitan sesuai keadaan sebelumnya. Jangan membuat undangan/CRUD nyata hanya demi tes tanpa persetujuan.
- Tidak ada data localhost yang dianggap otomatis ikut Git. Jika perlu memindahkan katalog manual, itu pekerjaan migrasi data tersendiri.

HTTP 200 saja bukan bukti semua fitur atau data aman. Jika gagal, gunakan [runbook rollback](BACKUP-PEMULIHAN-ROLLBACK.md), jangan reset database sebagai langkah pertama.

## Instalasi baru atau pemindahan layout

Belum ada prosedur otomatis untuk mengganti document root/release di hosting ini. Untuk domain/server **baru**, siapkan rencana terpisah: document root `public/`, database kosong baru, akun DB terbatas, `.env` privat, PHP/dependensi yang sesuai, dan storage. Generate key hanya untuk aplikasi baru yang tidak memulihkan data terenkripsi lama. Migration/seeding awal harus ditinjau sebagai inisialisasi, bukan langkah pemeliharaan situs yang sudah berisi data.

Tidak ada bagian panduan ini yang meminta menghapus seluruh `public_html`, database lama, atau backup sebelumnya.
