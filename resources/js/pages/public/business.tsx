import { Head } from '@inertiajs/react';
import AboutSection from '@/components/public/about-section';
import CatalogSection from '@/components/public/catalog-section';
import ContactSection from '@/components/public/contact-section';
import FloatingWa from '@/components/public/floating-wa';
import HeroSection from '@/components/public/hero-section';
import HighlightsSection from '@/components/public/highlights-section';
import PortfolioSection from '@/components/public/portfolio-section';
import ServicesSection from '@/components/public/services-section';
import BrandBusinessPage, {
    hasBrandSignature,
} from '@/components/public/signature/brand-business-page';
import NailsBusinessPage from '@/components/public/signature/nails-business-page';
import SiteNav from '@/components/public/site-nav';
import VisionMissionSection from '@/components/public/vision-mission-section';
import { useReveal } from '@/hooks/use-reveal';
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
    parentName,
    catalog,
    portfolio,
    contact,
}: PublicProfileProps) {
    useReveal();

    // Seluruh arah One Family, Five Signatures tetap memakai kontrak route
    // dan props yang sama; hanya presentasinya yang berbeda per brand.
    if (business.slug === 'nails-by-me') {
        return (
            <NailsBusinessPage
                business={business}
                parentName={parentName}
                catalog={catalog}
                portfolio={portfolio}
                contact={contact}
            />
        );
    }

    if (hasBrandSignature(business.slug)) {
        return (
            <BrandBusinessPage
                business={business}
                parentName={parentName}
                catalog={catalog}
                portfolio={portfolio}
                contact={contact}
            />
        );
    }

    const whatsapp = contact?.whatsapp;

    // Visi dan misi satu section, jadi cukup salah satunya ada.
    const hasVisionMission = Boolean(
        business.vision || business.mission?.length,
    );
    const highlights = business.highlights?.length ? business.highlights : null;
    const services = business.services?.length ? business.services : null;
    const featured = business.featured_services?.length
        ? business.featured_services
        : null;
    const hasCatalog = Boolean(catalog || business.catalog_note);

    /**
     * Penomoran bab dihitung di sini, bukan di dalam ChapterHeading.
     *
     * Sebabnya aturan section kosong: anak usaha yang belum mengirim visi
     * tidak punya bab Visi & Misi sama sekali. Kalau tiap komponen menomori
     * dirinya sendiri, halaman itu akan menampilkan 01, 03, 04 — lompatan
     * yang terbaca sebagai ada sesuatu yang hilang.
     *
     * Penghitung ini hanya maju untuk section yang BENAR-BENAR dirender.
     */
    let counter = 0;
    const nextChapter = () => String(++counter).padStart(2, '0');

    // Dihitung berurutan sesuai urutan tampil di layar. Jangan diubah
    // urutannya tanpa mengubah urutan JSX di bawah.
    const chapters = {
        about: business.description ? nextChapter() : '',
        visionMission: hasVisionMission ? nextChapter() : '',
        featured: featured ? nextChapter() : '',
        highlights: highlights ? nextChapter() : '',
        services: services ? nextChapter() : '',
        catalog: hasCatalog ? nextChapter() : '',
        portfolio: portfolio ? nextChapter() : '',
        contact: contact ? nextChapter() : '',
    };

    // Tautan navigasi hanya menunjuk ke section yang benar-benar ada —
    // tautan ke section yang tidak dirender akan menggulir ke tempat kosong.
    //
    // Anak usaha dengan materi lengkap bisa punya delapan section. Menaruh
    // semuanya di navigasi membuat barisnya penuh dan tidak ada yang
    // menonjol, jadi yang masuk hanya yang dicari pengunjung: cerita, apa
    // yang dijual, dan cara menghubungi.
    const navLinks = [
        business.description && { label: 'Tentang', href: '#tentang' },
        hasCatalog && { label: business.catalog_label, href: '#katalog' },
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

            {/* Warna aksen dipasang SEKALI di sini, lalu diwarisi seluruh
                komponen di dalamnya lewat var(--unit-accent). Nilainya sudah
                dipastikan berbentuk #RRGGBB di server — lihat
                Business::safeAccentColor(). */}
            <div
                className="min-h-svh bg-paper font-sans"
                style={
                    {
                        '--unit-accent': business.accent_color,
                    } as React.CSSProperties
                }
            >
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
                        coverImageUrl={business.cover_image_url}
                        whatsapp={whatsapp}
                    />

                    {business.description && (
                        <AboutSection
                            description={business.description}
                            chapter={chapters.about}
                        />
                    )}

                    {hasVisionMission && (
                        <VisionMissionSection
                            vision={business.vision}
                            mission={business.mission}
                            chapter={chapters.visionMission}
                        />
                    )}

                    {/* Layanan Unggulan sebelum Keunggulan, mengikuti urutan
                        di materi client: apa yang dijual dulu, baru alasan
                        memilihnya. */}
                    {featured && (
                        <ServicesSection
                            items={featured}
                            title="Layanan Unggulan"
                            id="layanan-unggulan"
                            chapter={chapters.featured}
                        />
                    )}

                    {highlights && (
                        <HighlightsSection
                            items={highlights}
                            businessName={business.name}
                            chapter={chapters.highlights}
                        />
                    )}

                    {services && (
                        <ServicesSection
                            items={services}
                            title={business.services_label ?? undefined}
                            chapter={chapters.services}
                        />
                    )}

                    {/* Section katalog tetap dirender bila daftarnya kosong
                        TAPI ada catatan harga — "Mulai dari Rp 30.000" pada
                        usaha yang belum mengirim rincian per layanan. Tanpa
                        ini, satu-satunya keterangan harga yang dimiliki
                        pengunjung tidak pernah muncul di layar. */}
                    {hasCatalog && (
                        <CatalogSection
                            label={business.catalog_label}
                            items={catalog ?? []}
                            businessName={business.name}
                            whatsapp={whatsapp}
                            note={business.catalog_note}
                            chapter={chapters.catalog}
                        />
                    )}

                    {portfolio && (
                        <PortfolioSection
                            label={business.portfolio_label}
                            items={portfolio}
                            chapter={chapters.portfolio}
                        />
                    )}

                    {contact && (
                        <ContactSection
                            contact={contact}
                            businessName={business.name}
                            chapter={chapters.contact}
                        />
                    )}
                </main>

                {whatsapp && (
                    <FloatingWa
                        number={whatsapp}
                        businessName={business.name}
                    />
                )}

                <footer className="border-t border-ink px-4 py-10 md:px-8">
                    <div className="mx-auto max-w-6xl">
                        <p className="text-[10.5px] tracking-[0.12em] text-ink-soft uppercase">
                            {parentName ? (
                                <>
                                    {business.name} — bagian dari{' '}
                                    <a
                                        href="/"
                                        className="text-[var(--unit-accent)] underline-offset-4 hover:underline"
                                    >
                                        {parentName}
                                    </a>
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
