# Runbook backup, pemulihan, dan rollback

Diperbarui 28 Agustus 2026. Cakupan: J-Corporate di localhost dan **Hostinger testing**, tanpa cron/CI/CD atau perubahan alur aplikasi. Skrip dan batasannya: [backup/README.md](backup/README.md).

## 1. Aturan yang tidak boleh dilewati

- **Database localhost berbeda dari database Hostinger.** Katalog/foto yang dimasukkan di localhost tidak ikut `git push/pull`. Buat snapshot masing-masing; jangan menimpa salah satunya agar terlihat sama.
- **Pulihkan ke database dan folder baru dahulu.** Mengimpor SQL ke database aktif dapat menghapus/mengganti data. Tidak ada auto-restore ke schema aktif dalam toolkit.
- Pertahankan `APP_KEY` dari lingkungan yang sedang dipulihkan. Jangan `key:generate` saat update/rollback. Penggantian key bisa membuat data terenkripsi/sesi lama tidak dapat dibaca.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `migrate:refresh`, seeder, `git reset --hard`, atau penghapusan seluruh webroot sebagai cara pemulihan.
- Kredensial/backup hanya untuk pemilik yang berwenang. Folder hasil ekstraksi tetap privat. Jangan membuka SQL atau `.env` di chat, repo, atau preview publik.
- `COMPLETE` + checksum cocok berarti **integritas arsip lolos**, bukan semua fitur aplikasi telah pulih.

## 2. Sebelum membuat snapshot

1. Catat lingkungan (local/testing), URL, path project, waktu UTC/lokal, SHA dengan `git rev-parse HEAD`, dan `git status --short`. Pastikan sedang di repository J-Corporate, bukan website POS atau project lain.
2. Jalankan preflight dari [README toolkit](backup/README.md). Pastikan database dan labelnya benar. Bila cache dan `.env` berbeda, **berhenti** untuk meninjau konfigurasi; jangan otomatis menghapus cache lalu membackup database yang berbeda.
3. Pastikan ruang disk dan salinan off-host siap. Jangan hanya mengandalkan backup hosting yang belum pernah diperiksa isinya.
4. Sepakati waktu berhenti menulis. Di localhost: tutup aktivitas panel, hentikan queue/dev worker dan proses lain yang menulis. Di Hostinger yang masih dipakai: koordinasikan maintenance singkat; jika disetujui, gunakan `php artisan down` dan hentikan worker/scheduler lewat mekanisme yang memang mengelolanya. Maintenance HTTP sendiri **tidak** menghentikan worker, cron, akses SQL, atau request yang sudah berjalan. Tunggu penulisan berjalan selesai. Jangan mematikan proses yang tidak dikenal.
5. Jalankan backup dengan konfirmasi no-writes. Jangan mengubah konten/migrasi selama snapshot. Verifikasi hasil, catat path persis dan hash `COMPLETE` di catatan privat.
6. Setelah berhasil/gagal ditangani, bila tadi mengaktifkan maintenance, jalankan `php artisan up` dan pulihkan proses worker yang memang dihentikan. Pastikan website kembali normal. Backup gagal bukan alasan membiarkan layanan tidak tersedia.
7. Buat salinan off-host ke penyimpanan terenkripsi dan verifikasi ulang di sana. Jangan hapus asal sebelum salinan ini terbukti dapat dibaca.

Kebijakan awal **manual**, bukan SLA: backup sebelum setiap perubahan/deployment berisiko dan setelah sesi pengisian konten penting. Pertahankan minimal baseline terakhir yang sudah diuji restore plus snapshot terbaru. Retensi/enkripsi/kapasitas off-host harus diputuskan pemilik sebelum menghapus snapshot lama; tidak ada penghapusan otomatis.

**RPO** = perubahan sejak snapshot terakhir yang mungkin hilang. **RTO** = durasi pemulihan yang benar-benar terukur saat latihan. Keduanya belum boleh diklaim terjamin hanya karena skrip tersedia.

**Keputusan pemilik, 28 Agustus 2026:** untuk sementara penyimpanan hanya di drive D lokal, tanpa salinan off-host atau upload cloud. Latihan boleh diteruskan dari snapshot lokal terverifikasi sesuai keputusan tersebut. Rekomendasi salinan off-host di runbook tetap berlaku untuk perlindungan dari kehilangan laptop/kerusakan drive; hasil lokal tidak boleh dilaporkan sebagai pemenuhan perlindungan itu. Backup serta folder hasil pemulihan masih belum dienkripsi.

## 3. Latihan pemulihan (tidak mengganti website aktif)

### A. Pilih dan verifikasi

1. Pilih snapshot yang benar: label, schema, SHA, waktu, serta `git-status.txt`. Bandingkan dengan catatan privat, bukan nama folder saja.
2. Jalankan `tool.php verify` pada salinan off-host. Jika gagal atau ada `INCOMPLETE`, jangan digunakan.
3. Ekstrak `files.zip` ke folder baru dengan wrapper/helper. Jangan menimpa checkout lokal/Hostinger, dan jangan menyalin `.env` testing ke aplikasi localhost yang sedang dipakai.

### B. Database uji baru

1. Buat **database kosong dengan nama baru** dan pengguna yang hanya punya hak pada database baru tersebut. Bisa lewat alat MySQL lokal atau hPanel. Jangan menggunakan akun yang juga dapat mengubah database aktif untuk impor percobaan.
2. Pastikan server dan collation mendukung dump. Gunakan client sesuai keluarga/version server. `--single-transaction` adalah strategi snapshot, bukan jaminan kompatibilitas restore lintas versi.
3. Impor ke database baru. Contoh **di Bash/SSH**, ganti placeholder sendiri dan cek kembali target sebelum Enter:

```bash
mysql --binary-mode --local-infile=0 --host=localhost \
  --user=RESTORE_USER --password --database=RESTORE_DB \
  < /absolute/private/path/SNAPSHOT/database.sql
```

Password diminta interaktif, bukan ditulis pada argumen/history. Untuk MariaDB gunakan `mariadb`. Jangan memakai `--force` untuk melanjutkan SQL yang gagal. `--binary-mode` mencegah sebagian besar command client di input non-interaktif; pengguna terbatas mencegah SQL menyasar database lain.

Di Windows, jangan menyalin pipeline `Get-Content SQL | mysql`, karena encoding/binary bisa berubah. Gunakan impor phpMyAdmin pada **database uji yang baru** atau client dengan input berkas yang mempertahankan byte. Test toolkit menguji cara kedua secara otomatis pada data buatan.

Dump routine/event mungkin memuat `DEFINER`/perintah terkait database. Jika impor ditolak karena hak/definer, tinjau secara privat; jangan memberi hak global/root sebagai solusi cepat. Keberhasilan salin file bukan keberhasilan impor SQL.

4. Bandingkan jumlah baris seluruh tabel dengan `manifest.json.table_counts_before_dump` pada snapshot dengan penulisan benar-benar berhenti. Periksa record katalog, galeri, akun, status publik, soft-delete, dan foreign key. Jangan memasukkan isi record ke laporan publik. Ketidaksesuaian harus diselidiki sebelum cutover.

### C. Aplikasi uji terpisah

1. Siapkan checkout/source dari **SHA yang dicatat**, di direktori baru di luar webroot. `source.zip` hanya commit, sedangkan patch/untracked perlu ditinjau manual. Jangan otomatis `git apply` patch ke aplikasi aktif. Jika kode penting belum committed, baseline belum siap dipakai untuk rollback kode.
2. Pasang dependensi dari `composer.lock` dengan `composer install --no-dev --optimize-autoloader --no-scripts` dahulu. Setelah konfigurasi uji aman, baru jalankan script package discovery yang diperlukan. Jangan menjalankan `composer setup`: script itu bisa generate key, migration, dan seeding.
3. Tempatkan salinan konfigurasi dan storage dari hasil ekstraksi ke **checkout uji**, bukan source aktif. Gunakan APP_KEY snapshot, tetapi ubah DB ke database uji, APP_URL ke URL uji lokal, `APP_DEBUG=false` pada alamat publik, dan nonaktifkan pengiriman nyata (mail log, integrasi eksternal/worker/scheduler tidak dijalankan). Jangan memakai cookie domain yang bertabrakan dengan aplikasi aktif. Sebaiknya akses via localhost/private network, bukan domain uji publik berisi salinan akun asli.
4. Gunakan build dan images dari `files.zip` agar manifest dan hash aset sesuai. Jangan mencampur `manifest.json` lama dengan berkas baru. Jangan build ulang dengan versi dependensi acak saat latihan.
5. Buat `public/storage` menuju `storage/app/public` hanya jika target belum ada. Di struktur project standar, saat `php artisan storage:link` gagal karena PHP exec dinonaktifkan, alternatif SSH adalah `ln -s ../storage/app/public public/storage` **dari root checkout uji**. Jika sudah ada, periksa `ls -ld public/storage` dan `readlink public/storage`; jangan langsung hapus/ganti.
6. `php artisan config:clear`, `php artisan package:discover`, dan `php artisan migrate:status` dijalankan hanya pada checkout uji yang telah diarahkan ke DB baru. Jangan menjalankan migration pending untuk membuktikan restore versi lama; validasi baseline dahulu.
7. Jalankan pemeriksaan aplikasi di bawah. Catat durasi dan kegagalan. Jangan memakai `php artisan test` terhadap data hasil restore akun asli: suite aplikasi memakai migration/fixture dan harus tetap diarahkan ke schema testnya sendiri.

### D. Checklist bukti restore

- [ ] SQL terimpor tanpa error ke schema baru dengan pengguna terbatas.
- [ ] Jumlah tabel/baris sesuai snapshot; katalog/galeri/status/peran/soft-delete diperiksa.
- [ ] File unggahan dan thumbnail ada; logo, build, serta halaman frontend berhasil dimuat.
- [ ] Homepage dan kelima profil sesuai status terbit; Ayodya ID/EN diperiksa.
- [ ] Pemilik dapat login ke admin uji dan peran/hak akses sesuai. Uji CRUD hanya pada lingkungan uji terisolasi yang memang boleh dimodifikasi.
- [ ] `.env`, `.git/config`, `composer.json`, dan `storage/logs/laravel.log` tidak terlayani lewat web (403/404, tanpa body sensitif). Verifikasi dari konfigurasi server dan respons, jangan sengaja menampilkan konten rahasia ke terminal.
- [ ] Tidak ada email/WhatsApp/integrasi nyata yang terkirim dari latihan.
- [ ] Waktu mulai/selesai, SHA, snapshot, kegagalan, hasil dan lokasi salinan off-host dicatat secara privat.

## 4. Rencana rollback saat update bermasalah

| Keadaan                                                  | Pilihan awal                                                                                                                            |
| -------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| CSS/JS/nav berubah, schema tetap kompatibel              | Pulihkan **kode + build yang berpasangan**, pertahankan DB dan unggahan terkini                                                         |
| Error karena root `.htaccess`/document root/storage link | Periksa dan pulihkan konfigurasi yang relevan; jangan restore database                                                                  |
| Migration baru sudah berjalan                            | Tinjau kompatibilitas kode lama; pilih forward-fix bila aman. Jangan otomatis `migrate:rollback`                                        |
| Data hilang/korup                                        | Amankan snapshot keadaan sekarang, lakukan restore terisolasi, lalu putuskan pemindahan aplikasi ke DB pulih dengan persetujuan pemilik |

### A. Rollback kode, tanpa mengembalikan data

1. Hentikan deployment lanjutan. Catat error/waktu/SHA baru, dan cadangkan keadaan terbaru agar perubahan setelah baseline tidak hilang.
2. Pilih SHA/baseline terakhir yang sudah diuji. Review migration di antara versi: `git log --oneline SHA_LAMA..SHA_BARU -- database/migrations` dan diff migration. Jika schema tidak kompatibel, **jangan lanjut rollback kode** tanpa rencana khusus.
3. Siapkan versi lama di **checkout/release baru** di luar webroot memakai SHA lengkap atau `source.zip`. Jangan reset checkout aktif dan jangan menghapus file untracked untuk memaksa pull. Pasang dependensi lockfile versi tersebut; build cocok dengan snapshot.
4. Hubungkan release ini ke **konfigurasi terkini dan database/unggahan terkini** yang memang akan dipertahankan. Ini berbeda dari pemulihan data: jangan otomatis memakai `.env`/uploads lama pada rollback kode saja. Uji versi lama dengan schema terkini pada salinan terisolasi dahulu.
5. Pergantian document root/release adalah tindakan terpisah yang perlu persetujuan dan dukungan hosting. Deployment testing saat ini belum memakai atomic release switch. **Jangan mengasumsikan** ada symlink `current` atau mengubah hPanel otomatis. Bila tidak bisa switch release, susun daftar file yang perlu diganti, backup lebih dulu, dan lakukan maintenance terkoordinasi; jangan menyalin/menimpa seluruh `public_html` secara buta.
6. Setelah cutover disetujui, bangun ulang cache konfigurasi/route/view untuk release yang aktif, restart hanya worker yang dikelola, kemudian smoke test dan buka maintenance. Siapkan jalan kembali ke release sebelumnya jika pemeriksaan gagal.

### B. Pemulihan data (dapat kehilangan perubahan terbaru)

1. Pemilik menyetujui snapshot, data yang hilang sejak waktu tersebut, waktu berhenti, serta target database. Backup keadaan sekarang sebelum melakukan apa pun.
2. Pulihkan ke **database baru**, verifikasi lengkap seperti §3. Jangan `DROP`/impor ke database aktif sebagai langkah awal.
3. Siapkan **uploads dari snapshot yang sama**. Menurunkan database saja bisa membuat referensi foto tidak cocok. Tetap simpan unggahan terkini secara terpisah untuk rekonsiliasi; jangan melakukan sinkronisasi dengan opsi delete.
4. Baru setelah diterima, pemilik mengganti koneksi/storage aplikasi ke hasil pulih melalui rencana cutover terkoordinasi. Simpan konfigurasi/key lama secara privat, bersihkan cache yang relevan dan cek autentikasi. Pengguna mungkin harus login kembali.
5. Database/folder sebelum pemulihan tetap dipertahankan sampai hasil diterima dan retensi disepakati. Tidak ada langkah otomatis menghapusnya.

## 5. Catatan khusus layout Hostinger testing

Riwayat troubleshooting menunjukkan checkout berada di `~/domains/lemonchiffon-crane-249931.hostingersite.com/public_html`, dengan entry Laravel di `public/index.php`. Root `.htaccess` manual pernah tidak ada dan homepage memberi 403. **Layout/config server saat ini belum diperiksa ulang oleh toolkit.**

- Laravel seharusnya disajikan dari direktori `public/`, bukan membuka seluruh source. Pilih document root yang benar bila layanan hosting mendukung; migrasi layout membutuhkan rencana terpisah.
- Jika masih memakai rewrite root ke `public/`, root `.htaccess` aktual perlu disimpan dan diuji sebelum/ sesudah update. `public/.htaccess` saja tidak menggantikan file root tersebut.
- Restore root `.htaccess` hanya setelah memeriksa layout; file dari layout lama bisa salah untuk layout baru. Jangan menyimpulkan semua 403 disebabkan satu hal.
- Jangan memindahkan backup ke dalam `domains/.../public_html`, meskipun diberi nama tersembunyi.
- `curl` homepage 200 **tidak** membuktikan DB, admin, seluruh aset, atau akses file rahasia aman. Gunakan checklist yang lebih lengkap.

Referensi: [Laravel deployment](https://laravel.com/framework/docs/13.x/deployment), [MySQL dump dan restore](https://dev.mysql.com/doc/refman/8.4/en/mysqldump.html).

## 6. Catatan latihan privat (isi setelah benar-benar dijalankan)

Simpan di luar repo, tanpa password/token atau isi data akun:

```text
Lingkungan / URL / path:
Waktu mulai dan selesai (timezone):
Snapshot + SHA + hash COMPLETE:
Lokasi salinan off-host dan perlindungannya:
Target DB dan folder uji (berbeda dari aplikasi aktif):
Hasil integritas / impor / jumlah baris / berkas:
Hasil halaman publik / gambar / admin / keamanan:
Durasi pemulihan yang terukur:
Kegagalan, tindak lanjut, dan persetujuan cutover jika ada:
```

Tahap operasional dianggap terbukti hanya setelah backup asli, salinan off-host, dan pemulihan terisolasi aplikasi asli berhasil. Test sintetis adalah pengujian toolkit, bukan pengganti tahap tersebut.
