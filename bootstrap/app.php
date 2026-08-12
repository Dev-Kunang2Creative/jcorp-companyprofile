<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\NoIndexPanel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            // Dipasang di lapisan `web`, bukan di grup route panel, karena
            // dua hal yang tidak tertangkap kalau dipasang per-grup:
            //
            // 1. Route login/logout/reset-password milik Fortify berada di
            //    bawah prefix yang sama tapi di luar grup route panel.
            // 2. Respons redirect dari middleware `auth` dihasilkan lewat
            //    exception, sebelum middleware grup sempat menyentuhnya.
            //
            // Middleware ini menentukan sendiri kapan header dikirim,
            // berdasarkan pola URL — jadi seluruh /jcorp-panel/* terlindungi
            // termasuk halaman login dan setiap redirect.
            NoIndexPanel::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /**
         * SATU callback untuk dua urusan sekaligus — bukan kebetulan.
         *
         * Laravel hanya menyimpan SATU callback `respondUsing`; panggilan
         * kedua menimpa yang pertama tanpa peringatan. Inertia
         * `handleExceptionsUsing()` juga memakai hook yang sama, jadi kalau
         * halaman error didaftarkan terpisah, header noindex akan hilang
         * diam-diam. Karena itu keduanya digabung di sini.
         */
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            // 1. Header noindex untuk seluruh /jcorp-panel/*.
            //
            // Respons yang lahir dari exception — termasuk redirect ke login
            // dari middleware `auth` — dibuat di dalam pipeline route, yang
            // lebih dalam daripada middleware `web`. Akibatnya NoIndexPanel
            // tidak pernah menyentuhnya. Ketahuan saat diuji lewat HTTP
            // sungguhan, bukan dari membaca kode.
            if ($request->is('jcorp-panel', 'jcorp-panel/*')) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            }

            // 2. Halaman error bergaya website untuk pengunjung (spec §10).
            //
            // Hanya untuk halaman publik: di panel admin, halaman error
            // bawaan Laravel justru lebih berguna karena menampilkan detail
            // teknis saat APP_DEBUG menyala.
            if (
                ! app()->environment('local', 'testing')
                && ! $request->is('jcorp-panel', 'jcorp-panel/*')
                && in_array($response->getStatusCode(), [404, 500, 503], true)
            ) {
                return Inertia::render('errors/error', [
                    'status' => $response->getStatusCode(),
                ])->toResponse($request)->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
