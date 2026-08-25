import { useState } from 'react';

type Props = {
    slug: string;
    name: string;
    tagline: string | null;
    logoUrl: string | null;
    accentColor: string;
    index: string;
    initials: string;
};

export default function HomeUnitCard({
    slug,
    name,
    tagline,
    logoUrl,
    accentColor,
    index,
    initials,
}: Props) {
    const [logoFailed, setLogoFailed] = useState(false);
    const showFallback = !logoUrl || logoFailed;

    return (
        <article
            className="home-unit-card group"
            style={{ '--unit-card-accent': accentColor } as React.CSSProperties}
        >
            <div className="flex items-start justify-between gap-4">
                <span className="font-display text-sm tracking-[0.08em] text-[var(--unit-card-accent)] tabular-nums">
                    {index}
                </span>
                <span className="home-unit-accent" aria-hidden="true" />
            </div>

            <div className="my-8 flex h-24 items-center">
                {showFallback ? (
                    <div className="home-unit-fallback" aria-hidden="true">
                        {initials}
                    </div>
                ) : (
                    <img
                        src={logoUrl ?? undefined}
                        alt={`Logo ${name}`}
                        loading="lazy"
                        onError={() => setLogoFailed(true)}
                        className="max-h-20 max-w-[220px] object-contain object-left transition-transform duration-700 ease-out group-hover:scale-[1.04]"
                    />
                )}
            </div>

            <h3 className="font-display text-[clamp(1.65rem,3vw,2.25rem)] leading-[1.02] font-normal tracking-[-0.025em] text-ink">
                {name}
            </h3>

            {tagline && (
                <p className="mt-3 max-w-[38ch] text-sm leading-[1.65] text-ink-soft">
                    {tagline}
                </p>
            )}

            <a
                href={`/${slug}`}
                target="_blank"
                rel="noopener"
                className="home-unit-link mt-auto pt-8"
            >
                Lihat profil
                <span className="sr-only"> {name} (membuka tab baru)</span>
                <svg
                    aria-hidden="true"
                    viewBox="0 0 14 14"
                    className="h-3 w-3"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="1.4"
                >
                    <path d="M3 11 11 3M5 3h6v6" />
                </svg>
            </a>
        </article>
    );
}
