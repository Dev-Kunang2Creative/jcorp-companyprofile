<?php

namespace App\Models;

use App\Policies\CatalogItemPolicy;
use Database\Factories\CatalogItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

/**
 * @property int $id
 * @property int $business_id
 * @property string $name
 * @property string|null $description
 * @property string|null $image_path
 * @property string|null $price
 * @property string|null $price_note
 * @property string|null $category
 * @property bool $is_available
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
// `business_id` sengaja TIDAK fillable. Kalau ikut terisi dari input form,
// seorang admin bisa memindahkan itemnya ke anak usaha lain hanya dengan
// menambah satu field di request. Diisi lewat relasi di controller.
#[Fillable([
    'name',
    'description',
    'image_path',
    'price',
    'price_note',
    'category',
    'is_available',
    'sort_order',
])]
#[UsePolicy(CatalogItemPolicy::class)]
class CatalogItem extends Model
{
    /** @use HasFactory<CatalogItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Penanda baris data contoh, disimpan di kolom `category`.
     *
     * Memakai kolom yang sudah ada, bukan kolom baru — supaya skema tabel
     * tidak membawa jejak sesuatu yang sifatnya sementara. Penandanya tidak
     * pernah tampil sebagai kategori di halaman publik, dan seluruh barisnya
     * dibersihkan dengan `php artisan jcorp:clear-samples`.
     */
    public const SAMPLE_MARKER = '__CONTOH__';

    /**
     * Kategori yang layak tampil. null untuk baris data contoh.
     */
    public function publicCategory(): ?string
    {
        return $this->category === self::SAMPLE_MARKER ? null : $this->category;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Angka harga saja: "Rp 150.000". Tanpa keterangan.
     *
     * Item tanpa harga mengembalikan null — ditampilkan tanpa baris harga
     * sama sekali, bukan "Rp 0".
     *
     * Desimal disembunyikan kalau harganya bulat (hampir selalu, karena harga
     * Rupiah tidak memakai sen) — "Rp 150.000", bukan "Rp 150.000,00". Kalau
     * ternyata ada pecahan, desimalnya tetap ditampilkan supaya tidak ada
     * angka yang hilang diam-diam karena pembulatan.
     */
    public function formattedAmount(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        $price = (float) $this->price;

        $amount = Number::currency(
            $price,
            in: 'IDR',
            locale: 'id',
            precision: fmod($price, 1.0) === 0.0 ? 0 : 2,
        );

        // ICU mengembalikan false kalau gagal memformat. Diperlakukan sama
        // dengan harga kosong — lebih baik barisnya tidak muncul sama sekali
        // daripada muncul kosong melompong di samping keterangan harga.
        return $amount === false ? null : $amount;
    }

    /**
     * Harga lengkap dalam satu baris: "mulai dari Rp 150.000".
     *
     * Dipakai di panel admin, yang menampilkannya sebagai teks ringkas.
     * Halaman publik memakai formattedAmount() dan `price_note` terpisah,
     * karena keterangannya ditaruh di baris kecil di atas angka
     * (DESIGN_SYSTEM §6).
     */
    public function formattedPrice(): ?string
    {
        $amount = $this->formattedAmount();

        if ($amount === null) {
            return null;
        }

        return $this->price_note
            ? "{$this->price_note} {$amount}"
            : $amount;
    }

    /**
     * Hanya item yang tampil di halaman publik.
     *
     * @param  Builder<CatalogItem>  $query
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true);
    }
}
