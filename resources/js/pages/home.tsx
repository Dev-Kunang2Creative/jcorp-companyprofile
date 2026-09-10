import { Head } from '@inertiajs/react';
import HomeContact from '@/components/public/home/home-contact';
import HomeHero from '@/components/public/home/home-hero';
import HomeLeadership from '@/components/public/home/home-leadership';
import HomeNav from '@/components/public/home/home-nav';
import type { HomeNavLink } from '@/components/public/home/home-nav';
import HomeUnitCard from '@/components/public/home/home-unit-card';
import { stagger, useReveal } from '@/hooks/use-reveal';
import type { HomeProps } from '@/types/home';

export default function Home({ parent, subsidiaries, contact }: HomeProps) {
    useReveal();

    const hasAbout = Boolean(parent.description);
    const hasVisionMission = Boolean(parent.vision || parent.mission?.length);
    const hasUnits = subsidiaries.length > 0;

    const paragraphs = (parent.description ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean);
    const aboutLead = paragraphs[0] ?? null;
    const aboutSupporting = paragraphs.slice(1);
    const parentName = splitLastWord(parent.name);

    const navLinks = [
        { label: 'Beranda', href: '#top' },
        hasAbout && { label: 'Tentang', href: '#tentang' },
        hasVisionMission && { label: 'Visi & Misi', href: '#visi-misi' },
        hasUnits && { label: 'Unit Usaha', href: '#usaha' },
        contact && { label: 'Hubungi Kami', href: '#kontak' },
    ].filter((link): link is HomeNavLink => Boolean(link));

    return (
        <>
            <Head title={parent.tagline ?? parent.name}>
                {parent.tagline && (
                    <meta name="description" content={parent.tagline} />
                )}
            </Head>

            <div
                className="home-luminous min-h-svh bg-paper font-sans text-ink"
                style={
                    {
                        '--unit-accent': parent.accent_color,
                    } as React.CSSProperties
                }
            >
                <HomeNav
                    businessName={parent.name}
                    logoUrl={parent.logo_url}
                    links={navLinks}
                />

                <main>
                    <HomeHero
                        parent={parent}
                        subsidiaries={subsidiaries}
                        hasAbout={hasAbout}
                        hasUnits={hasUnits}
                    />

                    {hasAbout && (
                        <section
                            id="tentang"
                            className="home-section home-mock-about-section"
                        >
                            <div className="home-mock-container">
                                <div className="home-mock-about-grid">
                                    <div className="reveal home-mock-about-statement">
                                        <p className="home-mock-eyebrow">
                                            Tentang Kami
                                        </p>
                                        <h2>
                                            <span>{parentName.first}</span>
                                            {parentName.second && (
                                                <em>{parentName.second}</em>
                                            )}
                                        </h2>

                                        {aboutSupporting.map(
                                            (paragraph, index) => (
                                                <p
                                                    key={index}
                                                    className="home-mock-about-pullquote"
                                                >
                                                    {paragraph}
                                                </p>
                                            ),
                                        )}
                                    </div>

                                    <div className="home-mock-about-stack">
                                        {aboutLead && (
                                            <article className="reveal home-mock-about-card">
                                                <p className="home-card-label">
                                                    Tentang Kami
                                                </p>
                                                <p>{aboutLead}</p>
                                            </article>
                                        )}

                                        <div className="home-mock-metric-row">
                                            <article className="reveal home-mock-metric">
                                                <strong>
                                                    {String(
                                                        subsidiaries.length,
                                                    ).padStart(2, '0')}
                                                </strong>
                                                <span>Unit Usaha</span>
                                            </article>
                                            <article
                                                className="reveal home-mock-metric"
                                                style={{
                                                    transitionDelay: '80ms',
                                                }}
                                            >
                                                <strong>01</strong>
                                                <span>Induk Usaha</span>
                                            </article>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    )}

                    {hasVisionMission && (
                        <section
                            id="visi-misi"
                            className="home-section home-mock-vision-section"
                        >
                            <div className="home-mock-container">
                                <div className="home-mock-vision-grid">
                                    <div className="reveal home-mock-vision-copy">
                                        <p className="home-mock-eyebrow">
                                            Visi
                                        </p>
                                        <h2>
                                            <span>Visi &amp; </span>
                                            <em>Misi</em>
                                        </h2>

                                        {parent.vision && (
                                            <blockquote>
                                                {parent.vision}
                                            </blockquote>
                                        )}
                                    </div>

                                    {parent.mission &&
                                        parent.mission.length > 0 && (
                                            <div className="home-mock-mission-column">
                                                <p className="reveal home-mock-eyebrow">
                                                    Misi
                                                </p>
                                                <ol className="home-mock-mission-list">
                                                    {parent.mission.map(
                                                        (mission, index) => (
                                                            <li
                                                                key={index}
                                                                className="reveal home-mock-mission"
                                                                style={stagger(
                                                                    index,
                                                                )}
                                                            >
                                                                <span aria-hidden="true">
                                                                    {String(
                                                                        index +
                                                                            1,
                                                                    ).padStart(
                                                                        2,
                                                                        '0',
                                                                    )}
                                                                </span>
                                                                <p>{mission}</p>
                                                            </li>
                                                        ),
                                                    )}
                                                </ol>
                                            </div>
                                        )}
                                </div>
                            </div>
                        </section>
                    )}

                    {hasUnits && (
                        <section id="usaha" className="home-section">
                            <div className="mx-auto max-w-6xl">
                                <p className="reveal home-mock-eyebrow home-mock-unit-eyebrow">
                                    Unit Usaha
                                </p>

                                <div className="grid items-stretch gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    {subsidiaries.map((subsidiary, index) => (
                                        <div
                                            key={subsidiary.slug}
                                            className="reveal h-full"
                                            style={stagger(index)}
                                        >
                                            <HomeUnitCard
                                                slug={subsidiary.slug}
                                                name={subsidiary.name}
                                                tagline={subsidiary.tagline}
                                                logoUrl={subsidiary.logo_url}
                                                featuredImageUrl={
                                                    subsidiary.featured_image_url
                                                }
                                                accentColor={
                                                    subsidiary.accent_color
                                                }
                                                index={String(
                                                    index + 1,
                                                ).padStart(2, '0')}
                                                initials={subsidiary.initials}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </section>
                    )}

                    <HomeLeadership />

                    {contact && (
                        <section
                            id="kontak"
                            className="home-section home-mock-contact-section pb-24! md:pb-32!"
                        >
                            <div className="home-mock-container">
                                <HomeContact
                                    contact={contact}
                                    businessName={parent.name}
                                />
                            </div>
                        </section>
                    )}
                </main>

                <footer className="home-mock-footer">
                    <div className="home-mock-footer-grid">
                        <p>
                            © {new Date().getFullYear()} {parent.name}
                        </p>
                        {parent.logo_url && (
                            <img
                                src={parent.logo_url}
                                alt={parent.name}
                                className="h-12 w-12 object-contain"
                            />
                        )}
                        {parent.tagline && <p>{parent.tagline}</p>}
                    </div>
                </footer>
            </div>
        </>
    );
}

function splitLastWord(value: string): {
    first: string;
    second: string | null;
} {
    const lastSpace = value.lastIndexOf(' ');

    if (lastSpace === -1) {
        return { first: value, second: null };
    }

    return {
        first: value.slice(0, lastSpace + 1),
        second: value.slice(lastSpace + 1) || null,
    };
}
