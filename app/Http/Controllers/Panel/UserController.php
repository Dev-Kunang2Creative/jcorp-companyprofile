<?php

namespace App\Http\Controllers\Panel;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\InviteUserRequest;
use App\Models\Business;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kelola akun panel — super-admin saja (spec §6).
 *
 * Super-admin bisa mengundang admin baru, mengirim ulang undangan, dan
 * menonaktifkan akses. Password tidak pernah melewati form ini — yang
 * diundang membuatnya sendiri lewat tautan (lihat InvitationService).
 *
 * Yang TIDAK bisa dilakukan di sini, sengaja:
 *
 * - Menghapus akun. Tabel `users` tidak memakai soft delete, jadi sekali
 *   hilang tidak bisa dipulihkan. "Nonaktifkan" menjawab hampir semua
 *   alasan ingin menghapus, dan bisa dibatalkan.
 * - Memindahkan admin antar anak usaha. Aturannya satu anak usaha satu
 *   admin, jadi memindahkan berarti yang lama jadi tanpa pengelola.
 *   Caranya: nonaktifkan yang lama, undang yang baru.
 */
class UserController extends Controller
{
    public function __construct(private readonly InvitationService $invitations) {}

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
                    'status' => $user->accountStatus(),
                    // Supaya tampilan bisa menyembunyikan tombol nonaktif
                    // pada akun sendiri — penjagaan sungguhannya di server.
                    'is_self' => $user->id === request()->user()->id,
                ]),

            // Hanya anak usaha yang belum punya admin aktif — yang lain
            // tidak perlu muncul sebagai pilihan.
            'availableBusinesses' => Business::query()
                ->whereDoesntHave('admins', fn ($q) => $q->where('is_active', true))
                ->orderBy('sort_order')
                ->get(['slug', 'name']),
        ]);
    }

    /**
     * Mengundang admin baru.
     *
     * Akun dibuat TANPA password. Yang dikembalikan adalah tautan sekali
     * pakai untuk disalin super-admin dan dikirim lewat WhatsApp.
     */
    public function store(InviteUserRequest $request): RedirectResponse
    {
        $this->authorize('manage', Business::class);

        $user = new User;
        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->role = UserRole::from($request->validated('role'));
        $user->business_id = $request->validated('business')
            ? Business::where('slug', $request->validated('business'))->value('id')
            : null;
        // password sengaja dibiarkan null — itu yang menandai akun ini
        // masih menunggu undangan dibuka.
        $user->is_active = true;
        $user->save();

        $token = $this->invitations->issue($user);

        // Tautannya dikirim lewat flash, bukan disimpan. Token mentah hanya
        // ada sekali; kalau super-admin lupa menyalinnya, undangannya harus
        // dibuat ulang.
        Inertia::flash('invitation', [
            'name' => $user->name,
            'url' => $this->invitations->url($token),
            'expires_in_days' => User::INVITATION_VALID_DAYS,
        ]);

        return back();
    }

    /**
     * Membuat tautan undangan baru.
     *
     * Diperlukan kalau tautan lama kedaluwarsa atau pesan WhatsApp-nya
     * hilang — tanpa ini, akun undangan jadi menggantung: tidak bisa
     * dipakai, tidak bisa diperbaiki.
     */
    public function resendInvitation(User $user): RedirectResponse
    {
        $this->authorize('manage', Business::class);

        abort_unless(
            $user->isPendingInvitation(),
            422,
            'Akun ini sudah aktif, tidak perlu undangan lagi.',
        );

        $token = $this->invitations->issue($user);

        Inertia::flash('invitation', [
            'name' => $user->name,
            'url' => $this->invitations->url($token),
            'expires_in_days' => User::INVITATION_VALID_DAYS,
        ]);

        return back();
    }

    /**
     * Menyalakan atau mematikan akses akun.
     *
     * `is_active` tidak fillable, jadi disetel lewat properti langsung —
     * memastikan hanya jalur ini yang bisa mengubahnya.
     */
    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', Business::class);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        // Super-admin tidak boleh menonaktifkan dirinya sendiri. Kalau
        // dibiarkan, satu-satunya super-admin bisa mengunci diri keluar dan
        // tidak ada yang bisa mengaktifkannya lagi selain lewat SSH.
        abort_if(
            $user->id === $request->user()->id && ! $validated['is_active'],
            422,
            'Anda tidak bisa menonaktifkan akun sendiri.',
        );

        $user->is_active = $validated['is_active'];
        $user->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $user->is_active
                ? "Akses {$user->name} dinyalakan."
                : "Akses {$user->name} dimatikan.",
        ]);

        return back();
    }
}
