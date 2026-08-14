<?php

use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Panel\BusinessController;
use App\Http\Controllers\Panel\BusinessProfileController;
use App\Http\Controllers\Panel\CatalogItemController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\PortfolioItemController;
use App\Http\Controllers\Panel\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel admin — /jcorp-panel
|--------------------------------------------------------------------------
|
| Route login/logout/reset-password disediakan Fortify dan ikut ber-prefix
| yang sama lewat `config/fortify.php`, jadi tidak ditulis ulang di sini.
|
| Alamat tersembunyi ini penyamaran, bukan kunci (spec §6). Yang melindungi:
| middleware `auth`, throttle login, dan pengecekan kepemilikan di Policy.
|
| Header `noindex` dipasang di `bootstrap/app.php`, bukan di sini — supaya
| halaman login Fortify dan respons redirect ikut terlindungi.
|
*/

/*
| Aktivasi undangan — DI LUAR middleware `auth`.
|
| Yang membukanya memang belum punya akun yang bisa dipakai masuk: akunnya
| ada, tapi passwordnya belum dibuat. Throttle menahan penebakan token —
| tanpa itu, token 48 karakter pun bisa dicoba berkali-kali tanpa hambatan.
*/
Route::prefix('jcorp-panel')
    ->name('panel.')
    ->middleware('throttle:10,1')
    ->group(function () {
        Route::get('undangan/{token}', [InvitationController::class, 'show'])
            ->name('invitation.show');
        Route::post('undangan/{token}', [InvitationController::class, 'store'])
            ->name('invitation.store');
    });

Route::prefix('jcorp-panel')
    ->name('panel.')
    ->middleware(['auth', EnsureUserIsActive::class])
    ->group(function () {
        Route::get('/', fn () => redirect()->route('panel.dashboard'));

        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('catalog', [CatalogItemController::class, 'index'])->name('catalog.index');
        Route::post('catalog', [CatalogItemController::class, 'store'])->name('catalog.store');
        Route::put('catalog/{catalogItem}', [CatalogItemController::class, 'update'])->name('catalog.update');
        Route::delete('catalog/{catalogItem}', [CatalogItemController::class, 'destroy'])->name('catalog.destroy');

        Route::get('portfolio', [PortfolioItemController::class, 'index'])->name('portfolio.index');
        Route::post('portfolio', [PortfolioItemController::class, 'store'])->name('portfolio.store');
        Route::put('portfolio/{portfolioItem}', [PortfolioItemController::class, 'update'])->name('portfolio.update');
        Route::delete('portfolio/{portfolioItem}', [PortfolioItemController::class, 'destroy'])->name('portfolio.destroy');

        Route::get('profile', [BusinessProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [BusinessProfileController::class, 'update'])->name('profile.update');

        // Super-admin saja. authorize() di controller menahan sekali lagi,
        // supaya middleware bukan satu-satunya penjaga.
        Route::middleware(EnsureUserIsSuperAdmin::class)->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}/active', [UserController::class, 'toggleActive'])
                ->name('users.active');
            Route::post('users/{user}/resend', [UserController::class, 'resendInvitation'])
                ->name('users.resend');

            Route::get('businesses', [BusinessController::class, 'index'])->name('businesses.index');
            Route::put('businesses/{business}/published', [BusinessController::class, 'togglePublished'])
                ->name('businesses.published');
            Route::put('businesses/{business}/portfolio', [BusinessController::class, 'togglePortfolio'])
                ->name('businesses.portfolio');
        });
    });
