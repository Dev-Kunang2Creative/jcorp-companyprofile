import { Head } from '@inertiajs/react';
import AboutSection from '@/components/public/about-section';
import CatalogSection from '@/components/public/catalog-section';
import ContactSection from '@/components/public/contact-section';
import FloatingWa from '@/components/public/floating-wa';
import HeroSection from '@/components/public/hero-section';
import PortfolioSection from '@/components/public/portfolio-section';
import SiteNav from '@/components/public/site-nav';
import type { PublicProfileProps } from '@/types/public';

/**
 * Halaman profil anak usaha.
 *
 * Aturan section kosong (spec §3): section yang datanya null tidak dirender
 * sama sekali — bukan ditampilkan kosong, bukan diisi "coming soon". Ini yang
 * membuat website tetap layak tayang saat konten baru terisi sebagian.
 *
 * Hero selalu dirender, minimal memuat nama usaha.
 */
export default function BusinessProfile({
    business,
    catalog,
    portfolio,
    contact,
}: PublicProfileProps) {
    const whatsapp = contact?.whatsapp;

    // Tautan navigasi hanya menunjuk ke section yang benar-benar ada —
    // tautan ke section yang tidak dirender akan menggulir ke tempat kosong.
    const navLinks = [
        business.description && { label: 'Tentang', href: '#tentang' },
        catalog && { label: business.catalog_label, href: '#katalog' },
        portfolio && { label: business.portfolio_label, href: '#portfolio' },
        contact && { label: 'Kontak', href: '#kontak' },
    ].filter((link): link is { label: string; href: string } => Boolean(link));

    return (
        <>
            <Head title={business.name}>
                {business.tagline && (
                    <meta name="description" content={business.tagline} />
                )}
            </Head>

            {/* Tanpa bg: aurora di <body> membentang di seluruh halaman, dan
                itulah yang dibiaskan setiap panel kaca di atasnya. */}
            <div className="min-h-svh font-sans">
                <SiteNav
                    businessName={business.name}
                    logoUrl={business.logo_url}
                    whatsapp={whatsapp}
                    links={navLinks}
                />

                <main>
                    <HeroSection
                        name={business.name}
                        tagline={business.tagline}
                        logoUrl={business.logo_url}
                        whatsapp={whatsapp}
                    />

                    {business.description && (
                        <AboutSection description={business.description} />
                    )}

                    {catalog && (
                        <CatalogSection
                            label={business.catalog_label}
                            items={catalog}
                            businessName={business.name}
                            whatsapp={whatsapp}
                        />
                    )}

                    {portfolio && (
                        <PortfolioSection
                            label={business.portfolio_label}
                            items={portfolio}
                        />
                    )}

                    {contact && (
                        <ContactSection
                            contact={contact}
                            businessName={business.name}
                        />
                    )}
                </main>

                {whatsapp && (
                    <FloatingWa
                        number={whatsapp}
                        businessName={business.name}
                    />
                )}

                {/* Footer ikut terang. Balok gelap di dasar halaman memotong
                    aurora dan membuat seluruh temanya terbaca setengah jadi. */}
                <footer className="glass-veil border-t px-4 py-8 text-center text-[13px] text-ink-soft md:px-8">
                    {business.name} — bagian dari{' '}
                    <a
                        href="/"
                        className="text-gold-deep underline-offset-4 hover:underline"
                    >
                        J Corp
                    </a>
                </footer>
            </div>
        </>
    );
}
