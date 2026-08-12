<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Catatan: akun dibuat dan dihapus lewat perintah artisan (spec §6), bukan
// lewat panel. Tidak ada route hapus-akun-sendiri — seorang business_admin
// yang menghapus akunnya sendiri akan meninggalkan anak usahanya tanpa
// pengelola, dan tabel users tidak memakai soft delete.

Route::middleware(['auth'])->group(function () {
    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
});
