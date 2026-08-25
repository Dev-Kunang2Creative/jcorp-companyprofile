import ChapterHeading from '@/components/public/chapter-heading';

/**
 * Tentang — hilang seluruhnya bila `description` kosong (spec §3).
 * Penyaringannya di controller; komponen ini hanya dipanggil kalau ada isinya.
 *
 * Sejak arah A+B (23 Agustus 2026) teksnya tidak lagi dibungkus panel kaca,
 * melainkan disusun DUA KOLOM seperti kolom cetak. Alasannya bukan gaya:
 * baris sepanjang 90 karakter melelahkan dibaca, dan dua kolom memaksa
 * panjangnya turun ke sekitar 45 — rentang yang memang paling nyaman.
 *
 * Di bawah 768px kembali satu kolom: dua kolom di layar sempit
 * menghasilkan baris yang terlalu pendek dan patah-patah.
 */

type Props = {
    description: string;
    /** Nomor bab; urutannya berbeda per anak usaha (lihat ChapterHeading). */
    chapter: string;
};

export default function AboutSection({ description, chapter }: Props) {
    // Paragraf dipisah dari baris kosong ganda, supaya admin bisa menulis
    // beberapa paragraf di satu textarea tanpa perlu menulis HTML.
    const paragraphs = description
        .split(/\n\s*\n/)
        .map((p) => p.trim())
        .filter(Boolean);

    return (
        <section id="tentang" className="px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <ChapterHeading number={chapter} title="Tentang Kami" />

                <div className="grid gap-6 md:grid-cols-2 md:gap-9">
                    {paragraphs.map((paragraph, index) => (
                        <p
                            key={index}
                            className="reveal text-[15px] leading-[1.78] text-ink-soft"
                            style={{
                                transitionDelay: `${Math.min(index, 4) * 60}ms`,
                            }}
                        >
                            {paragraph}
                        </p>
                    ))}
                </div>
            </div>
        </section>
    );
}
