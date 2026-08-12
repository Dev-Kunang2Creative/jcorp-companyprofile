<?php

namespace App\Http\Controllers\Panel\Concerns;

use App\Models\Business;
use App\Support\PanelContext;
use Illuminate\Http\Request;

/**
 * Menentukan anak usaha mana yang sedang dikelola.
 *
 * Ini penjaga sisi baca dari spec §6: business_admin hanya menerima data
 * miliknya sendiri dari server — data anak usaha lain tidak pernah dikirim
 * ke browsernya. Query disaring di sini, bukan diambil semua lalu difilter
 * di React.
 */
trait ResolvesActiveBusiness
{
    /**
     * Business_admin selalu terkunci ke anak usahanya sendiri; parameter
     * `?business=` dari URL diabaikan sepenuhnya untuk peran ini.
     *
     * Super-admin boleh berpindah konteks lewat parameter itu, dan pilihannya
     * DIINGAT di sesi. Tanpa itu, konteks hilang begitu berpindah menu —
     * super-admin yang memilih Sweetness di dashboard akan mendapati halaman
     * katalog menampilkan J Corp lagi.
     */
    protected function activeBusiness(Request $request): Business
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            // Akun business_admin tanpa business_id adalah data rusak —
            // menolaknya lebih aman daripada menebak anak usaha mana.
            abort_if($user->business_id === null, 403, 'Akun ini belum terhubung ke anak usaha mana pun.');

            return $user->business()->firstOrFail();
        }

        // Pilihan baru dari URL menimpa yang tersimpan.
        if ($slug = $request->query('business')) {
            $business = Business::where('slug', $slug)->firstOrFail();

            $request->session()->put(PanelContext::SESSION_KEY, $business->slug);

            return $business;
        }

        if ($remembered = $request->session()->get(PanelContext::SESSION_KEY)) {
            $business = Business::where('slug', $remembered)->first();

            // Anak usaha yang tersimpan bisa saja sudah dihapus sejak
            // terakhir dipilih — jatuh ke induk alih-alih gagal.
            if ($business) {
                return $business;
            }

            $request->session()->forget(PanelContext::SESSION_KEY);
        }

        return Business::where('is_parent', true)->firstOrFail();
    }
}
