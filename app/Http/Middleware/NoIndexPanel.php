<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjauhkan seluruh halaman panel dari mesin pencari (spec §6).
 *
 * Dikirim sebagai header HTTP, bukan meta tag: di aplikasi Inertia isi
 * halaman baru ada setelah JavaScript jalan, jadi meta tag `noindex` yang
 * disisipkan React belum tentu terbaca crawler. Header selalu terbaca,
 * bahkan pada respons redirect.
 *
 * Dipasang di lapisan `web` dan menyaring sendiri berdasarkan pola URL,
 * bukan dipasang di grup route panel. Alasannya dua, keduanya sempat
 * membuat header ini tidak terkirim saat diuji lewat HTTP sungguhan:
 *
 * 1. Route login/logout/reset-password disediakan Fortify dan ikut prefix
 *    yang sama, tapi berada di luar grup route panel.
 * 2. Redirect dari middleware `auth` dihasilkan lewat exception, sebelum
 *    middleware grup sempat menyentuh respons.
 */
class NoIndexPanel
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('jcorp-panel', 'jcorp-panel/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        return $response;
    }
}
