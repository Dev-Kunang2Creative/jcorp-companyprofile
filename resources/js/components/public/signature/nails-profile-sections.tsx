import SignatureSectionLabel from '@/components/public/signature/signature-section-label';
import { stagger } from '@/hooks/use-reveal';
import type { PublicDetail } from '@/types/public';

type AboutProps = {
    description: string;
    logoUrl: string | null;
};

export function NailsAboutSection({ description, logoUrl }: AboutProps) {
    return (
        <section id="tentang" className="nails-section">
            <div className="signature-container">
                <SignatureSectionLabel>Tentang Kami</SignatureSectionLabel>

                <div className="nails-about-grid">
                    <div className="nails-about-heading reveal">
                        <h2 className="nails-section-title">
                            Tentang
                            <br />
                            <em>Kami</em>
                        </h2>

                        {logoUrl && (
                            <img
                                src={logoUrl}
                                alt=""
                                aria-hidden="true"
                                loading="lazy"
                                className="nails-about-mark"
                            />
                        )}
                    </div>

                    <div
                        className="nails-story-panel signature-glass reveal"
                        style={stagger(1)}
                    >
                        <p>{description}</p>
                    </div>
                </div>
            </div>
        </section>
    );
}

type VisionMissionProps = {
    vision: string | null;
    mission: string[] | null;
};

export function NailsVisionMissionSection({
    vision,
    mission,
}: VisionMissionProps) {
    return (
        <section id="visi-misi" className="nails-section nails-vm-section">
            <div className="signature-container">
                <SignatureSectionLabel>Visi &amp; Misi</SignatureSectionLabel>

                <div className="nails-vm-grid">
                    {vision && (
                        <div className="nails-vision-card reveal">
                            <p className="signature-small-label">Visi</p>
                            <blockquote>{vision}</blockquote>
                        </div>
                    )}

                    {mission && mission.length > 0 && (
                        <div className="nails-mission-list">
                            <p className="signature-small-label reveal">Misi</p>

                            {mission.map((item, index) => (
                                <article
                                    key={index}
                                    className="nails-mission-card signature-glass reveal"
                                    style={stagger(index)}
                                >
                                    <span
                                        className="nails-mission-number"
                                        aria-hidden="true"
                                    >
                                        {String(index + 1).padStart(2, '0')}
                                    </span>
                                    <p>{item}</p>
                                </article>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}

type HighlightsProps = {
    items: PublicDetail[];
    businessName: string;
};

export function NailsHighlightsSection({
    items,
    businessName,
}: HighlightsProps) {
    return (
        <section id="keunggulan" className="nails-section">
            <div className="signature-container">
                <SignatureSectionLabel>
                    Mengapa memilih {businessName}
                </SignatureSectionLabel>

                <ul className="nails-benefit-grid">
                    {items.map((item, index) => (
                        <li
                            key={`${item.title}-${index}`}
                            className="nails-benefit-card reveal"
                            style={stagger(index)}
                        >
                            <h3>{item.title}</h3>
                            <p>{item.description}</p>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

type ServicesProps = {
    id?: string;
    title: string;
    items: PublicDetail[];
};

export function NailsServicesSection({
    id = 'layanan',
    title,
    items,
}: ServicesProps) {
    return (
        <section id={id} className="nails-section nails-services-section">
            <div className="signature-container">
                <SignatureSectionLabel>{title}</SignatureSectionLabel>

                <ul className="nails-services-grid">
                    {items.map((item, index) => (
                        <li
                            key={`${item.title}-${index}`}
                            className="nails-service-card reveal"
                            style={stagger(index)}
                        >
                            <div
                                className="nails-service-swatch"
                                aria-hidden="true"
                            />
                            <h3>{item.title}</h3>
                            <p>{item.description}</p>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
