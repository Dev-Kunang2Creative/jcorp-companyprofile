<?php

namespace App\Http\Controllers;

use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman aktivasi undangan.
 *
 * Berada DI LUAR middleware `auth` — yang membukanya memang belum punya akun
 * yang bisa dipakai masuk.
 *
 * Yang dijaga:
 *
 * - Token tidak dikenal, sudah dipakai, atau kedaluwarsa: ketiganya
 *   menghasilkan 404 yang sama persis. Membedakannya akan memberi tahu
 *   penebak bahwa sebuah token pernah ada.
 * - Route-nya dibatasi percobaan (throttle), supaya token 48 karakter tidak
 *   bisa ditebak dengan mencoba berkali-kali.
 * - Password yang dibuat memakai aturan yang sama dengan seluruh aplikasi.
 */
class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitations) {}

    public function show(string $token): Response
    {
        $user = $this->invitations->resolve($token);

        abort_if($user === null, 404);

        return Inertia::render('auth/invitation', [
            'token' => $token,
            // Ditampilkan sebagai penegasan bahwa undangannya memang untuk
            // orang yang benar. Email tidak ikut dikirim — tidak menambah
            // kepastian apa pun, dan tautannya bisa saja salah kirim.
            'name' => $user->name,
            'businessName' => $user->business?->name,
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $user = $this->invitations->resolve($token);

        abort_if($user === null, 404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $this->invitations->accept($user, $validated['password']);

        // Langsung masuk — orangnya baru saja membuktikan menguasai tautan
        // undangan DAN menetapkan passwordnya sendiri. Meminta login lagi
        // hanya menambah langkah tanpa menambah keamanan.
        Auth::login($user);

        $request->session()->regenerate();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Akun Anda aktif. Selamat datang, '.$user->name.'.',
        ]);

        return redirect()->route('panel.dashboard');
    }
}
