<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route publik
|--------------------------------------------------------------------------
|
| Halaman induk J Corp — etalase: hero, profil singkat, dan kartu anak usaha
| yang sudah diterbitkan.
|
*/

Route::get('/', HomeController::class)->name('home');

/*
|--------------------------------------------------------------------------
| Panel admin
|--------------------------------------------------------------------------
|
| Route login/logout/reset-password disediakan Fortify dan otomatis ikut
| ber-prefix `jcorp-panel` lewat `config/fortify.php`.
|
*/

require __DIR__.'/panel.php';

require __DIR__.'/settings.php';

/*
|--------------------------------------------------------------------------
| Profil anak usaha — DIDAFTARKAN PALING AKHIR (Fase 3)
|--------------------------------------------------------------------------
|
| `GET /{slug}` cocok dengan hampir semua alamat satu segmen, termasuk
| `/jcorp-panel`. Laravel memakai route yang cocok pertama, jadi route itu
| WAJIB berada di baris terakhir file ini — kalau digeser ke atas, panel
| admin akan tertangkap olehnya dan mengembalikan 404.
|
| Lapisan kedua: pola `[a-z][a-z0-9-]*` membatasi bentuk slug yang diterima.
|
*/

Route::get('/{slug}', PublicProfileController::class)
    ->where('slug', '[a-z][a-z0-9-]*')
    ->name('business.show');
