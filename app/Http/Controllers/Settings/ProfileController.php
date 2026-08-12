<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil akun milik admin yang sedang masuk.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/profile');
    }

    /**
     * Memperbarui nama dan email akun sendiri.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profil akun diperbarui.']);

        return to_route('profile.edit');
    }
}
