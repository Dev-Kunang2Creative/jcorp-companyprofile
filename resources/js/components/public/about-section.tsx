/**
 * Tentang — hilang seluruhnya bila `description` kosong (spec §3).
 * Penyaringannya di controller; komponen ini hanya dipanggil kalau ada isinya.
 */

type Props = {
    description: string;
};

export default function AboutSection({ description }: Props) {
    // Paragraf dipisah dari baris kosong ganda, supaya admin bisa menulis
    // beberapa paragraf di satu textarea tanpa perlu menulis HTML.
    const paragraphs = description
        .split(/\n\s*\n/)
        .map((p) => p.trim())
        .filter(Boolean);

    return (
        <section id="tentang" className="bg-white px-4 py-12 md:px-8 md:py-16">
            <div className="mx-auto max-w-6xl">
                <h2 className="mb-6 font-display text-2xl leading-tight font-semibold text-ink md:text-[30px]">
                    Tentang Kami
                </h2>

                <div className="max-w-[52ch] space-y-4">
                    {paragraphs.map((paragraph, index) => (
                        <p
                            key={index}
                            className="text-[15px] leading-[1.75] text-ink-soft md:text-[15.5px]"
                        >
                            {paragraph}
                        </p>
                    ))}
                </div>
            </div>
        </section>
    );
}
