import ChapterHeading from '@/components/public/chapter-heading';

/**
 * Visi & Misi — hilang bila keduanya kosong (spec §3).
 *
 * Visi dan misi dijadikan satu section, bukan dua: keduanya menjawab
 * pertanyaan yang sama ("usaha ini mau ke mana"), dan memisahkannya membuat
 * halaman punya dua judul yang isinya masing-masing satu blok pendek.
 *
 * Sejak arah A+B, visi tampil sebagai KUTIPAN bergaris kiri dengan huruf
 * serif besar — satu-satunya blok teks di halaman yang diperlakukan
 * istimewa. Itu yang membuatnya terbaca sebagai pernyataan, bukan sekadar
 * paragraf lain.
 */

type Props = {
    vision: string | null;
    mission: string[] | null;
    chapter: string;
};

export default function VisionMissionSection({
    vision,
    mission,
    chapter,
}: Props) {
    const hasMission = Boolean(mission?.length);

    return (
        <section id="visi-misi" className="px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <ChapterHeading number={chapter} title="Visi & Misi" />

                {/* Dua kolom di desktop supaya misi yang panjang tidak
                    mendorong visi jauh ke atas layar. Kolom visi sengaja
                    lebih sempit — kutipan tidak boleh selebar daftar. */}
                <div className="grid gap-9 md:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] md:gap-12">
                    {vision && (
                        <div className="reveal">
                            <p className="mb-4 text-[10.5px] font-medium tracking-[0.16em] text-brass uppercase">
                                Visi
                            </p>

                            <blockquote className="border-l-2 border-[var(--unit-accent)] pl-6">
                                <p className="font-display text-[21px] leading-[1.44] font-normal tracking-[-0.015em] text-ink md:text-[26px]">
                                    {vision}
                                </p>
                            </blockquote>
                        </div>
                    )}

                    {hasMission && (
                        <div
                            className="reveal"
                            style={{ transitionDelay: '80ms' }}
                        >
                            <p className="mb-4 text-[10.5px] font-medium tracking-[0.16em] text-brass uppercase">
                                Misi
                            </p>

                            <ol className="space-y-3.5">
                                {mission?.map((item, index) => (
                                    <li
                                        key={index}
                                        className="flex gap-4 text-[15px] leading-[1.72] text-ink-soft"
                                    >
                                        {/* Nomor dibuat sendiri, bukan penanda
                                            bawaan <ol> — supaya bisa diberi
                                            warna aksen dan lebarnya tetap sama
                                            saat teksnya membungkus.

                                            Penomorannya juga DIBUAT ULANG di
                                            sini: materi Nail's by Me menomori
                                            1, 3, 4 (nomor 2 memang tidak ada),
                                            dan lompatan itu tidak perlu ikut
                                            tampil. */}
                                        <span
                                            aria-hidden="true"
                                            className="mt-px w-5 shrink-0 font-display text-[15px] text-[var(--unit-accent)] tabular-nums"
                                        >
                                            {index + 1}.
                                        </span>
                                        <span>{item}</span>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
