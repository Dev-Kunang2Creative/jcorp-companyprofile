import type { PublicPortfolioItem } from '@/types/public';

/**
 * Portfolio — hilang bila tidak ada foto (spec §3).
 *
 * Rasio 1:1 sesuai DESIGN_SYSTEM §8. Gambar dibungkus tautan ke versi
 * ukuran penuh, supaya pengunjung bisa melihat detail hasil kerja.
 */

type Props = {
    label: string;
    items: PublicPortfolioItem[];
};

export default function PortfolioSection({ label, items }: Props) {
    return (
        <section
            id="portfolio"
            className="bg-white px-4 py-12 md:px-8 md:py-16"
        >
            <div className="mx-auto max-w-6xl">
                <h2 className="mb-6 font-display text-2xl leading-tight font-semibold text-ink md:text-[30px]">
                    {label}
                </h2>

                <ul className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                    {items.map((item) => (
                        <li key={item.id}>
                            <a
                                href={item.full_url ?? undefined}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="block overflow-hidden rounded-brand-md border border-line"
                            >
                                <img
                                    src={item.image_url ?? undefined}
                                    // Keterangan jadi alt bila ada; kalau tidak,
                                    // alt tetap bermakna, bukan nama berkas.
                                    alt={item.caption ?? `Hasil kerja ${label}`}
                                    loading="lazy"
                                    className="aspect-square w-full bg-[#F7F1E4] object-cover"
                                />
                            </a>

                            {item.caption && (
                                <p className="mt-2 text-[13px] leading-snug text-ink-soft">
                                    {item.caption}
                                </p>
                            )}
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
