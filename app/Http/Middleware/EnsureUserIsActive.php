<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak akun yang dinonaktifkan atau belum mengaktifkan undangannya.
 *
 * DIPERIKSA DI SETIAP PERMINTAAN, BUKAN HANYA SAAT LOGIN
 * ------------------------------------------------------
 * Kalau statusnya hanya diperiksa saat masuk, admin yang aksesnya baru saja
 * dicabut masih bisa mengubah dan menghapus data sampai sesinya kedaluwarsa —
 * dua jam berikutnya. Padahal alasan mencabut akses biasanya justru mendesak.
 *
 * Biayanya praktis nol: Laravel memang sudah mengambil baris user dari
 * database di setiap permintaan untuk mengisi `$request->user()`.
 *
 * Sesinya sekalian diakhiri, bukan cuma dialihkan — kalau tidak, penggunanya
 * berputar-putar antara panel dan login tanpa pernah benar-benar keluar.
 */
class EnsureUserIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->canAccessPanel()) {
            $pesan = $user->isPendingInvitation()
                ? 'Akun ini belum diaktifkan. Buka tautan undangan yang dikirim super-admin.'
                : 'Akses akun Anda sudah dinonaktifkan. Hubungi super-admin.';

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => $pesan]);
        }

        return $next($request);
    }
}
