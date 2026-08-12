<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Daftar akun panel — super-admin saja (spec §6).
 *
 * Hanya menampilkan. Membuat akun dilakukan lewat `php artisan
 * jcorp:make-admin` supaya password tidak pernah melintas lewat form web,
 * dan tidak ada jalur di aplikasi yang bisa menaikkan peran seseorang.
 */
class UserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage', Business::class);

        return Inertia::render('panel/users/index', [
            'users' => User::with('business:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'role_label' => $user->role->label(),
                    'business_name' => $user->business?->name,
                ]),
        ]);
    }
}
