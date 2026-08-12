<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property UserRole $role
 * @property int|null $business_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
// `role` dan `business_id` sengaja TIDAK fillable. Keduanya menentukan apa
// yang boleh disentuh seorang admin — kalau ikut terisi dari form biasa,
// seorang business_admin bisa menaikkan dirinya jadi super_admin hanya
// dengan menambah satu field di request. Diisi lewat perintah artisan.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Anak usaha yang dipegang. null untuk super_admin.
     *
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Apakah akun ini berhak menyentuh data milik anak usaha tertentu.
     *
     * Super-admin boleh apa saja. Business_admin hanya boleh yang cocok
     * dengan `business_id` miliknya — dan tidak pernah boleh kalau
     * `business_id`-nya sendiri kosong, supaya akun yang datanya tidak
     * lengkap tidak diam-diam berubah jadi punya akses ke mana-mana.
     */
    public function owns(?int $businessId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->business_id !== null && $this->business_id === $businessId;
    }
}
