<?php

namespace App\Models;

use App\Enums\UserRole;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $password
 * @property UserRole $role
 * @property int|null $business_id
 * @property bool $is_active
 * @property string|null $invitation_token
 * @property CarbonImmutable|null $invitation_expires_at
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
// `role`, `business_id`, `is_active`, dan kolom undangan sengaja TIDAK
// fillable. Semuanya menentukan siapa boleh apa — kalau ikut terisi dari form
// biasa, seorang business_admin bisa menaikkan dirinya jadi super_admin atau
// mengaktifkan kembali akun yang dinonaktifkan, hanya dengan menambah satu
// field di request. Diisi lewat jalur khusus di controller dan artisan.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'invitation_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Berapa lama tautan undangan berlaku. */
    public const INVITATION_VALID_DAYS = 7;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'invitation_expires_at' => 'datetime',
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

    // ------------------------------------------------------------ undangan

    /**
     * Akun yang sudah dibuat tapi undangannya belum dibuka.
     *
     * Ditandai dari `password` yang masih null — bukan dari ada tidaknya
     * token, karena token dihapus begitu dipakai.
     */
    public function isPendingInvitation(): bool
    {
        return $this->password === null;
    }

    public function invitationIsExpired(): bool
    {
        return $this->invitation_expires_at !== null
            && $this->invitation_expires_at->isPast();
    }

    /**
     * Boleh masuk panel?
     *
     * Tiga syarat sekaligus: aktif, sudah punya password, dan tidak sedang
     * menunggu undangan. Dipakai middleware EnsureUserIsActive.
     */
    public function canAccessPanel(): bool
    {
        return $this->is_active && ! $this->isPendingInvitation();
    }

    /**
     * Status untuk ditampilkan di halaman Kelola Akun.
     */
    public function accountStatus(): string
    {
        if (! $this->is_active) {
            return 'nonaktif';
        }

        if ($this->isPendingInvitation()) {
            return $this->invitationIsExpired()
                ? 'undangan_kedaluwarsa'
                : 'menunggu_aktivasi';
        }

        return 'aktif';
    }

    /**
     * Hanya akun yang benar-benar memegang sebuah anak usaha.
     *
     * Akun nonaktif tidak dihitung — itu yang membuat anak usaha yang
     * adminnya dinonaktifkan bisa diundangkan admin baru.
     *
     * @param  Builder<User>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
