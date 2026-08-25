import CatalogCard from '@/components/public/catalog-card';
import ChapterHeading from '@/components/public/chapter-heading';
import PriceList from '@/components/public/price-list';
import type { PublicCatalogItem } from '@/types/public';

/**
 * Katalog — hilang seluruhnya bila tidak ada item yang tampil, termasuk saat
 * semua item disembunyikan sementara (spec §3). Penyaringannya di controller.
 *
 * Label sectionnya menyesuaikan per anak usaha: "Menu Kami", "Layanan &
 * Harga", "Unit Tersedia" — dari kolom database, bukan lima layout berbeda.
 *
 * DUA BENTUK TAMPILAN
 * -------------------
 * Grid kartu kalau ada item yang berfoto, daftar harga kalau tidak ada sama
 * sekali. Alasannya: kartu punya area gambar 4:3, dan lima belas layanan
 * tanpa foto berarti lima belas kotak inisial memenuhi layar sementara
 * harganya — yang paling dicari pengunjung — justru tenggelam.
 *
 * Penentuannya dari data, bukan setelan per anak usaha: begitu satu foto
 * diunggah lewat panel, tampilannya kembali jadi grid dengan sendirinya.
 */

type Props = {
    label: string;
    items: PublicCatalogItem[];
    businessName: string;
    whatsapp?: string;
    /** Keterangan yang berlaku untuk seluruh item ("sudah termasuk …"). */
    note?: string | null;
    chapter: string;
};

export default function CatalogSection({
    label,
    items,
    businessName,
    whatsapp,
    note,
    chapter,
}: Props) {
    const hasAnyImage = items.some((item) => item.image_url);

    return (
        // Tanpa latar sendiri: aurora di <body> yang terlihat di celah antar
        // kartu, dan itu yang dibiaskan permukaan kaca tiap kartu.
        <section id="katalog" className="px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <ChapterHeading number={chapter} title={label} />

                {hasAnyImage ? (
                    /* items-stretch memaksa setiap kartu setinggi selnya, yang
                       membuat `h-full` di kartu bekerja dan tombol "Tanya"
                       sejajar di satu baris. Ini bawaan grid CSS, tapi ditulis
                       eksplisit supaya tidak hilang tanpa sengaja. */
                    <div className="grid grid-cols-2 items-stretch gap-0 md:grid-cols-3">
                        {items.map((item) => (
                            <CatalogCard
                                key={item.id}
                                item={item}
                                businessName={businessName}
                                whatsapp={whatsapp}
                            />
                        ))}
                    </div>
                ) : (
                    <PriceList
                        items={items}
                        businessName={businessName}
                        whatsapp={whatsapp}
                        note={note}
                    />
                )}
            </div>
        </section>
    );
}
