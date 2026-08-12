import WhatsappButton from '@/components/public/whatsapp-button';
import { formatWhatsappDisplay } from '@/lib/whatsapp';
import type { PublicContact } from '@/types/public';

/**
 * Kontak — hilang bila SELURUH kolom kontak kosong (spec §3).
 *
 * Salah satu dari tiga panel kaca yang diizinkan per layar.
 * Baris yang kolomnya kosong tidak ikut dirender.
 */

type Props = {
    contact: PublicContact;
    businessName: string;
};

export default function ContactSection({ contact, businessName }: Props) {
    const rows: Array<{ label: string; value: string; href?: string }> = [];

    if (contact.whatsapp) {
        rows.push({
            label: 'WhatsApp',
            value: formatWhatsappDisplay(contact.whatsapp),
        });
    }

    if (contact.instagram) {
        rows.push({
            label: 'Instagram',
            value: `@${contact.instagram}`,
            href: `https://instagram.com/${contact.instagram}`,
        });
    }

    if (contact.tiktok) {
        rows.push({
            label: 'TikTok',
            value: `@${contact.tiktok}`,
            href: `https://tiktok.com/@${contact.tiktok}`,
        });
    }

    if (contact.address) {
        rows.push({ label: 'Alamat', value: contact.address });
    }

    if (contact.business_hours) {
        rows.push({ label: 'Jam Buka', value: contact.business_hours });
    }

    return (
        <section
            id="kontak"
            className="px-4 py-12 pb-16 md:px-8 md:py-16 md:pb-24"
            style={{ background: 'var(--contact-gradient)' }}
        >
            <div className="mx-auto max-w-6xl">
                <div className="glass-panel max-w-[720px] rounded-brand-lg p-6 md:p-8">
                    <h2 className="mb-2 font-display text-2xl leading-tight font-semibold text-ink md:text-[30px]">
                        Pesan &amp; Kunjungi
                    </h2>

                    {contact.whatsapp && (
                        <p className="text-[15px] leading-relaxed text-ink-soft">
                            Paling cepat lewat WhatsApp. Balasan biasanya di jam
                            buka.
                        </p>
                    )}

                    <dl className="my-6 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-x-8">
                        {rows.map((row) => (
                            <div key={row.label}>
                                <dt className="mb-0.5 text-[11px] font-medium tracking-[0.16em] text-gold-deep uppercase">
                                    {row.label}
                                </dt>
                                <dd className="text-[15px] text-ink">
                                    {row.href ? (
                                        <a
                                            href={row.href}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="underline decoration-gold underline-offset-4 hover:text-gold-deep"
                                        >
                                            {row.value}
                                        </a>
                                    ) : (
                                        row.value
                                    )}
                                </dd>
                            </div>
                        ))}
                    </dl>

                    {contact.whatsapp && (
                        <WhatsappButton
                            number={contact.whatsapp}
                            businessName={businessName}
                            className="w-full md:w-auto"
                        >
                            Chat sekarang
                        </WhatsappButton>
                    )}
                </div>
            </div>
        </section>
    );
}
