import WhatsappButton from '@/components/public/whatsapp-button';
import { formatWhatsappDisplay } from '@/lib/whatsapp';
import type { PublicContact } from '@/types/public';

type Props = {
    contact: PublicContact;
    businessName: string;
};

export default function HomeContact({ contact, businessName }: Props) {
    const rows: Array<{ label: string; value: string; href?: string }> = [];

    if (contact.whatsapp) {
        rows.push({
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
        <div className="home-mock-contact-shell">
            <p className="reveal home-mock-eyebrow home-mock-contact-eyebrow">
                Kontak
            </p>

            <div className="home-mock-contact-panel reveal">
                <h2 className="home-mock-contact-title">
                    <span>Hubungi </span>
                    <em>Kami</em>
                </h2>

                <div className="home-mock-contact-bottom">
                    {contact.contact_note && (
                        <p className="home-mock-contact-copy">
                            {contact.contact_note}
                        </p>
                    )}

                    <div className="home-mock-contact-channels">
                        {contact.unit_socials &&
                            contact.unit_socials.length > 0 && (
                                <div>
                                    <p className="home-mock-contact-label">
                                        Instagram unit usaha
                                    </p>
                                    <ul className="home-mock-socials">
                                        {contact.unit_socials.map((unit) => (
                                            <li
                                                key={`${unit.label}-${unit.username}`}
                                            >
                                                <a
                                                    href={`https://instagram.com/${unit.username}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="home-social-link"
                                                >
                                                    <strong>
                                                        {unit.label}
                                                    </strong>
                                                    <span>
                                                        @{unit.username}
                                                    </span>
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                        {rows.length > 0 && (
                            <dl className="home-mock-contact-rows">
                                {rows.map((row) => (
                                    <div key={row.label}>
                                        <dt>{row.label}</dt>
                                        <dd>
                                            {row.href ? (
                                                <a
                                                    href={row.href}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
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
                        )}

                        {contact.whatsapp && (
                            <WhatsappButton
                                number={contact.whatsapp}
                                businessName={businessName}
                                className="mt-5 w-full rounded-full! px-7! md:w-auto"
                            >
                                WhatsApp
                            </WhatsappButton>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
