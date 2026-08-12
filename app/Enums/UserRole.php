<?php

namespace App\Enums;

/**
 * Peran akun panel.
 *
 * Nilainya sengaja dikunci di dua tempat sekaligus — enum ini dan kolom
 * `enum` di tabel `users`. Salah ketik seperti 'superadmin' gagal saat
 * disimpan, bukan diam-diam lolos sebagai peran yang tidak dikenali.
 */
enum UserRole: string
{
    /** Menguasai seluruh anak usaha, termasuk sakelar terbit dan kelola akun. */
    case SuperAdmin = 'super_admin';

    /** Hanya mengelola satu anak usaha yang ditunjuk lewat `business_id`. */
    case BusinessAdmin = 'business_admin';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::BusinessAdmin => 'Admin Anak Usaha',
        };
    }
}
