import { useState } from 'react';
import SignatureSectionLabel from '@/components/public/signature/signature-section-label';
import SignatureWhatsappButton from '@/components/public/signature/signature-whatsapp-button';
import { stagger } from '@/hooks/use-reveal';
import { formatWhatsappDisplay } from '@/lib/whatsapp';
import type {
    PublicCatalogItem,
    PublicContact,
    PublicPortfolioItem,
} from '@/types/public';

type CatalogImageProps = {
    item: PublicCatalogItem;
};

function NailsCatalogImage({ item }: CatalogImageProps) {
    const [failed, setFailed] = useState(false);

    if (!item.image_url || failed) {
        return (
            <div className="nails-catalog-fallback" aria-hidden="true">
                {item.initials}
            </div>
        );
    }

    return (
        <img
            src={item.image_url}
            alt={item.name}
            loading="lazy"
            onError={() => setFailed(true)}
            className="nails-catalog-image"
        />
    );
}

type CatalogProps = {
    label: string;
    items: PublicCatalogItem[];
    note: string | null;
    businessName: string;
    whatsapp?: string;
};

export function NailsCatalogSection({
    label,
    items,
    note,
    businessName,
    whatsapp,
}: CatalogProps) {
    const hasAnyImage = items.some((item) => item.image_url);

    return (
        <section id="katalog" className="nails-section">
            <div className="signature-container">
                <SignatureSectionLabel>{label}</SignatureSectionLabel>

                {hasAnyImage ? (
                    <ul className="nails-catalog-grid">
                        {items.map((item, index) => (
                            <li
                                key={item.id}
                                className="nails-catalog-card reveal"
                                style={stagger(index)}
                            >
                                <NailsCatalogImage item={item} />

                                <div className="nails-catalog-content">
                                    {item.category && (
                                        <p className="signature-small-label">
                                            {item.category}
                                        </p>
                                    )}
                                    <h3>{item.name}</h3>
                                    {item.description && (
                                        <p>{item.description}</p>
                                    )}

                                    {(item.formatted_price ||
                                        item.price_note ||
                                        whatsapp) && (
                                        <div className="nails-catalog-footer">
                                            {(item.formatted_price ||
                                                item.price_note) && (
                                                <div className="nails-catalog-price">
                                                    {item.price_note && (
                                                        <span>
                                                            {item.price_note}
                                                        </span>
                                                    )}
                                                    {item.formatted_price && (
                                                        <strong>
                                                            {
                                                                item.formatted_price
                                                            }
                                                        </strong>
                                                    )}
                                                </div>
                                            )}

                                            {whatsapp && (
                                                <SignatureWhatsappButton
                                                    number={whatsapp}
                                                    businessName={businessName}
                                                    itemName={item.name}
                                                    className="nails-catalog-cta"
                                                >
                                                    Tanya
                                                </SignatureWhatsappButton>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <NailsPriceList
                        items={items}
                        note={note}
                        businessName={businessName}
                        whatsapp={whatsapp}
                    />
                )}
            </div>
        </section>
    );
}

function NailsPriceList({
    items,
    note,
    businessName,
    whatsapp,
}: Omit<CatalogProps, 'label'>) {
    const groups: Array<{
        category: string | null;
        items: PublicCatalogItem[];
    }> = [];

    for (const item of items) {
        const last = groups.at(-1);

        if (last && last.category === item.category) {
            last.items.push(item);
        } else {
            groups.push({ category: item.category, items: [item] });
        }
    }

    return (
        <div className="nails-price-panel signature-glass reveal">
            {groups.map((group, groupIndex) => (
                <section key={`${group.category}-${groupIndex}`}>
                    {group.category && <h3>{group.category}</h3>}
                    <ul>
                        {group.items.map((item) => (
                            <li key={item.id}>
                                <div>
                                    <p>{item.name}</p>
                                    {item.description && (
                                        <small>{item.description}</small>
                                    )}
                                </div>
                                {(item.formatted_price || item.price_note) && (
                                    <strong>
                                        {item.formatted_price ??
                                            item.price_note}
                                    </strong>
                                )}
                            </li>
                        ))}
                    </ul>
                </section>
            ))}

            {(note || whatsapp) && (
                <div className="nails-price-note">
                    {note && <p>{note}</p>}
                    {whatsapp && (
                        <SignatureWhatsappButton
                            number={whatsapp}
                            businessName={businessName}
                        >
                            Tanya &amp; buat janji
                        </SignatureWhatsappButton>
                    )}
                </div>
            )}
        </div>
    );
}

type GalleryProps = {
    label: string;
    items: PublicPortfolioItem[];
};

export function NailsGallerySection({ label, items }: GalleryProps) {
    const visibleItems = items.filter((item) => item.image_url);

    if (visibleItems.length === 0) {
        return null;
    }

    return (
        <section id="galeri" className="nails-section nails-gallery-section">
            <div className="signature-container">
                <header className="nails-gallery-heading">
                    <SignatureSectionLabel>Galeri</SignatureSectionLabel>
                    <h2 className="reveal">{label}</h2>
                </header>

                <ul className="nails-gallery-grid">
                    {visibleItems.map((item, index) => (
                        <li
                            key={item.id}
                            className="nails-gallery-item reveal"
                            style={stagger(index)}
                        >
                            <figure>
                                <a
                                    href={
                                        item.full_url ?? item.image_url ?? '#'
                                    }
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={`Buka ${item.caption ?? label}`}
                                >
                                    <img
                                        src={item.image_url ?? undefined}
                                        alt={item.caption ?? label}
                                        loading="lazy"
                                        decoding="async"
                                        sizes="(min-width: 1120px) 285px, (min-width: 720px) 25vw, 50vw"
                                    />
                                </a>
                                {item.caption && (
                                    <figcaption>{item.caption}</figcaption>
                                )}
                            </figure>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

type ContactRow = {
    label: string;
    value: string;
    href?: string;
};

function contactRows(contact: PublicContact): ContactRow[] {
    const rows: ContactRow[] = [];

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

    return rows;
}

type ContactProps = {
    contact: PublicContact;
    businessName: string;
};

export function NailsContactSection({ contact, businessName }: ContactProps) {
    const rows = contactRows(contact);

    return (
        <section id="kontak" className="nails-section nails-contact-section">
            <div className="signature-container">
                <SignatureSectionLabel>
                    Pesan &amp; Kunjungi
                </SignatureSectionLabel>

                <div className="nails-contact-panel signature-glass reveal">
                    <div className="nails-contact-note">
                        {contact.contact_note && <p>{contact.contact_note}</p>}

                        {contact.whatsapp && (
                            <SignatureWhatsappButton
                                number={contact.whatsapp}
                                businessName={businessName}
                            >
                                Chat sekarang
                            </SignatureWhatsappButton>
                        )}
                    </div>

                    {rows.length > 0 && (
                        <dl className="nails-contact-list">
                            {rows.map((row) => (
                                <div
                                    key={`${row.label}-${row.value}`}
                                    className="nails-contact-item"
                                >
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
                </div>
            </div>
        </section>
    );
}
