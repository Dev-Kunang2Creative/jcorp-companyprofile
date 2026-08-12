<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\PortfolioItem;
use App\Models\User;

/**
 * Pengecekan kepemilikan foto portfolio — aturan sama dengan katalog.
 * Lihat CatalogItemPolicy untuk penjelasannya.
 */
class PortfolioItemPolicy
{
    public function view(User $user, PortfolioItem $item): bool
    {
        return $user->owns($item->business_id);
    }

    public function create(User $user, Business $business): bool
    {
        return $user->owns($business->id);
    }

    public function update(User $user, PortfolioItem $item): bool
    {
        return $user->owns($item->business_id);
    }

    public function delete(User $user, PortfolioItem $item): bool
    {
        return $user->owns($item->business_id);
    }
}
