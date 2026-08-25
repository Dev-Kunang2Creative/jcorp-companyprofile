import ChapterHeading from '@/components/public/chapter-heading';
import { stagger } from '@/hooks/use-reveal';
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
    chapter: string;
};

export default function PortfolioSection({ label, items, chapter }: Props) {
    return (
        <section id="portfolio" className="px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <ChapterHeading number={chapter} title={label} />

                <ul className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                    {items.map((item, index) => (
                        <li
                            key={item.id}
                            className="reveal"
                            style={stagger(index)}
                        >
                            <a
                                href={item.full_url ?? undefined}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="block overflow-hidden border border-hair-soft bg-wash p-1 transition-[transform,border-color] duration-300 hover:-translate-y-0.5 hover:border-hair"
                            >
                                <img
                                    src={item.image_url ?? undefined}
                                    // Keterangan jadi alt bila ada; kalau tidak,
                                    // alt tetap bermakna, bukan nama berkas.
                                    alt={item.caption ?? `Hasil kerja ${label}`}
                                    loading="lazy"
                                    className="aspect-square w-full bg-wash object-cover"
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
