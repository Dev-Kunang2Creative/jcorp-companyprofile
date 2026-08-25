import { brandUiCopy } from '@/components/public/signature/ayodya-i18n';
import type { BrandLocale } from '@/components/public/signature/ayodya-i18n';
import SignatureSectionLabel from '@/components/public/signature/signature-section-label';
import { stagger } from '@/hooks/use-reveal';
import type { PublicDetail } from '@/types/public';

function paragraphs(text: string): string[] {
    return text
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean);
}

type AboutProps = {
    businessName: string;
    description: string;
    logoUrl: string | null;
    locale?: BrandLocale;
};

export function BrandAboutSection({
    businessName,
    description,
    logoUrl,
    locale = 'id',
}: AboutProps) {
    return (
        <section id="tentang" className="brand-section brand-about-section">
            <div className="signature-container">
                <SignatureSectionLabel>
                    {brandUiCopy[locale].about}
                </SignatureSectionLabel>

                <div className="brand-about-grid">
                    <div className="brand-about-heading reveal">
                        <h2 className="brand-section-title">{businessName}</h2>
                        {logoUrl && (
                            <img
                                src={logoUrl}
                                alt=""
                                loading="lazy"
                                className="brand-about-mark"
                            />
                        )}
                    </div>

                    <div className="brand-story-panel signature-glass reveal">
                        {paragraphs(description).map((paragraph, index) => (
                            <p key={index}>{paragraph}</p>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

type VisionMissionProps = {
    vision: string | null;
    mission: string[] | null;
    locale?: BrandLocale;
};

export function BrandVisionMissionSection({
    vision,
    mission,
    locale = 'id',
}: VisionMissionProps) {
    return (
        <section id="visi-misi" className="brand-section brand-vm-section">
            <div className="signature-container">
                <div className="brand-vm-grid">
                    <div className="brand-vision-column">
                        <SignatureSectionLabel>
                            {brandUiCopy[locale].vision}
                        </SignatureSectionLabel>
                        {vision && (
                            <blockquote className="reveal">{vision}</blockquote>
                        )}
                    </div>

                    {mission && mission.length > 0 && (
                        <div className="brand-mission-column">
                            <SignatureSectionLabel>
                                {brandUiCopy[locale].mission}
                            </SignatureSectionLabel>
                            <ol className="brand-mission-list">
                                {mission.map((item, index) => (
                                    <li
                                        key={index}
                                        className="brand-mission-card signature-glass reveal"
                                        style={stagger(index)}
                                    >
                                        <span
                                            className="brand-mission-number"
                                            aria-hidden="true"
                                        >
                                            {String(index + 1).padStart(2, '0')}
                                        </span>
                                        <p>{item}</p>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}

type DetailsProps = {
    id: string;
    label: string;
    items: PublicDetail[];
    kind: 'featured' | 'highlights' | 'services';
};

export function BrandDetailsSection({ id, label, items, kind }: DetailsProps) {
    return (
        <section
            id={id}
            className={`brand-section brand-details-section brand-details-section--${kind}`}
        >
            <div className="signature-container">
                <SignatureSectionLabel>{label}</SignatureSectionLabel>

                <ul
                    className={`brand-details-grid ${items.length === 1 ? 'is-single' : ''}`.trim()}
                    style={
                        {
                            '--brand-detail-columns': Math.min(
                                items.length,
                                kind === 'highlights' ? 4 : 3,
                            ),
                        } as React.CSSProperties
                    }
                >
                    {items.map((item, index) => (
                        <li
                            // Urutan detail dikunci oleh data client. Index
                            // sengaja menjadi identity yang stabil lintas
                            // locale; memakai judul membuat kartu Ayodya
                            // di-remount saat ID/EN berubah dan kehilangan
                            // status reveal yang sudah diamati.
                            key={index}
                            className="brand-detail-card reveal"
                            style={stagger(index)}
                        >
                            <div
                                className="brand-detail-motif"
                                aria-hidden="true"
                            >
                                <span />
                                <span />
                            </div>
                            <span
                                className="brand-detail-number"
                                aria-hidden="true"
                            >
                                {String(index + 1).padStart(2, '0')}
                            </span>
                            <h3>{item.title}</h3>
                            <p>{item.description}</p>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
