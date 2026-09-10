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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $vision
 * @property list<string>|null $mission
 * @property list<array{title: string, description: string}>|null $featured_services
 * @property list<array{title: string, description: string}>|null $services
 * @property string|null $services_label
 * @property list<array{title: string, description: string}>|null $highlights
 * @property string|null $logo_path
 * @property string|null $cover_image_path
 * @property string|null $accent_color
 * @property string $catalog_label
 * @property string|null $catalog_note
 * @property string $portfolio_label
 * @property bool $has_portfolio
 * @property string|null $whatsapp
 * @property string|null $whatsapp_label
 * @property string|null $whatsapp_alt
 * @property string|null $whatsapp_alt_label
 * @property string|null $instagram
 * @property string|null $tiktok
 * @property list<array{label: string, username: string}>|null $unit_socials
 * @property string|null $address
 * @property string|null $business_hours
 * @property string|null $contact_note
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
//
// `vision`, `mission`, `services`, dan `highlights` juga tidak fillable —
// perlakuannya disamakan dengan `description`: teks panjang yang jarang
// berubah, diisi lewat seeder saat materi client masuk, bukan lewat form.
#[Fillable([
    'name',
    'tagline',
    'description',
    'logo_path',
    'cover_image_path',
    'catalog_label',
    'catalog_note',
    'portfolio_label',
    'whatsapp',
    'whatsapp_label',
    'whatsapp_alt',
    'whatsapp_alt_label',
    'instagram',
    'tiktok',
    'address',
    'business_hours',
    'contact_note',
    'sort_order',
])]
#[UsePolicy(BusinessPolicy::class)]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Kuningan induk — dipakai anak usaha yang belum punya aksen sendiri.
     */
    public const DEFAULT_ACCENT = '#7C5F33';

    /**
     * Warna aksen yang AMAN dipasang di atribut `style`.
     *
     * Nilainya berasal dari database dan berakhir di HTML sebagai CSS
     * custom property. Tanpa pemeriksaan bentuk, kolom ini jadi jalan
     * masuk untuk menyuntikkan CSS — sebuah nilai seperti
     * `red; background: url(...)` akan lolos begitu saja.
     *
     * Karena itu bentuknya dipaksa: pagar, lalu tepat enam digit heksa.
     * Yang tidak cocok tidak "diperbaiki", melainkan diganti kuningan
     * induk — lebih baik warnanya salah daripada halamannya jadi celah.
     */
    /**
     * Ikon tab browser (favicon) untuk halaman usaha ini.
     *
     * Diturunkan dari jalur logonya, bukan disimpan di kolom terpisah —
     * berkas lencana selalu dibuat bersamaan dengan logonya oleh skrip
     * di `docs/scripts/`, dengan pola nama yang sama persis:
     *
     *   images/brand/ngelash-logo-600.webp  ->  ngelash-badge-48.png
     *
     * Kolom kedua berarti dua daftar yang harus dijaga tetap seiring,
     * dan cepat atau lambat keduanya berbeda.
     *
     * null kalau logonya belum ada atau bentuk namanya tidak dikenali —
     * halaman itu memakai favicon bawaan J-Corporate.
     */
    public function faviconPath(): ?string
    {
        $logo = (string) $this->logo_path;

        if (preg_match('#^images/brand/([a-z0-9-]+)-logo-\d+\.\w+$#', $logo, $m) !== 1) {
            return null;
        }

        $badge = "images/brand/{$m[1]}-badge-48.png";

        // Diperiksa keberadaannya: nama yang cocok pola belum tentu
        // berkasnya ada, dan favicon yang menunjuk ke berkas hilang
        // membuat browser menampilkan ikon kosong — lebih buruk daripada
        // memakai ikon induk.
        return is_file(public_path($badge)) ? $badge : null;
    }

    public function safeAccentColor(): string
    {
        $value = (string) $this->accent_color;

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1
            ? $value
            : self::DEFAULT_ACCENT;
    }

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
            'mission' => 'array',
            'featured_services' => 'array',
            'services' => 'array',
            'highlights' => 'array',
            'unit_socials' => 'array',
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

    /** @return HasOne<PortfolioItem, $this> */
    public function featuredPortfolioItem(): HasOne
    {
        return $this->hasOne(PortfolioItem::class)
            ->where('is_featured_on_home', true)
            ->orderBy('sort_order')
            ->orderBy('id');
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
