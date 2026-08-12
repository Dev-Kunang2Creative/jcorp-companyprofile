<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\User;

/**
 * Pengecekan kepemilikan item katalog (spec §6).
 *
 * Aturannya satu kalimat: super-admin boleh apa saja; business_admin hanya
 * boleh menyentuh baris yang `business_id`-nya sama dengan miliknya.
 *
 * Ini ditegakkan di sisi server, bukan dengan menyembunyikan tombol di
 * tampilan — permintaan hapus tetap bisa dikirim langsung ke server dengan
 * mengubah angka di alamat.
 */
class CatalogItemPolicy
{
    public function view(User $user, CatalogItem $item): bool
    {
        return $user->owns($item->business_id);
    }

    /**
     * Membuat item selalu terikat ke satu anak usaha yang dituju, jadi
     * anak usahanya ikut diperiksa — bukan hanya "apakah dia admin".
     */
    public function create(User $user, Business $business): bool
    {
        return $user->owns($business->id);
    }

    public function update(User $user, CatalogItem $item): bool
    {
        return $user->owns($item->business_id);
    }

    public function delete(User $user, CatalogItem $item): bool
    {
        return $user->owns($item->business_id);
    }
}
