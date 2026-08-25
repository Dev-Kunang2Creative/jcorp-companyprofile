import ChapterHeading from '@/components/public/chapter-heading';
import { stagger } from '@/hooks/use-reveal';
import type { PublicDetail } from '@/types/public';

/**
 * Keunggulan — hilang bila kosong (spec §3).
 *
 * Kartu garis rambut, bukan panel kaca. Isinya potongan pendek yang
 * dipindai sekilas, bukan paragraf yang dibaca — dan kisi bergaris
 * membuat perbandingan antar butir jadi mudah.
 */

type Props = {
    items: PublicDetail[];
    businessName: string;
    chapter: string;
};

export default function HighlightsSection({
    items,
    businessName,
    chapter,
}: Props) {
    // Jumlah kolom menyesuaikan banyaknya keunggulan, supaya baris terakhir
    // tidak menyisakan satu kartu yang terlihat seperti sisa.
    //
    // Empat butir (Sweetness) → 4 kolom, satu baris penuh.
    // Tujuh butir (ngelash) → 3 kolom, jadi 3+3+1.
    const columns =
        items.length % 4 === 0 ? 'lg:grid-cols-4' : 'lg:grid-cols-3';

    return (
        <section id="keunggulan" className="px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <ChapterHeading
                    number={chapter}
                    title={`Mengapa memilih ${businessName}`}
                />

                {/* gap-0 disengaja: kartu dirapatkan jadi satu kisi utuh,
                    dan margin negatif di .hairline-card mencegah garis antar
                    kartu menebal jadi dua piksel. */}
                <ul
                    className={`grid grid-cols-1 items-stretch gap-0 sm:grid-cols-2 ${columns}`}
                >
                    {items.map((item, index) => (
                        <li
                            key={index}
                            className="reveal hairline-card h-full p-6 md:p-7"
                            style={stagger(index)}
                        >
                            <span
                                aria-hidden="true"
                                className="mb-4 block font-display text-[13px] tracking-[0.08em] text-[var(--unit-accent)] tabular-nums"
                            >
                                {String(index + 1).padStart(2, '0')}
                            </span>

                            <h3 className="mb-2 font-display text-[19px] leading-snug font-normal tracking-[-0.015em] text-ink">
                                {item.title}
                            </h3>

                            <p className="text-[13.5px] leading-[1.65] text-ink-soft">
                                {item.description}
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
