<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Undangan admin baru.
 *
 * Super-admin membuat akun tanpa password, lalu mengirim tautan sekali pakai
 * lewat WhatsApp. Pemilik akun sendiri yang membuat passwordnya.
 *
 * KENAPA BEGINI, BUKAN SUPER-ADMIN MENGETIK PASSWORD LANGSUNG
 * -----------------------------------------------------------
 * Spec §6 menetapkan akun dibuat lewat artisan supaya password tidak pernah
 * melintas lewat form web. Alasan itu tetap dihormati di sini — yang melintas
 * cuma tautannya, dan passwordnya diketik pemiliknya sendiri di halaman
 * aktivasi. Super-admin tidak pernah tahu password admin lain.
 *
 * TOKEN DISIMPAN SEBAGAI HASH
 * ---------------------------
 * Yang masuk database bukan token mentahnya, melainkan hash-nya — perlakuan
 * yang sama dengan password. Kalau database bocor, token yang bisa dipakai
 * tidak ikut terbawa.
 *
 * Konsekuensinya: token mentah hanya ada sekali, saat dibuat. Kalau
 * super-admin lupa menyalinnya, tautannya harus dibuat ulang — tidak ada
 * cara menampilkannya lagi.
 */
class InvitationService
{
    /**
     * Membuat token undangan baru untuk sebuah akun.
     *
     * Mengembalikan token MENTAH untuk dirangkai jadi tautan. Yang tersimpan
     * di database adalah hash-nya.
     */
    public function issue(User $user): string
    {
        $token = Str::random(48);

        $user->invitation_token = hash('sha256', $token);
        $user->invitation_expires_at = now()->addDays(User::INVITATION_VALID_DAYS);
        $user->save();

        return $token;
    }

    /**
     * Mencari akun dari token mentah.
     *
     * null bila tokennya tidak dikenal, sudah dipakai, atau kedaluwarsa —
     * ketiganya sengaja tidak dibedakan, supaya halaman aktivasi tidak
     * membocorkan apakah sebuah token pernah ada.
     */
    public function resolve(string $token): ?User
    {
        $user = User::where('invitation_token', hash('sha256', $token))->first();

        if (! $user || ! $user->isPendingInvitation() || $user->invitationIsExpired()) {
            return null;
        }

        return $user;
    }

    /**
     * Menyelesaikan undangan: password dibuat, token dimusnahkan.
     *
     * Token dihapus supaya tautannya sekali pakai — tautan yang tercecer di
     * riwayat WhatsApp tidak berguna lagi setelah dipakai.
     */
    public function accept(User $user, string $password): void
    {
        $user->password = $password;
        $user->invitation_token = null;
        $user->invitation_expires_at = null;
        $user->is_active = true;
        $user->save();
    }

    /**
     * Tautan lengkap yang disalin super-admin dan dikirim lewat WhatsApp.
     */
    public function url(string $token): string
    {
        return route('panel.invitation.show', ['token' => $token]);
    }
}
