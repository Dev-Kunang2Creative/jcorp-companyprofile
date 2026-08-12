import WhatsappButton from '@/components/public/whatsapp-button';

/**
 * Hero — selalu dirender, minimal memuat nama usaha (spec §3).
 *
 * Gradasi menempel di sudut lewat --hero-gradient, bukan latar penuh; itu
 * yang membedakannya dari gradasi template. Motif bintang di lapisan atasnya
 * adalah benang merah dari taburan bintang di logo — opasitasnya sengaja
 * sangat rendah dan tidak boleh dinaikkan (DESIGN_SYSTEM §6).
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
            className="relative px-4 py-14 md:px-8 md:py-20"
            style={{ background: 'var(--hero-gradient)' }}
        >
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 opacity-50"
                style={{ backgroundImage: 'var(--star-motif)' }}
            />

            <div className="relative mx-auto max-w-6xl">
                <div className="glass-panel max-w-[660px] rounded-brand-lg p-6 md:p-8">
                    {logoUrl && (
                        <img
                            src={logoUrl}
                            alt={`Logo ${name}`}
                            className="mb-5 h-14 w-auto md:h-[74px]"
                        />
                    )}

                    {eyebrow && (
                        <p className="mb-3 text-[11px] font-medium tracking-[0.22em] text-gold-deep uppercase">
                            {eyebrow}
                        </p>
                    )}

                    <h1 className="font-display text-[31px] leading-[1.06] font-semibold tracking-[-0.015em] text-ink md:text-[46px]">
                        {name}
                    </h1>

                    <hr className="my-4 h-0.5 w-[52px] border-0 bg-gold" />

                    {tagline && (
                        <p className="mb-6 max-w-[42ch] text-[15px] leading-relaxed text-ink-soft md:text-[16.5px]">
                            {tagline}
                        </p>
                    )}

                    {whatsapp && (
                        <WhatsappButton
                            number={whatsapp}
                            businessName={name}
                            className="w-full md:w-auto"
                        >
                            Pesan lewat WhatsApp
                        </WhatsappButton>
                    )}
                </div>
            </div>
        </section>
    );
}
