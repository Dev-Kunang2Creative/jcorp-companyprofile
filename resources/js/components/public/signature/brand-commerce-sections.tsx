import { useState } from 'react';
import { brandUiCopy } from '@/components/public/signature/ayodya-i18n';
import type { BrandLocale } from '@/components/public/signature/ayodya-i18n';
import SignatureSectionLabel from '@/components/public/signature/signature-section-label';
import SignatureWhatsappButton from '@/components/public/signature/signature-whatsapp-button';
import { stagger } from '@/hooks/use-reveal';
import { formatWhatsappDisplay } from '@/lib/whatsapp';
import type {
    PublicCatalogItem,
    PublicContact,
    PublicPortfolioItem,
} from '@/types/public';

function CatalogImage({ item }: { item: PublicCatalogItem }) {
    const [failed, setFailed] = useState(false);

    if (!item.image_url || failed) {
        return (
            <div className="brand-catalog-fallback" aria-hidden="true">
                {item.initials}
            </div>
        );
    }

    return (
        <img
            src={item.image_url}
            alt={item.name}
            loading="lazy"
            decoding="async"
            onError={() => setFailed(true)}
            className="brand-catalog-image"
        />
    );
}

type CatalogProps = {
    label: string;
    items: PublicCatalogItem[];
    note: string | null;
    businessName: string;
    whatsapp?: string;
    locale?: BrandLocale;
};

export function BrandCatalogSection({
    label,
    items,
    note,
    businessName,
    whatsapp,
    locale = 'id',
}: CatalogProps) {
    const hasAnyImage = items.some((item) => item.image_url);

    return (
        <section id="katalog" className="brand-section brand-catalog-section">
            <div className="signature-container">
                <SignatureSectionLabel>{label}</SignatureSectionLabel>

                {hasAnyImage ? (
                    <ul className="brand-catalog-grid">
                        {items.map((item, index) => (
                            <li
                                key={item.id}
                                className="brand-catalog-card reveal"
                                style={stagger(index)}
                            >
                                <CatalogImage item={item} />
                                <div className="brand-catalog-content">
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
                                        <div className="brand-catalog-footer">
                                            {(item.formatted_price ||
                                                item.price_note) && (
                                                <div className="brand-catalog-price">
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
                                                    className="brand-catalog-cta"
                                                    locale={locale}
                                                >
                                                    {brandUiCopy[locale].ask}
                                                </SignatureWhatsappButton>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <BrandPriceList
                        items={items}
                        note={note}
                        businessName={businessName}
                        whatsapp={whatsapp}
                        locale={locale}
                    />
                )}
            </div>
        </section>
    );
}

function BrandPriceList({
    items,
    note,
    businessName,
    whatsapp,
    locale = 'id',
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
        <div className="brand-price-panel signature-glass reveal">
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
                <div className="brand-price-note">
                    {note && <p>{note}</p>}
                    {whatsapp && (
                        <SignatureWhatsappButton
                            number={whatsapp}
                            businessName={businessName}
                            locale={locale}
                        >
                            {brandUiCopy[locale].askWhatsapp}
                        </SignatureWhatsappButton>
                    )}
                </div>
            )}
        </div>
    );
}

export function BrandGallerySection({
    label,
    items,
    locale = 'id',
}: {
    label: string;
    items: PublicPortfolioItem[];
    locale?: BrandLocale;
}) {
    const visibleItems = items.filter((item) => item.image_url);

    if (visibleItems.length === 0) {
        return null;
    }

    return (
        <section id="galeri" className="brand-section brand-gallery-section">
            <div className="signature-container">
                <header className="brand-gallery-heading">
                    <SignatureSectionLabel>
                        {brandUiCopy[locale].gallery}
                    </SignatureSectionLabel>
                    <h2 className="brand-gallery-title reveal">{label}</h2>
                </header>
                <ul className="brand-gallery-grid">
                    {visibleItems.map((item, index) => (
                        <li
                            key={item.id}
                            className="brand-gallery-item reveal"
                            style={stagger(index)}
                        >
                            <a
                                href={item.full_url ?? item.image_url ?? '#'}
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label={`${brandUiCopy[locale].open} ${item.caption ?? label}`}
                            >
                                <img
                                    src={item.image_url ?? undefined}
                                    alt={item.caption ?? label}
                                    loading="lazy"
                                    decoding="async"
                                    sizes="(min-width: 1120px) 285px, (min-width: 720px) 25vw, 50vw"
                                />
                            </a>
                            {item.caption && <p>{item.caption}</p>}
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

function contactRows(
    contact: PublicContact,
    locale: BrandLocale,
): ContactRow[] {
    const rows: ContactRow[] = [];
    const isEnglish = locale === 'en';

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
        rows.push({
            label: isEnglish ? 'Address' : 'Alamat',
            value: contact.address,
        });
    }

    if (contact.business_hours) {
        rows.push({
            label: isEnglish ? 'Business Hours' : 'Jam Buka',
            value: contact.business_hours,
        });
    }

    return rows;
}

export function BrandContactSection({
    contact,
    businessName,
    logoUrl = null,
    locale = 'id',
    structured = false,
}: {
    contact: PublicContact;
    businessName: string;
    logoUrl?: string | null;
    locale?: BrandLocale;
    structured?: boolean;
}) {
    const rows = contactRows(contact, locale);
    const contactNoteParts = contact.contact_note
        ? structured
            ? contact.contact_note
                  .split(/(?<=\.)\s+(?=[A-ZÀ-ÖØ-Þ])/u)
                  .filter(Boolean)
            : [contact.contact_note]
        : [];

    return (
        <section id="kontak" className="brand-section brand-contact-section">
            <div className="signature-container">
                <SignatureSectionLabel>
                    {brandUiCopy[locale].contact}
                </SignatureSectionLabel>

                <div
                    className={`brand-contact-panel signature-glass reveal ${structured ? 'is-structured' : ''}`.trim()}
                >
                    <div className="brand-contact-note">
                        {contactNoteParts.length > 0 && (
                            <div className="brand-contact-note-copy">
                                {contactNoteParts.map((part, index) => (
                                    <p key={index}>{part}</p>
                                ))}
                            </div>
                        )}
                        {contact.whatsapp && (
                            <SignatureWhatsappButton
                                number={contact.whatsapp}
                                businessName={businessName}
                                locale={locale}
                            >
                                {brandUiCopy[locale].chatNow}
                            </SignatureWhatsappButton>
                        )}
                        {logoUrl && (
                            <img
                                src={logoUrl}
                                alt={`Logo ${businessName}`}
                                loading="lazy"
                                decoding="async"
                                width={600}
                                height={600}
                                className="brand-contact-logo"
                            />
                        )}
                    </div>

                    {rows.length > 0 && (
                        <dl className="brand-contact-list">
                            {rows.map((row) => (
                                <div
                                    key={`${row.label}-${row.value}`}
                                    className="brand-contact-item"
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
