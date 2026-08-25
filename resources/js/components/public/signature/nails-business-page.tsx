import { Head } from '@inertiajs/react';
import {
    NailsCatalogSection,
    NailsContactSection,
    NailsGallerySection,
} from '@/components/public/signature/nails-commerce-sections';
import {
    NailsAboutSection,
    NailsHighlightsSection,
    NailsServicesSection,
    NailsVisionMissionSection,
} from '@/components/public/signature/nails-profile-sections';
import SignatureNav from '@/components/public/signature/signature-nav';
import SignatureWhatsappButton from '@/components/public/signature/signature-whatsapp-button';
import type { PublicProfileProps } from '@/types/public';

function SignatureTitle({ name }: { name: string }) {
    const match = name.match(/^(.+?)(\s+by\s+me)$/i);

    if (!match) {
        return <>{name}</>;
    }

    return (
        <>
            <span>{match[1]} </span>
            <em>{match[2].trim()}</em>
        </>
    );
}

/**
 * Override presentasi Nail's by Me.
 *
 * Seluruh isi tetap datang dari PublicProfileProps yang sama dengan halaman
 * anak usaha lain. File ini hanya mengubah komposisi dan bahasa visual; bila
 * admin mengosongkan suatu data, section terkait tetap hilang.
 */
export default function NailsBusinessPage({
    business,
    parentName,
    catalog,
    portfolio,
    contact,
}: PublicProfileProps) {
    const whatsapp = contact?.whatsapp;
    const hasVisionMission = Boolean(
        business.vision || business.mission?.length,
    );
    const highlights = business.highlights?.length ? business.highlights : null;
    const services = business.services?.length ? business.services : null;
    const featured = business.featured_services?.length
        ? business.featured_services
        : null;
    const hasCatalog = Boolean(catalog || business.catalog_note);

    const navLinks = [
        business.description && { label: 'Tentang', href: '#tentang' },
        hasVisionMission && { label: 'Visi & Misi', href: '#visi-misi' },
        services && {
            label: business.services_label ?? 'Cara Pemesanan',
            href: '#layanan',
        },
        portfolio && {
            label: 'Galeri',
            href: '#galeri',
        },
        hasCatalog && {
            label: business.catalog_label,
            href: '#katalog',
        },
        contact && { label: 'Kontak', href: '#kontak' },
    ].filter((link): link is { label: string; href: string } => Boolean(link));

    return (
        <>
            <Head title={business.name}>
                {business.tagline && (
                    <meta name="description" content={business.tagline} />
                )}
            </Head>

            <div
                className="nails-page"
                data-presentation="nails-signature"
                style={
                    {
                        '--unit-accent': business.accent_color,
                    } as React.CSSProperties
                }
            >
                <SignatureNav
                    businessName={business.name}
                    logoUrl={business.logo_url}
                    whatsapp={whatsapp}
                    links={navLinks}
                />

                <main>
                    <section id="top" className="nails-hero">
                        <div className="signature-container nails-hero-grid">
                            <div
                                className="nails-world-field"
                                aria-hidden="true"
                            >
                                <span />
                                <span />
                            </div>

                            <div className="nails-hero-copy">
                                <p className="signature-section-label nails-hero-eyebrow">
                                    Profil Usaha
                                </p>
                                <h1>
                                    <SignatureTitle name={business.name} />
                                </h1>

                                {whatsapp && (
                                    <SignatureWhatsappButton
                                        number={whatsapp}
                                        businessName={business.name}
                                        className="nails-hero-cta"
                                    >
                                        Hubungi lewat WhatsApp
                                    </SignatureWhatsappButton>
                                )}
                            </div>

                            {business.logo_url && (
                                <div
                                    className="nails-hero-stage"
                                    aria-label={`Logo ${business.name}`}
                                >
                                    <div
                                        className="nails-stage-aura"
                                        aria-hidden="true"
                                    />
                                    <div
                                        className="nails-tip-fan"
                                        aria-hidden="true"
                                    >
                                        <span />
                                        <span />
                                        <span />
                                        <span />
                                    </div>
                                    <div className="nails-logo-vessel signature-glass">
                                        <img
                                            src={business.logo_url}
                                            alt={`Logo ${business.name}`}
                                            width={600}
                                            height={384}
                                            fetchPriority="high"
                                        />
                                    </div>
                                    <span
                                        className="nails-signature-line"
                                        aria-hidden="true"
                                    />
                                </div>
                            )}
                        </div>
                    </section>

                    {business.description && (
                        <NailsAboutSection
                            description={business.description}
                            logoUrl={business.logo_url}
                        />
                    )}

                    {hasVisionMission && (
                        <NailsVisionMissionSection
                            vision={business.vision}
                            mission={business.mission}
                        />
                    )}

                    {featured && (
                        <NailsServicesSection
                            id="layanan-unggulan"
                            title="Layanan Unggulan"
                            items={featured}
                        />
                    )}

                    {highlights && (
                        <NailsHighlightsSection
                            items={highlights}
                            businessName={business.name}
                        />
                    )}

                    {services && (
                        <NailsServicesSection
                            title={business.services_label ?? 'Cara Pemesanan'}
                            items={services}
                        />
                    )}

                    {portfolio && (
                        <NailsGallerySection
                            label={business.portfolio_label}
                            items={portfolio}
                        />
                    )}

                    {hasCatalog && (
                        <NailsCatalogSection
                            label={business.catalog_label}
                            items={catalog ?? []}
                            note={business.catalog_note}
                            businessName={business.name}
                            whatsapp={whatsapp}
                        />
                    )}

                    {contact && (
                        <NailsContactSection
                            contact={contact}
                            businessName={business.name}
                        />
                    )}
                </main>

                <footer className="nails-footer">
                    <div className="signature-container">
                        <p>
                            {parentName ? (
                                <>
                                    {business.name} — bagian dari{' '}
                                    <a href="/">{parentName}</a>
                                </>
                            ) : (
                                business.name
                            )}
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
