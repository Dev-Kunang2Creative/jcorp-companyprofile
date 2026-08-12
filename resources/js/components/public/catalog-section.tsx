import CatalogCard from '@/components/public/catalog-card';
import type { PublicCatalogItem } from '@/types/public';

/**
 * Katalog — hilang seluruhnya bila tidak ada item yang tampil, termasuk saat
 * semua item disembunyikan sementara (spec §3). Penyaringannya di controller.
 *
 * Label sectionnya menyesuaikan per anak usaha: "Menu Kami", "Layanan &
 * Harga", "Unit Tersedia" — dari kolom database, bukan lima layout berbeda.
 */

type Props = {
    label: string;
    items: PublicCatalogItem[];
    businessName: string;
    whatsapp?: string;
};

export default function CatalogSection({
    label,
    items,
    businessName,
    whatsapp,
}: Props) {
    return (
        <section
            id="katalog"
            className="px-4 py-12 md:px-8 md:py-16"
            style={{
                background:
                    'linear-gradient(180deg, var(--color-cream) 0%, #FFFFFF 100%)',
            }}
        >
            <div className="mx-auto max-w-6xl">
                <h2 className="mb-6 font-display text-2xl leading-tight font-semibold text-ink md:text-[30px]">
                    {label}
                </h2>

                {/* items-stretch memaksa setiap kartu setinggi selnya, yang
                    membuat `h-full` di kartu bekerja dan tombol "Tanya"
                    sejajar di satu baris. Ini bawaan grid CSS, tapi ditulis
                    eksplisit supaya tidak hilang tanpa sengaja. */}
                <div className="grid grid-cols-2 items-stretch gap-3 md:grid-cols-3 md:gap-4">
                    {items.map((item) => (
                        <CatalogCard
                            key={item.id}
                            item={item}
                            businessName={businessName}
                            whatsapp={whatsapp}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}
