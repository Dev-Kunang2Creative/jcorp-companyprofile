import { useState } from 'react';
import WhatsappButton from '@/components/public/whatsapp-button';
import type { PublicCatalogItem } from '@/types/public';

/**
 * Kartu katalog — garis rambut, bukan kaca (arah A+B, 23 Agustus 2026).
 *
 * Perpindahan ini juga menyelesaikan masalah biaya yang dulu ditangani
 * setengah-setengah: `backdrop-filter` dihitung ulang setiap kali halaman
 * digulir, dan dengan 20 kartu di layar bebannya terasa di HP kelas bawah.
 * Dulu ditahan dengan mengecilkan blur (12px, lalu 8px di layar sempit);
 * sekarang biayanya nol karena tidak ada blur sama sekali.
 *
 * PERATAAN TOMBOL
 * ---------------
 * Tombol "Tanya" harus sejajar di seluruh kartu dalam satu baris grid. Tanpa
 * penanganan khusus, tingginya berbeda-beda karena tiga hal:
 *
 *   1. Nama item bisa satu atau dua baris ("Lepas Gel & Perawatan")
 *   2. Keterangan harga tidak selalu ada ("mulai dari" vs harga polos)
 *   3. Sebagian item memang tanpa harga — di Ayodya Logistic, 4 dari 5
 *
 * Diselesaikan dengan: kartu jadi kolom setinggi selnya, blok isi memanjang
 * (`flex-1`), lalu blok harga+tombol didorong ke dasar (`mt-auto`). Kedua
 * baris harga selalu memesan tinggi walau kosong, supaya angka harga pun
 * ikut sejajar antar kartu.
 */

type Props = {
    item: PublicCatalogItem;
    businessName: string;
    whatsapp?: string;
};

export default function CatalogCard({ item, businessName, whatsapp }: Props) {
    const [imageFailed, setImageFailed] = useState(false);

    const showFallback = !item.image_url || imageFailed;

    // Item tanpa harga tapi berketerangan ("hubungi kami") menaikkan
    // keterangannya ke baris utama, supaya tidak ada kartu yang blok
    // harganya kosong melompong.
    const mainLine = item.formatted_price ?? item.price_note;
    const noteLine = item.formatted_price ? item.price_note : null;

    return (
        <article className="hairline-card flex h-full flex-col overflow-hidden">
            {showFallback ? (
                // Kotak inisial, bukan ikon rusak (spec §10).
                <div
                    className="flex aspect-[4/3] shrink-0 items-center justify-center border-b border-hair-soft bg-wash font-display text-[26px] text-[var(--unit-accent)]"
                    aria-hidden="true"
                >
                    {item.initials}
                </div>
            ) : (
                <img
                    src={item.image_url ?? undefined}
                    alt={item.name}
                    loading="lazy"
                    onError={() => setImageFailed(true)}
                    className="aspect-[4/3] w-full shrink-0 border-b border-hair-soft bg-wash object-cover"
                />
            )}

            <div className="flex flex-1 flex-col p-3 md:p-4">
                {item.category && (
                    <p className="mb-1.5 text-[10px] font-medium tracking-[0.14em] text-[var(--unit-accent)] uppercase">
                        {item.category}
                    </p>
                )}

                <h3 className="mb-1.5 font-display text-[16px] font-normal tracking-[-0.01em] text-ink md:text-[18px]">
                    {item.name}
                </h3>

                {item.description && (
                    // Disembunyikan di mobile: di kolom sempit jadi terlalu
                    // padat dan menenggelamkan harga (DESIGN_SYSTEM §7).
                    <p className="mb-3 hidden text-[13.5px] leading-relaxed text-ink-soft md:block">
                        {item.description}
                    </p>
                )}

                {/* mt-auto mendorong blok ini ke dasar kartu, berapa pun
                    panjang nama itemnya di atas. */}
                <div className="mt-auto flex flex-col items-stretch gap-2 pt-3 md:flex-row md:items-end md:justify-between">
                    <div className="min-w-0">
                        {/* Kedua baris selalu memesan tingginya walau kosong.
                            Kalau tidak, harga di kartu bersebelahan ikut
                            bergeser naik-turun. */}
                        <span
                            aria-hidden={noteLine ? undefined : 'true'}
                            className="block min-h-4 text-xs leading-4 font-normal text-ink-soft"
                        >
                            {noteLine ?? ' '}
                        </span>

                        <span
                            aria-hidden={mainLine ? undefined : 'true'}
                            className="block min-h-5 text-[14.5px] leading-5 font-medium text-[var(--unit-accent)]"
                        >
                            {mainLine ?? ' '}
                        </span>
                    </div>

                    {whatsapp && (
                        <WhatsappButton
                            number={whatsapp}
                            businessName={businessName}
                            itemName={item.name}
                            variant="outline"
                            className="shrink-0"
                        >
                            Tanya
                        </WhatsappButton>
                    )}
                </div>
            </div>
        </article>
    );
}
