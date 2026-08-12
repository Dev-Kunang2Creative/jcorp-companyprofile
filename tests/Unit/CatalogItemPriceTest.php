<?php

namespace Tests\Unit;

use App\Models\CatalogItem;
use PHPUnit\Framework\TestCase;

/**
 * Spec §4 dan §11: harga disimpan sebagai angka, keterangannya terpisah,
 * lalu digabung jadi satu baris siap tampil.
 */
class CatalogItemPriceTest extends TestCase
{
    /**
     * ICU menyisipkan non-breaking space (U+00A0) antara "Rp" dan angkanya,
     * bukan spasi biasa. Ini memang disengaja — mencegah "Rp" terpisah dari
     * angkanya saat teks berganti baris di layar sempit. Dibuat eksplisit di
     * sini supaya test tidak terbaca seolah ada bug.
     */
    private const NBSP = "\u{00A0}";

    private function item(?string $price, ?string $note = null): CatalogItem
    {
        $item = new CatalogItem;
        $item->price = $price;
        $item->price_note = $note;

        return $item;
    }

    public function test_it_combines_the_note_and_the_amount(): void
    {
        $this->assertSame(
            'mulai dari Rp'.self::NBSP.'150.000',
            $this->item('150000', 'mulai dari')->formattedPrice(),
        );
    }

    public function test_it_shows_the_amount_alone_when_there_is_no_note(): void
    {
        $this->assertSame('Rp'.self::NBSP.'150.000', $this->item('150000')->formattedPrice());
    }

    public function test_it_hides_decimals_for_whole_amounts(): void
    {
        // Harga Rupiah praktis tidak pernah memakai sen — "Rp 150.000,00"
        // terbaca janggal.
        $this->assertSame(
            'Rp'.self::NBSP.'2.500.000.000',
            $this->item('2500000000')->formattedPrice(),
        );
    }

    public function test_it_keeps_decimals_when_the_amount_actually_has_them(): void
    {
        // Kalau ada pecahan, ditampilkan apa adanya — bukan dibulatkan
        // diam-diam sehingga ada angka yang hilang.
        $this->assertSame(
            'Rp'.self::NBSP.'150.000,50',
            $this->item('150000.50')->formattedPrice(),
        );
    }

    public function test_an_item_without_a_price_has_no_price_line_at_all(): void
    {
        // Bukan "Rp 0" — item tanpa harga tampil tanpa baris harga (spec §4).
        $this->assertNull($this->item(null)->formattedPrice());
        $this->assertNull($this->item(null, 'mulai dari')->formattedPrice());
    }

    public function test_a_zero_price_is_still_shown(): void
    {
        // Berbeda dari null: harga nol adalah keputusan sadar ("gratis"),
        // jadi tetap ditampilkan.
        $this->assertSame('Rp'.self::NBSP.'0', $this->item('0')->formattedPrice());
    }
}
