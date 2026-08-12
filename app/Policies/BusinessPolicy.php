<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function view(User $user, Business $business): bool
    {
        return $user->owns($business->id);
    }

    /**
     * Mengubah info kontak, tagline, dan label section (spec §6).
     */
    public function update(User $user, Business $business): bool
    {
        return $user->owns($business->id);
    }

    /**
     * Sakelar terbit, slug, dan menambah/menghapus anak usaha — super-admin
     * saja. Business_admin yang bisa menerbitkan anak usahanya sendiri berarti
     * bisa menayangkan halaman setengah jadi tanpa persetujuan siapa pun.
     */
    public function manage(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
