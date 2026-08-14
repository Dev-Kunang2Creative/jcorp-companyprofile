# Rencana — Kelola Akun lewat Panel

**Tanggal:** 14 Agustus 2026
**Acuan:** [spec §6](../specs/2026-08-10-jcorp-company-profile-design.md)

Super-admin bisa mengundang, menonaktifkan, dan mengirim ulang undangan admin anak usaha — tanpa perlu SSH.

---

## Kenapa ini dikerjakan

Spec §6 memuat dua hal yang agak bertentangan:

- *"Tidak ada halaman pendaftaran; akun dibuat lewat perintah artisan"*
- Menu `/jcorp-panel/users` disebut **"Kelola akun"**

Yang terbangun sekarang hanya menampilkan daftar — menunya bernama "Kelola" tapi tidak bisa mengelola apa pun. Setiap penambahan admin harus lewat SSH, dan itu hanya bisa dilakukan developer.

**Alasan spec memilih artisan tetap sah:** password tidak boleh melintas lewat form web. Rencana ini menghormati alasan itu tanpa mempertahankan keterbatasannya.

---

## Cara kerjanya

```
SUPER-ADMIN                    ADMIN BARU
─────────────                  ──────────
Isi nama, email,
anak usaha
  ↓
Sistem membuat
tautan sekali pakai
  ↓
Salin, kirim
lewat WhatsApp      ──────→    Buka tautan
                                 ↓
                               Buat password sendiri
                                 ↓
                               Akun aktif, langsung masuk
```

**Password tidak pernah diketahui super-admin, dan tidak pernah melewati form panel.** Ini lebih aman daripada `jcorp:make-admin` sekalipun — di sana developer mengetikkan password orang lain.

Tidak butuh SMTP. Tautannya disalin manual, sesuai kebiasaan pasar yang memang memakai WhatsApp (spec §8).

---

## Keputusan yang sudah diambil

| Hal | Pilihan |
|---|---|
| Cara membuat akun | Undangan lewat tautan |
| Yang bisa dilakukan | Undang, nonaktifkan, kirim ulang undangan |
| **Tidak** dikerjakan | Hapus akun, pindah anak usaha |
| Akun dinonaktifkan saat login | **Langsung terlempar keluar** |
| Masa berlaku undangan | **7 hari** |

### Kenapa "hapus akun" tidak dikerjakan

Tabel `users` tidak memakai soft delete — sekali dihapus, hilang. Hampir semua alasan ingin menghapus terjawab oleh "nonaktifkan", yang lebih aman dan bisa dibatalkan. Penghapusan permanen tetap bisa lewat SSH untuk kasus langka.

### Kenapa "pindah anak usaha" tidak dikerjakan

Aturannya satu anak usaha satu admin. Memindahkan berarti anak usaha lama jadi tanpa pengelola, dan itu butuh penjagaan rumit untuk kasus yang jarang. Cara sederhana yang sudah cukup: nonaktifkan yang lama, undang yang baru.

---

## Yang sudah diperiksa

Sebelum rencana ini ditulis, bukan diasumsikan:

- **Kolom `password` bertipe NOT NULL** — akun undangan yang belum punya password perlu penanganan
- **Token reset bawaan Laravel hanya berlaku 60 menit** — terlalu pendek untuk undangan lewat WhatsApp, jadi butuh mekanisme sendiri
- Tabel `password_reset_tokens` sudah ada, tapi dipakai untuk keperluan berbeda — tidak akan disentuh

---

## Langkah 1 — Migrasi

Tiga kolom baru di `users`:

```php
$table->boolean('is_active')->default(true)->after('business_id');
$table->string('invitation_token', 64)->nullable()->unique()->after('is_active');
$table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
```

Dan **`password` diubah jadi nullable** — akun undangan belum punya password sampai pemiliknya membuatnya sendiri.

Ini yang membedakan akun undangan dari akun aktif:

| Keadaan | `password` | `invitation_token` |
|---|---|---|
| Menunggu undangan dibuka | `null` | terisi |
| Aktif | terisi | `null` |
| Dinonaktifkan | terisi | `null`, `is_active` false |

**Tokennya disimpan sebagai hash**, bukan apa adanya. Kalau database bocor, token mentahnya tidak ikut — sama seperti perlakuan terhadap password.

---

## Langkah 2 — Model

Di `User`:

```php
public function isPendingInvitation(): bool   // password null & token masih ada
public function invitationIsExpired(): bool
```

`is_active` masuk `$casts` sebagai boolean. Ketiga kolom baru **tidak** masuk `$fillable` — sama alasannya dengan `role` dan `business_id`: kalau ikut terisi dari form, seseorang bisa mengaktifkan dirinya sendiri lewat satu field tambahan di request.

---

## Langkah 3 — Middleware `EnsureUserIsActive`

Dipasang di grup route panel. Setiap permintaan memeriksa `is_active`; kalau mati, sesi diakhiri dan dialihkan ke login dengan keterangan.

**Kenapa di setiap permintaan, bukan hanya saat login:** akun yang dicabut aksesnya karena masalah tidak boleh bisa mengubah atau menghapus data selama dua jam sisa sesinya.

Biayanya praktis nol — Laravel memang sudah mengambil data user di setiap permintaan.

Akun yang undangannya belum dibuka juga ditolak di sini, sebagai lapisan kedua.

---

## Langkah 4 — Controller undangan

`UserController` ditambah:

| Method | Fungsi |
|---|---|
| `store` | Membuat akun + token undangan, mengembalikan tautannya |
| `resend` | Membuat token baru untuk akun yang undangannya kedaluwarsa |
| `toggleActive` | Menyalakan/mematikan akses |

Semuanya diawali `authorize('manage', Business::class)` — sama seperti route super-admin lain.

**Aturan yang ditegakkan di server:**

- Satu anak usaha hanya boleh punya satu admin **aktif**
- `business_admin` wajib punya `business_id`; `super_admin` wajib tidak
- Super-admin **tidak bisa menonaktifkan dirinya sendiri** — kalau tidak, sistem bisa terkunci tanpa satu pun super-admin

Aturan pertama dan kedua sudah ada di `MakeAdminCommand`; logikanya dipindah ke satu tempat supaya tidak ada dua versi yang bisa lepas sinkron.

---

## Langkah 5 — Halaman aktivasi

Route publik di luar `auth`, karena yang membukanya belum punya akun aktif:

```
GET  /jcorp-panel/undangan/{token}
POST /jcorp-panel/undangan/{token}
```

Halamannya memakai layout auth yang sudah ada. Isinya: nama dan anak usaha yang ditampilkan sebagai penegasan, lalu dua kolom password.

Yang dijaga:

- Token tidak ada atau kedaluwarsa → 404 bergaya website, bukan pesan yang membocorkan apakah tokennya pernah ada
- Token dibatasi percobaan (throttle), supaya tidak bisa ditebak dengan mencoba berkali-kali
- Setelah password dibuat: token dihapus, `is_active` true, langsung masuk

**Token sekali pakai** — begitu dipakai, tautan lama mati. Jadi tautan yang tercecer di riwayat WhatsApp tidak berguna lagi.

---

## Langkah 6 — Tampilan panel

Halaman **Kelola Akun** ditulis ulang:

- Tombol **Undang admin** di kanan atas, membuka modal (pola yang sama dengan Katalog)
- Setelah undangan dibuat, tautannya ditampilkan dengan tombol **Salin** — ini satu-satunya kesempatan menyalinnya
- Tiap baris menampilkan status: **Aktif**, **Menunggu aktivasi**, **Undangan kedaluwarsa**, atau **Nonaktif**
- Tombol per baris menyesuaikan keadaannya

Menonaktifkan dikonfirmasi lewat dialog yang menyebut nama dan akibatnya — pola yang sama dengan sakelar terbit.

---

## Langkah 7 — `jcorp:make-admin` tetap ada

Perintah artisan **tidak dihapus**. Alasannya:

- Super-admin pertama harus dibuat sebelum ada siapa pun yang bisa mengundang
- Jalan keluar kalau semua super-admin kehilangan akses

Yang berubah: logikanya memakai aturan yang sama dengan controller, supaya tidak ada dua tempat yang bisa berbeda perilaku.

---

## Langkah 8 — Test

| Berkas | Membuktikan |
|---|---|
| `UserInvitationTest` | Undangan membuat akun tanpa password; token sekali pakai; token kedaluwarsa ditolak; token asing 404 |
| `UserInvitationTest` | Password yang dibuat benar-benar bisa dipakai masuk |
| `UserManagementTest` | business_admin ditolak di seluruh route kelola akun |
| `UserManagementTest` | Super-admin tidak bisa menonaktifkan dirinya sendiri |
| `UserManagementTest` | Satu anak usaha tidak bisa punya dua admin aktif |
| `AccountStatusTest` | Akun nonaktif **langsung** terlempar keluar, bukan menunggu sesi habis |
| `AccountStatusTest` | Akun yang undangannya belum dibuka tidak bisa masuk panel |

Yang terakhir dua itu inti keamanannya — ditulis dari sisi penyerang, sama seperti test kepemilikan di Fase 1.

---

## Yang perlu diperhatikan

**Ini menyentuh jalur pembuatan akun**, bagian paling sensitif dari sisi keamanan. Karena itu:

- Token disimpan sebagai hash, bukan apa adanya
- Halaman aktivasi dibatasi percobaan
- Token sekali pakai dan kedaluwarsa
- Setiap aturan ditegakkan di server, bukan disembunyikan di tampilan

**Migrasi mengubah kolom `password` jadi nullable.** Akun yang sudah ada tidak terpengaruh — semuanya sudah punya password.

**Perlu dijalankan di server** setelah di-push:

```bash
git pull origin main
php artisan migrate --force
php artisan config:cache
```

---

## Yang tidak dikerjakan

- Hapus akun permanen — tetap lewat SSH
- Pindah admin antar anak usaha
- Kirim undangan lewat email — butuh SMTP, dan WhatsApp sudah jadi pilihan sadar di spec §8
- Beberapa admin per anak usaha — spec §4 menetapkan satu admin satu anak usaha
