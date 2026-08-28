# Backup manual J-Corporate

Toolkit ini membuat cadangan **database + unggahan + konfigurasi + build + sumber Git**. Tidak mengubah fitur website, menjalankan migration/seeder, menyalakan maintenance mode, atau mengatur cron.

**Status 28 Agustus 2026:** uji dasar pemulihan lokal lulus. Backup asli localhost berhasil dan latihan terisolasi membuktikan impor 12 tabel, kecocokan jumlah data/file, halaman publik, serta aset. Login super admin, pembacaan panel, dan tambah/ubah/hapus katalog tanpa gambar lulus memakai data sementara yang kemudian dibersihkan. Unggahan satu foto galeri baru beserta WebP/thumbnail dan akses HTTP/browser juga lulus. Soft delete foto uji terverifikasi: hilang dari galeri, empat foto lama tetap ada, record/gambar/thumbnail uji masih tersimpan. Login admin Sweetness dan batas akses baca yang dicoba lulus: konteks usaha tidak berubah melalui parameter URL, halaman akun/usaha ditolak 403, dan galeri usaha lain ditolak 404. Pengaktifan kembali item terhapus serta otorisasi tulis admin anak usaha pada aplikasi latihan belum diuji. Pemilik memilih menyimpan di D saja untuk sementara; tidak ada salinan off-host/enkripsi yang sudah dilakukan. Backup/pemulihan Hostinger dan rollback rilis penuh belum diuji. Toolkit/dokumentasi/test masih belum di-commit sehingga belum masuk `source.zip` snapshot lama. Rincian bukti ada di `PROGRESS.md`; domain Hostinger tetap **testing**, bukan peluncuran produksi.

## Mulai dari pemeriksaan, bukan restore

Jalankan dari folder project. Laragon/MySQL harus aktif. `-Check` / `--check` hanya membaca konfigurasi, versi server, jumlah baris, dan Git; tidak membuat snapshot atau mengubah data.

### Windows / localhost — PowerShell

```powershell
cd 'D:\Kuliah\Bahan Kuliah\Matkul\Vscode\jcorp-company-profile'
.\deploy\backup\backup.ps1 -Destination 'D:\JCorp-Backups' -Check `
  -PhpBinary 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' `
  -DumpBinary 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe'
```

Sesuaikan lokasi PHP/MySQL jika Laragon berubah. `php` dan `mysqldump` boleh tanpa path lengkap jika sudah masuk PATH. Tidak perlu mengubah execution policy sistem; bila skrip diblokir, jalankan dengan `powershell.exe -NoProfile -ExecutionPolicy Bypass -File ...` hanya untuk proses ini setelah membaca skripnya.

Setelah preflight berhasil dan **tidak ada admin, upload, migration, atau worker yang menulis**, ulangi perintah dengan mengganti `-Check` menjadi `-ConfirmNoWrites`.

Jika percobaan dengan versi awal berhenti pada `SetAccessControl` / `Attempted to perform an unauthorized operation`, gunakan helper yang sudah diperbaiki: ia hanya mengubah daftar izin (DACL), tidak menetapkan ulang pemilik folder. Folder yang dibuat pada drive dengan izin turunan `Modify` tidak selalu mengizinkan operasi `SetOwner`. Tidak perlu menjalankan sebagai Administrator atau memberi akses ke semua pengguna. Izin privat dibaca ulang sebelum credential maupun data disalin; bila pemeriksaan gagal, backup berhenti.

Kegagalan pada tahap izin bisa meninggalkan folder **kosong**, tanpa marker `INCOMPLETE` karena tahap persiapan belum berjalan. Folder itu bukan backup dan tidak perlu dipakai/dihapus untuk mencoba ulang: setiap eksekusi membuat nama snapshot baru. Jangan menganggap folder parsial sebagai backup berhasil tanpa hasil verifikasi `COMPLETE`.

### Hostinger testing — SSH Linux

Toolkit harus sudah tersedia di checkout server (commit/pull dilakukan terpisah). Jangan menarik perubahan aplikasi lain hanya demi menjalankan backup; jika ada perubahan lain yang belum dibackup, pindahkan toolkit saja ke checkout terpercaya di lokasi privat dan gunakan `--project`.

```bash
cd "$HOME/domains/lemonchiffon-crane-249931.hostingersite.com/public_html"
bash deploy/backup/backup.sh --destination "$HOME/private-backups/jcorp" --check
```

Jika server memakai MariaDB, gunakan `--dump mariadb-dump`. PHP yang berbeda dapat dipilih melalui `--php /path/to/php`. Toolkit menolak client MySQL yang dipasangkan dengan server MariaDB, dan sebaliknya.

Setelah preflight berhasil dan penulisan benar-benar dihentikan, ganti `--check` dengan `--confirm-no-writes`. Skrip tidak memutus akses pengguna sendiri. Untuk pembekuan penulisan saat server digunakan, ikuti [runbook](../BACKUP-PEMULIHAN-ROLLBACK.md#2-sebelum-membuat-snapshot).

## Persyaratan dan batasan

- PHP CLI 8.3+, `pdo_mysql`, `zip`, serta `vendor/` hasil `composer install` di checkout toolkit. Tidak memerlukan Node atau paket Composer tambahan.
- Git dan client MySQL 8+/MariaDB yang kompatibel dengan server; koneksi database **loopback** dari mesin yang sama. Database remote/TLS/custom storage belum didukung: berhenti untuk peninjauan, bukan mengabaikan opsi koneksi.
- Semua base table harus InnoDB. Dump memakai `--single-transaction`, tetapi operator tetap harus menghentikan perubahan konten, unggahan, DDL/migration, dan worker agar database dan berkas tidak berbeda waktu.
- Kredensial diambil dari `.env` tanpa menjalankannya sebagai shell. Cache konfigurasi yang berbeda, atau environment eksternal yang berbeda, ditolak; cache tidak otomatis dihapus.
- Client memerlukan hak dump tabel/view/trigger/routine/event. Jika ditolak, jangan menambahkan `--force` atau mengabaikan error; tinjau hak akun dan kemampuan paket hosting. Alternatif manual harus tetap mencakup SQL, unggahan, konfigurasi, dan bukti uji restore.
- Native dump dipanggil shell, **bukan PHP `exec()`**, menghindari kendala PHP exec pada shared hosting. `--result-file` mencegah perubahan encoding SQL oleh PowerShell.
- Tujuan wajib absolut, di luar project dan direktori publik. Guard menolak nama umum `public_html`, `public`, `htdocs`, `www`, `wwwroot`, traversal, dan symlink sumber. Operator tetap harus memastikan folder tidak dipublikasikan lewat alias/situs lain.
- Sediakan ruang untuk SQL, dua ZIP, ekstraksi, dan database uji. Toolkit berhenti bila operasi gagal; tidak menghapus backup lama atau snapshot parsial secara otomatis.

## Hasil satu snapshot

```text
TUJUAN/
└── local/ atau hostinger-testing/
    └── jcorp-snapshot-TANGGAL_UTC-ID_ACAK/
        ├── database.sql
        ├── files.zip
        ├── source.zip
        ├── git-status.txt
        ├── git-tracked.patch
        ├── manifest.json
        ├── COMPLETE
        └── dump-error.log
```

| Berkas              | Isi                                                                                                                                                     |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `database.sql`      | Schema dan data database yang dipilih `.env`; bukan semua database server                                                                               |
| `files.zip`         | `.env`, root `.htaccess` bila ada, `public/.htaccess`, `public/index.php`, `public/build`, `public/images`, `storage/app/public`, `storage/app/private` |
| `source.zip`        | Kode pada SHA Git yang dicatat; bukan working tree lengkap                                                                                              |
| `git-tracked.patch` | Perbedaan tracked terhadap SHA tersebut, termasuk perubahan staged/unstaged; ditinjau, tidak otomatis diterapkan                                        |
| `git-status.txt`    | Peringatan file berubah/untracked; **kode untracked tidak masuk source.zip**                                                                            |
| `manifest.json`     | Waktu UTC, asal local/testing, database, versi server, SHA Git, hitungan tabel sebelum dump, ukuran/hash payload dan inventaris berkas                  |
| `COMPLETE`          | Hash manifest, ditulis terakhir setelah pemeriksaan payload; **bukan tanda tangan keaslian**                                                            |

`vendor`, `node_modules`, cache, sesi berbasis file, log aplikasi, konfigurasi hPanel/DNS/PHP/cron, dan executable MySQL tidak dicadangkan. Dependensi dipasang lagi dari lockfile. `public/storage` tidak diikuti sebagai symlink; isinya sudah dicadangkan dari `storage/app/public`, lalu link dibuat ulang. Folder storage kosong dibuat kembali saat ekstraksi.

Snapshot kotor masih berguna untuk pemulihan data, tetapi **bukan rollback kode yang siap dipakai** sampai patch/untracked ditinjau. Simpan kode penting dalam commit sebelum menjadikannya baseline rilis. Git tidak menggantikan backup database/unggahan.

`source.zip` mengikuti aturan `export-ignore` pada `.gitattributes` dan tidak menyimpan folder `.git`/seluruh riwayat. SHA tetap ada di manifest; repository Git dan snapshot data saling melengkapi.

## Kerahasiaan

**Snapshot BELUM dienkripsi.** SQL mengandung data akun/konten, `.env` mengandung password dan APP_KEY, serta private storage mungkin berisi dokumen privat.

- Windows: snapshot baru memakai ACL hanya akun Windows pembuat, tanpa perubahan pemilik atau grup. Izin privat dan pewarisannya ke berkas/subfolder diverifikasi. Gunakan filesystem yang mendukung ACL, bukan FAT/exFAT. Administrator mesin tetap dapat mengambil alih akses.
- Linux: direktori snapshot `0700`, file `0600`, `umask 077`.
- `client.cnf` sementara hanya dibuat di folder privat, lalu dihapus sebelum snapshot selesai dan juga saat skrip gagal. Jika proses dibunuh paksa/mesin mati, folder `INCOMPLETE` bisa masih berisi file ini: lindungi dan hapus **hanya file tersebut** setelah memastikan proses sudah berhenti. Jangan memaksa snapshot parsial menjadi `COMPLETE`.
- Jangan kirim `.env`, SQL, ZIP, patch, atau isi log dump ke chat; jangan commit/unggah ke webroot atau tautan publik.
- Simpan salinan kedua di perangkat/lokasi lain dengan enkripsi at-rest (misalnya drive terenkripsi yang sudah dimiliki). SFTP/SCP mengenkripsi transfer, **bukan file yang sudah tersimpan**. Salinan di disk/server yang sama tidak melindungi dari kerusakan mesin tersebut.

## Memeriksa hasil / salinan

```bash
php deploy/backup/tool.php verify /absolute/path/to/jcorp-snapshot-TANGGAL-ID
```

Di PowerShell, gunakan `& 'C:\...\php.exe' ...` jika PHP tidak ada di PATH. Jalankan verifikasi lagi **pada salinan off-host**, bukan hanya asalnya.

Verifikasi memeriksa checksum/ukuran, kelengkapan, isi ZIP, serta menolak path traversal/duplikat/symlink arsip. Ia **tidak** mengimpor SQL, tidak membuktikan login/gambar berhasil, dan tidak mendeteksi pemalsuan jika pelaku mengubah seluruh payload beserta manifest. Gunakan hanya backup milik sendiri dari sumber tepercaya.

### Ekstraksi aman ke folder baru

Windows (target **belum ada**, wrapper membuat ACL privat):

```powershell
.\deploy\backup\extract.ps1 -Snapshot 'D:\JCorp-Backups\local\NAMA-SNAPSHOT' `
  -Target 'D:\JCorp-Recovery\latihan-01' -PhpBinary 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe'
```

Linux (buat target **baru** privat, bukan lokasi website):

```bash
umask 077
mkdir -m 700 "$HOME/jcorp-recovery-latihan-01"
php deploy/backup/tool.php extract /absolute/path/to/SNAPSHOT "$HOME/jcorp-recovery-latihan-01"
```

Target berisi file ditolak. Hasil ini hanya ekstraksi `files.zip`, **belum pemulihan database atau aplikasi**. Lanjutkan dengan [uji restore dan rollback](../BACKUP-PEMULIHAN-ROLLBACK.md).

## Menjalankan test toolkit

Test biasa tidak mem-boot Laravel dan tidak menyentuh database aplikasi:

```bash
php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php tests/Backup
```

Pada Windows, test ini juga menjalankan regresi ACL dengan fixture temporer yang hanya mewariskan `Modify`, tanpa hak `WRITE_OWNER`. Ia memeriksa izin hanya untuk akun pembuat sampai ke file/subfolder, pemilik/grup tetap, izin parent tidak berubah, serta penolakan target yang sudah ada. Runtime default Windows PowerShell 5.1; `JCORP_DRILL_POWERSHELL` dapat menunjuk ke `pwsh.exe` untuk PowerShell 7. Tidak memerlukan database atau hak Administrator.

Drill MySQL bersifat **opt-in Windows/Laragon lokal**. Membuat dua schema acak `jcorp_backup_drill_*`, akun restore acak yang dibatasi hanya ke schema tujuan, repository/berkas sintetis, lalu menghapus hanya objek buatannya. Memerlukan root loopback tanpa password sesuai Laragon test ini; jangan jalankan dengan akun/server produksi atau mengubah password server untuk memenuhi test.

```powershell
$env:JCORP_BACKUP_MYSQL_DRILL = '1'
$env:JCORP_DRILL_MYSQLDUMP = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe'
$env:JCORP_DRILL_MYSQL = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe'
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php tests/Backup
```

Gunakan terminal sementara lalu tutup setelah test. Jika test terputus paksa, periksa objek berawalan acak yang dilaporkan test; jangan menghapus database/folder dengan wildcard.

Referensi perilaku dump dan batas konsistensinya: [dokumentasi MySQL](https://dev.mysql.com/doc/refman/8.4/en/mysqldump.html).
