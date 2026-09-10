import { Head } from '@inertiajs/react';
import { Fragment, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import {
    brandUiCopy,
    localizeAyodyaBusiness,
    localizeAyodyaCatalog,
    localizeAyodyaContact,
} from '@/components/public/signature/ayodya-i18n';
import type { BrandLocale } from '@/components/public/signature/ayodya-i18n';
import {
    BrandCatalogSection,
    BrandContactSection,
    BrandGallerySection,
} from '@/components/public/signature/brand-commerce-sections';
import {
    BrandAboutSection,
    BrandDetailsSection,
    BrandVisionMissionSection,
} from '@/components/public/signature/brand-profile-sections';
import SignatureNav from '@/components/public/signature/signature-nav';
import SignatureWhatsappButton from '@/components/public/signature/signature-whatsapp-button';
import type { PublicProfileProps } from '@/types/public';

export type BrandSignature = 'sweetness' | 'ngelash' | 'logistic' | 'property';

type BrandSectionKey =
    | 'about'
    | 'visionMission'
    | 'featured'
    | 'highlights'
    | 'services'
    | 'catalog'
    | 'gallery'
    | 'contact';

const signatureBySlug: Record<string, BrandSignature> = {
    'sweetness-things': 'sweetness',
    ngelash: 'ngelash',
    'ayodya-logistic': 'logistic',
    'j-land-property': 'property',
};

/**
 * Content/order lock: visual boleh berbeda per signature, tetapi urutan
 * informasi tetap mengikuti struktur yang sudah disetujui sebelumnya.
 * Section tanpa data tetap dilewati tanpa menggeser section lainnya.
 */
const sectionOrder: BrandSectionKey[] = [
    'about',
    'visionMission',
    'featured',
    'highlights',
    'services',
    'catalog',
    'gallery',
    'contact',
];

export function hasBrandSignature(slug: string): boolean {
    return Object.prototype.hasOwnProperty.call(signatureBySlug, slug);
}

function initialLocale(slug: string): BrandLocale {
    if (slug !== 'ayodya-logistic' || typeof window === 'undefined') {
        return 'id';
    }

    return new URLSearchParams(window.location.search).get('lang') === 'en'
        ? 'en'
        : 'id';
}

/**
 * Family shell untuk empat anak usaha selain Nail's by Me.
 *
 * Kontrak data tetap PublicProfileProps. Variant hanya mengubah bentuk,
 * komposisi, dan motion; tidak pernah memilih atau menulis ulang isi client.
 */
export default function BrandBusinessPage({
    business,
    parentName,
    catalog,
    portfolio,
    contact,
}: PublicProfileProps) {
    const signature = signatureBySlug[business.slug];
    const isBilingual = business.slug === 'ayodya-logistic';
    const [locale, setLocale] = useState<BrandLocale>(() =>
        initialLocale(business.slug),
    );

    useEffect(() => {
        if (!isBilingual) {
            return;
        }

        document.documentElement.lang = locale;

        const url = new URL(window.location.href);

        if (locale === 'en') {
            url.searchParams.set('lang', 'en');
        } else {
            url.searchParams.delete('lang');
        }

        window.history.replaceState(window.history.state, '', url);
    }, [isBilingual, locale]);

    if (!signature) {
        return null;
    }

    const displayBusiness = localizeAyodyaBusiness(business, locale);
    const displayContact = localizeAyodyaContact(
        contact,
        business.slug,
        locale,
    );
    const displayCatalog = localizeAyodyaCatalog(
        catalog,
        business.slug,
        locale,
    );
    const copy = brandUiCopy[locale];
    const whatsapp = displayContact?.whatsapp;
    const hasVisionMission = Boolean(
        displayBusiness.vision || displayBusiness.mission?.length,
    );
    const featured = displayBusiness.featured_services?.length
        ? displayBusiness.featured_services
        : null;
    const highlights = displayBusiness.highlights?.length
        ? displayBusiness.highlights
        : null;
    const services = displayBusiness.services?.length
        ? displayBusiness.services
        : null;
    const hasCatalog = Boolean(
        displayCatalog?.length || displayBusiness.catalog_note,
    );
    const hasPortfolio = Boolean(portfolio?.length);
    const firstServicesId = featured ? 'layanan-unggulan' : 'layanan';

    const navTargetBySection: Partial<
        Record<BrandSectionKey, { label: string; href: string } | false>
    > = {
        about: Boolean(displayBusiness.description) && {
            label: copy.aboutNav,
            href: '#tentang',
        },
        visionMission: hasVisionMission && {
            label: copy.visionMissionNav,
            href: '#visi-misi',
        },
        featured: Boolean(featured || services) && {
            label: featured
                ? copy.featuredServices
                : (displayBusiness.services_label ?? copy.defaultServices),
            href: `#${firstServicesId}`,
        },
        catalog: hasCatalog && {
            label: displayBusiness.catalog_label,
            href: '#katalog',
        },
        gallery: hasPortfolio && {
            label: copy.gallery,
            href: '#galeri',
        },
        contact: Boolean(displayContact) && {
            label: copy.contact,
            href: '#kontak',
        },
    };
    const navLinks = sectionOrder
        .map((key) => navTargetBySection[key])
        .filter((link): link is { label: string; href: string } =>
            Boolean(link),
        );

    const sections: Record<BrandSectionKey, ReactNode> = {
        about: displayBusiness.description ? (
            <BrandAboutSection
                businessName={displayBusiness.name}
                description={displayBusiness.description}
                logoUrl={displayBusiness.logo_url}
                locale={locale}
            />
        ) : null,
        visionMission: hasVisionMission ? (
            <BrandVisionMissionSection
                vision={displayBusiness.vision}
                mission={displayBusiness.mission}
                locale={locale}
            />
        ) : null,
        featured: featured ? (
            <BrandDetailsSection
                id="layanan-unggulan"
                label={copy.featuredServices}
                items={featured}
                kind="featured"
            />
        ) : null,
        highlights: highlights ? (
            <BrandDetailsSection
                id="keunggulan"
                label={`${copy.whyChoose} ${displayBusiness.name}`}
                items={highlights}
                kind="highlights"
            />
        ) : null,
        services: services ? (
            <BrandDetailsSection
                id="layanan"
                label={displayBusiness.services_label ?? copy.defaultServices}
                items={services}
                kind="services"
            />
        ) : null,
        catalog: hasCatalog ? (
            <BrandCatalogSection
                label={displayBusiness.catalog_label}
                items={displayCatalog ?? []}
                note={displayBusiness.catalog_note}
                businessName={displayBusiness.name}
                whatsapp={whatsapp}
                locale={locale}
            />
        ) : null,
        gallery:
            hasPortfolio && portfolio ? (
                <BrandGallerySection
                    label={displayBusiness.portfolio_label}
                    items={portfolio}
                    locale={locale}
                />
            ) : null,
        contact: displayContact ? (
            <BrandContactSection
                contact={displayContact}
                businessName={displayBusiness.name}
                logoUrl={
                    signature === 'sweetness' || signature === 'property'
                        ? displayBusiness.logo_url
                        : null
                }
                locale={locale}
                structured={signature === 'logistic'}
            />
        ) : null,
    };

    return (
        <>
            <Head title={displayBusiness.name}>
                {displayBusiness.tagline && (
                    <meta
                        name="description"
                        content={displayBusiness.tagline}
                    />
                )}
            </Head>

            <div
                className={`signature-page brand-page brand-page--${signature}`}
                data-presentation={`${signature}-editorial-world`}
                data-world={signature}
                data-language={isBilingual ? locale : undefined}
                style={
                    {
                        '--unit-accent': business.accent_color,
                    } as React.CSSProperties
                }
            >
                <SignatureNav
                    businessName={displayBusiness.name}
                    logoUrl={displayBusiness.logo_url}
                    whatsapp={whatsapp}
                    links={navLinks}
                    language={
                        isBilingual
                            ? { value: locale, onChange: setLocale }
                            : undefined
                    }
                />

                <main className="brand-world-main">
                    <section
                        id="top"
                        className={
                            displayBusiness.cover_image_url
                                ? 'brand-hero brand-hero--with-cover'
                                : 'brand-hero'
                        }
                    >
                        {displayBusiness.cover_image_url && (
                            <div
                                className="brand-hero-background"
                                aria-hidden="true"
                            >
                                <img
                                    src={displayBusiness.cover_image_url}
                                    alt=""
                                    width={1200}
                                    height={675}
                                    fetchPriority="high"
                                />
                            </div>
                        )}

                        <div className="signature-container brand-hero-grid">
                            <div
                                className="brand-world-field"
                                aria-hidden="true"
                            >
                                <span />
                                <span />
                                <span />
                            </div>

                            <div className="brand-hero-copy">
                                <p className="signature-section-label brand-hero-eyebrow">
                                    {copy.profile}
                                </p>

                                <h1>{displayBusiness.name}</h1>

                                {displayBusiness.tagline && (
                                    <p className="brand-hero-tagline">
                                        {displayBusiness.tagline}
                                    </p>
                                )}

                                {whatsapp && (
                                    <SignatureWhatsappButton
                                        number={whatsapp}
                                        businessName={displayBusiness.name}
                                        className="brand-hero-cta"
                                        locale={locale}
                                    >
                                        {copy.heroWhatsapp}
                                    </SignatureWhatsappButton>
                                )}
                            </div>

                            {displayBusiness.logo_url &&
                                !displayBusiness.cover_image_url && (
                                    <div
                                        className="brand-hero-stage"
                                        aria-label={`Identitas ${displayBusiness.name}`}
                                    >
                                        <div
                                            className="brand-hero-motif"
                                            aria-hidden="true"
                                        >
                                            <span />
                                            <span />
                                            <span />
                                            <span />
                                        </div>

                                        <div className="brand-logo-vessel signature-glass">
                                            <img
                                                src={
                                                    displayBusiness.logo_url ??
                                                    undefined
                                                }
                                                alt={`Logo ${displayBusiness.name}`}
                                                width={600}
                                                height={384}
                                                fetchPriority="high"
                                            />
                                        </div>
                                    </div>
                                )}
                        </div>
                    </section>

                    {sectionOrder.map((key) => (
                        <Fragment key={key}>{sections[key]}</Fragment>
                    ))}
                </main>

                <footer className="brand-footer">
                    <div className="signature-container">
                        <p>
                            {parentName ? (
                                <>
                                    {displayBusiness.name} — {copy.partOf}{' '}
                                    <a href="/">{parentName}</a>
                                </>
                            ) : (
                                displayBusiness.name
                            )}
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
