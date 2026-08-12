<?php

namespace App\Models;

use App\Policies\BusinessPolicy;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $logo_path
 * @property string $catalog_label
 * @property string $portfolio_label
 * @property bool $has_portfolio
 * @property string|null $whatsapp
 * @property string|null $instagram
 * @property string|null $tiktok
 * @property string|null $address
 * @property string|null $business_hours
 * @property bool $is_parent
 * @property bool $is_published
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
// `is_published`, `is_parent`, dan `slug` sengaja TIDAK fillable: ketiganya
// menentukan apa yang terlihat publik dan alamatnya. Diubah lewat method
// khusus di controller super-admin, bukan ikut terisi dari form biasa.
#[Fillable([
    'name',
    'tagline',
    'description',
    'logo_path',
    'catalog_label',
    'portfolio_label',
    'whatsapp',
    'instagram',
    'tiktok',
    'address',
    'business_hours',
    'sort_order',
])]
#[UsePolicy(BusinessPolicy::class)]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Slug dipakai sebagai kunci route publik: /sweetness-things.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_parent' => 'boolean',
            'is_published' => 'boolean',
            'has_portfolio' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<CatalogItem, $this> */
    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }

    /** @return HasMany<PortfolioItem, $this> */
    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    /** @return HasMany<User, $this> */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Hanya yang sudah diterbitkan — dipakai di seluruh halaman publik.
     *
     * @param  Builder<Business>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Lima anak usaha, tanpa induk.
     *
     * @param  Builder<Business>  $query
     */
    public function scopeSubsidiaries(Builder $query): void
    {
        $query->where('is_parent', false);
    }
}
