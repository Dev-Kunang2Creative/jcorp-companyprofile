import ChapterHeading from '@/components/public/chapter-heading';
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
    /**
     * Judul section. Bawaannya "Pesan & Kunjungi" — tepat untuk anak usaha
     * yang menjual sesuatu, tapi keliru untuk induk yang tidak berjualan
     * dan tidak menerima kunjungan.
     */
    title?: string;
    chapter: string;
};

export default function ContactSection({
    contact,
    businessName,
    title = 'Pesan & Kunjungi',
    chapter,
}: Props) {
    const rows: Array<{ label: string; value: string; href?: string }> = [];

    if (contact.whatsapp) {
        rows.push({
            // Nama pemiliknya dipakai kalau ada ("WhatsApp (Sekar)") —
            // pengunjung tahu siapa yang dihubungi sebelum mengirim pesan.
            //
            // Tanpa nama, penomoran hanya muncul kalau memang ada nomor
            // kedua: "WhatsApp 1" sendirian terbaca seolah ada nomor lain
            // yang tidak ditampilkan.
            label: contact.whatsapp_label
                ? `WhatsApp (${contact.whatsapp_label})`
                : contact.whatsapp_alt
                  ? 'WhatsApp 1'
                  : 'WhatsApp',
            value: formatWhatsappDisplay(contact.whatsapp),
        });
    }

    if (contact.whatsapp_alt) {
        rows.push({
            label: contact.whatsapp_alt_label
                ? `WhatsApp (${contact.whatsapp_alt_label})`
                : 'WhatsApp 2',
            value: formatWhatsappDisplay(contact.whatsapp_alt),
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
        >
            <div className="mx-auto max-w-6xl">
                <ChapterHeading number={chapter} title={title} />

                <div className="reveal max-w-[720px]">
                    {/* Kalau usahanya menyebutkan sendiri cara pemesanannya,
                        itu yang dipakai — kalimat generik di bawahnya bisa
                        keliru (misalnya menyebut jam buka pada usaha yang
                        melayani pre-order saja). */}
                    {contact.contact_note ? (
                        <p className="text-[15px] leading-relaxed text-ink-soft">
                            {contact.contact_note}
                        </p>
                    ) : (
                        contact.whatsapp && (
                            <p className="text-[15px] leading-relaxed text-ink-soft">
                                Paling cepat lewat WhatsApp. Balasan biasanya di
                                jam buka.
                            </p>
                        )
                    )}

                    {/* Akun unit usaha — hanya ada di halaman induk. Ditaruh
                        SEBELUM baris kontak karena di sinilah pengunjung
                        halaman induk paling mungkin menemukan yang dicarinya:
                        unit usaha yang dituju, bukan kantor pusatnya. */}
                    {contact.unit_socials &&
                        contact.unit_socials.length > 0 && (
                            <div className="mt-6">
                                <p className="mb-3 text-[10.5px] font-medium tracking-[0.14em] text-brass uppercase">
                                    Instagram unit usaha
                                </p>

                                <ul className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    {contact.unit_socials.map((unit) => (
                                        <li key={unit.username}>
                                            <a
                                                href={`https://instagram.com/${unit.username}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="hairline-card flex min-h-11 items-center justify-between gap-3 px-4 py-3"
                                            >
                                                <span className="text-[14px] text-ink-soft">
                                                    {unit.label}
                                                </span>
                                                <span className="text-[14px] font-medium text-[var(--unit-accent)]">
                                                    @{unit.username}
                                                </span>
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                    <dl className="my-6 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-x-8">
                        {rows.map((row) => (
                            <div key={row.label}>
                                <dt className="mb-1 text-[10.5px] font-medium tracking-[0.14em] text-brass uppercase">
                                    {row.label}
                                </dt>
                                <dd className="text-[15px] text-ink">
                                    {row.href ? (
                                        <a
                                            href={row.href}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="underline decoration-hair underline-offset-4 transition-colors hover:text-[var(--unit-accent)]"
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
