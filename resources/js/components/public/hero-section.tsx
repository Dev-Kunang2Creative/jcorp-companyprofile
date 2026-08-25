import WhatsappButton from '@/components/public/whatsapp-button';

/**
 * Hero — selalu dirender, minimal memuat nama usaha (spec §3).
 *
 * Sejak arah A+B (23 Agustus 2026) hero tidak lagi berupa panel kaca di
 * atas gradasi. Judulnya berdiri langsung di atas putih, rata kiri,
 * dengan ruang kosong lebar di sekelilingnya.
 *
 * Yang menggantikan gradasi sebagai penarik perhatian: UKURAN. Judul
 * hero desktop 78px — hampir dua kali judul section. Halaman perusahaan
 * tidak perlu ornamen untuk terasa berwibawa; cukup satu hal yang jelas
 * paling penting di layar.
 */

type Props = {
    name: string;
    tagline: string | null;
    logoUrl: string | null;
    whatsapp?: string;
    /** Label bidang usaha di atas judul. Berbeda per anak usaha. */
    eyebrow?: string;
};

export default function HeroSection({
    name,
    tagline,
    logoUrl,
    whatsapp,
    eyebrow,
}: Props) {
    return (
        <section
            id="top"
            className="px-4 pt-14 pb-12 md:px-8 md:pt-24 md:pb-16"
        >
            <div className="mx-auto max-w-6xl">
                {logoUrl && (
                    <img
                        src={logoUrl}
                        alt={`Logo ${name}`}
                        className="reveal mb-8 h-12 w-auto md:h-16"
                    />
                )}

                <p
                    className="reveal mb-5 text-[10.5px] font-medium tracking-[0.2em] text-brass uppercase"
                    style={{ transitionDelay: '60ms' }}
                >
                    {eyebrow ?? 'Profil Usaha'}
                </p>

                <h1
                    className="reveal max-w-[13ch] font-display text-[40px] leading-[0.99] font-normal tracking-[-0.028em] text-ink md:text-[78px]"
                    style={{ transitionDelay: '120ms' }}
                >
                    {name}
                </h1>

                {tagline && (
                    <p
                        className="reveal mt-7 max-w-[45ch] text-[16px] leading-[1.7] text-ink-soft md:text-[17px]"
                        style={{ transitionDelay: '180ms' }}
                    >
                        {tagline}
                    </p>
                )}

                {whatsapp && (
                    <div
                        className="reveal mt-9"
                        style={{ transitionDelay: '240ms' }}
                    >
                        <WhatsappButton
                            number={whatsapp}
                            businessName={name}
                            className="w-full md:w-auto"
                        >
                            Hubungi lewat WhatsApp
                        </WhatsappButton>
                    </div>
                )}
            </div>
        </section>
    );
}
