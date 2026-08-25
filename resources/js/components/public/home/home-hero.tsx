import type { ParentBusiness, SubsidiaryCard } from '@/types/home';

type Props = {
    parent: ParentBusiness;
    subsidiaries: SubsidiaryCard[];
    hasAbout: boolean;
    hasUnits: boolean;
};

export default function HomeHero({
    parent,
    subsidiaries,
    hasAbout,
    hasUnits,
}: Props) {
    const title = splitAtComma(parent.tagline ?? parent.name);
    const titleLead = splitBeforeLastWord(title.first);
    const orbitNotes = uniqueOrbitNotes(subsidiaries);

    return (
        <section id="top" className="home-mock-hero">
            <div className="home-mock-hero-grid">
                <div className="home-mock-hero-copy">
                    <p className="home-mock-eyebrow">{parent.name}</p>

                    <h1 className="home-mock-hero-title">
                        <span>
                            {titleLead.first && <span>{titleLead.first}</span>}
                            <span>{titleLead.last}</span>
                        </span>
                        {title.second && <em> {title.second}</em>}
                    </h1>

                    <div className="home-mock-hero-bottom home-mock-hero-bottom-actions-only">
                        <div className="home-mock-hero-actions">
                            {hasUnits && (
                                <a
                                    href="#usaha"
                                    className="home-action-primary"
                                >
                                    Lihat Unit Usaha
                                    <ArrowUpRight />
                                </a>
                            )}
                            {hasAbout && (
                                <a
                                    href="#tentang"
                                    className="home-action-secondary"
                                >
                                    Tentang Kami
                                </a>
                            )}
                        </div>
                    </div>
                </div>

                <div
                    className="home-logo-stage home-mock-logo-stage"
                    aria-label={`Ekosistem ${subsidiaries.length} unit usaha`}
                >
                    <div
                        className="home-orbit home-orbit-outer"
                        aria-hidden="true"
                    />
                    <div
                        className="home-orbit home-orbit-inner"
                        aria-hidden="true"
                    />

                    {orbitNotes.map((note, index) => (
                        <span
                            key={note.label}
                            className={`home-orbit-note home-orbit-note-${index + 1}`}
                            style={
                                {
                                    '--orbit-note-color': note.accentColor,
                                } as React.CSSProperties
                            }
                        >
                            <i aria-hidden="true" />
                            {note.label}
                        </span>
                    ))}

                    <div className="home-logo-glass">
                        {parent.logo_url ? (
                            <img
                                src={parent.logo_url}
                                alt={`Logo ${parent.name}`}
                                loading="eager"
                                fetchPriority="high"
                                className="h-[58%] w-[58%] object-contain drop-shadow-[0_18px_22px_rgb(68_47_17/0.12)]"
                            />
                        ) : (
                            <span className="max-w-[11ch] text-center font-display text-3xl leading-tight text-ink">
                                {parent.name}
                            </span>
                        )}
                    </div>

                    {hasUnits && (
                        <div className="home-mock-stage-caption">
                            <strong>
                                {String(subsidiaries.length).padStart(2, '0')}
                            </strong>
                            <span>Unit Usaha</span>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}

function uniqueOrbitNotes(subsidiaries: SubsidiaryCard[]): Array<{
    label: string;
    accentColor: string;
}> {
    const notes = new Map<string, string>();

    subsidiaries.forEach((subsidiary) => {
        if (subsidiary.sector_label && !notes.has(subsidiary.sector_label)) {
            notes.set(subsidiary.sector_label, subsidiary.accent_color);
        }
    });

    return Array.from(notes, ([label, accentColor]) => ({
        label,
        accentColor,
    }));
}

function splitAtComma(value: string): { first: string; second: string | null } {
    const commaIndex = value.indexOf(',');

    if (commaIndex === -1) {
        return { first: value, second: null };
    }

    return {
        first: value.slice(0, commaIndex + 1),
        second: value.slice(commaIndex + 1).trim() || null,
    };
}

function splitBeforeLastWord(value: string): {
    first: string | null;
    last: string;
} {
    const lastSpace = value.lastIndexOf(' ');

    if (lastSpace === -1) {
        return { first: null, last: value };
    }

    return {
        first: value.slice(0, lastSpace + 1),
        last: value.slice(lastSpace + 1),
    };
}

function ArrowUpRight() {
    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 16 16"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.4"
        >
            <path d="M3.5 12.5 12.5 3.5M5 3.5h7.5V11" />
        </svg>
    );
}
