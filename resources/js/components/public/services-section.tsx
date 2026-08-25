import ChapterHeading from '@/components/public/chapter-heading';
import { stagger } from '@/hooks/use-reveal';
import type { PublicDetail } from '@/types/public';

/**
 * Layanan — hilang bila kosong (spec §3).
 *
 * Bedanya dari Keunggulan: yang ini menjelaskan APA yang dikerjakan atau
 * BAGAIMANA prosesnya, bukan alasan memilih.
 *
 * Dipakai dua kali di halaman yang sama pada sebagian anak usaha: sekali
 * untuk "Layanan Unggulan" (apa yang dijual), sekali untuk "Layanan Kami"
 * (bagaimana prosesnya). Karena itu judul dan id-nya bisa disesuaikan.
 */

type Props = {
    items: PublicDetail[];
    /** Bawaannya "Cara Pemesanan" — sebagian usaha menyebutnya lain. */
    title?: string;
    /** Sasaran tautan navigasi. Harus unik bila komponen dipakai dua kali. */
    id?: string;
    chapter: string;
};

export default function ServicesSection({
    items,
    title = 'Cara Pemesanan',
    id = 'layanan',
    chapter,
}: Props) {
    // Tiga kolom kalau isinya tiga, selain itu dua — supaya baris terakhir
    // tidak menyisakan satu kartu sendirian. Tidak pernah lebih dari tiga:
    // kartu layanan isinya kalimat penjelas yang lebih panjang dari kartu
    // keunggulan, dan di kolom sempit jadi terlalu padat (DESIGN_SYSTEM §7).
    const columns =
        items.length % 3 === 0 ? 'md:grid-cols-3' : 'md:grid-cols-2';

    return (
        <section id={id} className="px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <ChapterHeading number={chapter} title={title} />

                <ul
                    className={`grid grid-cols-1 items-stretch gap-0 ${columns}`}
                >
                    {items.map((item, index) => (
                        <li
                            key={index}
                            className="reveal hairline-card h-full p-6 md:p-8"
                            style={stagger(index)}
                        >
                            <h3 className="font-display text-[20px] leading-snug font-normal tracking-[-0.015em] text-ink md:text-[22px]">
                                {item.title}
                            </h3>

                            <hr className="my-4 h-px w-9 border-0 bg-[var(--unit-accent)]" />

                            <p className="text-[14px] leading-[1.72] text-ink-soft">
                                {item.description}
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
