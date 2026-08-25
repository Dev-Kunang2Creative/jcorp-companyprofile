import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

export type HomeNavLink = {
    label: string;
    href: string;
};

type Props = {
    businessName: string;
    logoUrl: string | null;
    links: HomeNavLink[];
};

export default function HomeNav({ businessName, logoUrl, links }: Props) {
    const [open, setOpen] = useState(false);
    const [activeHref, setActiveHref] = useState(links[0]?.href ?? '#top');
    const [scrollProgress, setScrollProgress] = useState(0);
    const contactLink = links.find((link) => link.href === '#kontak');
    const primaryLinks = links.filter((link) => link.href !== '#kontak');

    useEffect(() => {
        const sections = links
            .map((link) => document.querySelector<HTMLElement>(link.href))
            .filter((section): section is HTMLElement => Boolean(section));

        if (
            sections.length === 0 ||
            typeof IntersectionObserver === 'undefined'
        ) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort(
                        (a, b) => b.intersectionRatio - a.intersectionRatio,
                    )[0];

                if (visible?.target.id) {
                    setActiveHref(`#${visible.target.id}`);
                }
            },
            {
                rootMargin: '-28% 0px -58% 0px',
                threshold: [0, 0.15, 0.4],
            },
        );

        sections.forEach((section) => observer.observe(section));

        return () => observer.disconnect();
    }, [links]);

    useEffect(() => {
        let frame = 0;

        const updateProgress = () => {
            const scrollable =
                document.documentElement.scrollHeight - window.innerHeight;
            const next = scrollable > 0 ? window.scrollY / scrollable : 0;

            setScrollProgress(Math.min(Math.max(next, 0), 1));
            frame = 0;
        };

        const queueUpdate = () => {
            if (frame === 0) {
                frame = window.requestAnimationFrame(updateProgress);
            }
        };

        updateProgress();
        window.addEventListener('scroll', queueUpdate, { passive: true });
        window.addEventListener('resize', queueUpdate);

        return () => {
            if (frame !== 0) {
                window.cancelAnimationFrame(frame);
            }

            window.removeEventListener('scroll', queueUpdate);
            window.removeEventListener('resize', queueUpdate);
        };
    }, []);

    useEffect(() => {
        if (!open) {
            return;
        }

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        window.addEventListener('keydown', closeOnEscape);

        return () => {
            document.body.style.overflow = previousOverflow;
            window.removeEventListener('keydown', closeOnEscape);
        };
    }, [open]);

    const followLink = (href: string) => {
        setActiveHref(href);
        setOpen(false);
    };

    return (
        <>
            <div className="home-scroll-progress" aria-hidden="true">
                <span style={{ transform: `scaleX(${scrollProgress})` }} />
            </div>

            <header className="home-glass-nav fixed top-3 left-1/2 z-50 w-[calc(100%-1.5rem)] max-w-[1380px] -translate-x-1/2 md:top-[18px] md:w-[calc(100%-3rem)]">
                <div className="home-mock-nav-bar min-h-16 px-4 md:px-3 md:pl-6">
                    <a
                        href="#top"
                        onClick={() => followLink('#top')}
                        className="flex min-w-0 items-center gap-3"
                        aria-label={`Beranda ${businessName}`}
                    >
                        {logoUrl && (
                            <img
                                src={logoUrl}
                                alt=""
                                className="h-9 w-9 shrink-0 object-contain"
                                width={36}
                                height={36}
                            />
                        )}
                        <span className="truncate text-[11px] font-semibold tracking-[0.14em] text-ink uppercase sm:text-xs">
                            {businessName}
                        </span>
                    </a>

                    <nav
                        aria-label="Navigasi utama"
                        className="hidden items-center gap-1 md:flex"
                    >
                        {primaryLinks.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                onClick={() => followLink(link.href)}
                                aria-current={
                                    activeHref === link.href
                                        ? 'location'
                                        : undefined
                                }
                                className={cn(
                                    'home-nav-link',
                                    activeHref === link.href && 'is-active',
                                )}
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>

                    {contactLink && (
                        <a
                            href={contactLink.href}
                            onClick={() => followLink(contactLink.href)}
                            aria-current={
                                activeHref === contactLink.href
                                    ? 'location'
                                    : undefined
                            }
                            className="home-nav-cta hidden md:inline-flex"
                        >
                            {contactLink.label}
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.4"
                            >
                                <path d="M3.5 12.5 12.5 3.5M5 3.5h7.5V11" />
                            </svg>
                        </a>
                    )}

                    <button
                        type="button"
                        onClick={() => setOpen((value) => !value)}
                        aria-expanded={open}
                        aria-controls="home-mobile-navigation"
                        aria-label={open ? 'Tutup menu' : 'Buka menu'}
                        className="home-menu-button md:hidden"
                    >
                        <span
                            aria-hidden="true"
                            className={cn(
                                'home-menu-line',
                                open && 'translate-y-[5px] rotate-45',
                            )}
                        />
                        <span
                            aria-hidden="true"
                            className={cn(
                                'home-menu-line mt-1',
                                open && 'opacity-0',
                            )}
                        />
                        <span
                            aria-hidden="true"
                            className={cn(
                                'home-menu-line mt-1',
                                open && '-translate-y-[5px] -rotate-45',
                            )}
                        />
                    </button>
                </div>

                <nav
                    id="home-mobile-navigation"
                    aria-label="Navigasi utama"
                    aria-hidden={!open}
                    className={cn(
                        'home-mobile-nav md:hidden',
                        open && 'is-open',
                    )}
                >
                    <ul className="px-2 pb-2">
                        {links.map((link, index) => (
                            <li key={link.href}>
                                <a
                                    href={link.href}
                                    onClick={() => followLink(link.href)}
                                    tabIndex={open ? 0 : -1}
                                    aria-current={
                                        activeHref === link.href
                                            ? 'location'
                                            : undefined
                                    }
                                    className={cn(
                                        'home-mobile-nav-link',
                                        activeHref === link.href && 'is-active',
                                    )}
                                >
                                    <span aria-hidden="true">
                                        {String(index + 1).padStart(2, '0')}
                                    </span>
                                    {link.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>
            </header>
        </>
    );
}
