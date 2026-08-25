import { ArrowDownRight, ArrowUpRight } from 'lucide-react';
import { useEffect, useState } from 'react';
import SignatureWhatsappButton from '@/components/public/signature/signature-whatsapp-button';
import { whatsappUrl } from '@/lib/whatsapp';

type NavLink = { label: string; href: string };
type NavLocale = 'id' | 'en';

type LanguageControl = {
    value: NavLocale;
    onChange: (locale: NavLocale) => void;
};

type Props = {
    businessName: string;
    logoUrl: string | null;
    whatsapp?: string;
    links: NavLink[];
    language?: LanguageControl;
};

/** Navbar keluarga signature: kaca terang, tiga zona, menu mobile aksesibel. */
export default function SignatureNav({
    businessName,
    logoUrl,
    whatsapp,
    links,
    language,
}: Props) {
    const [open, setOpen] = useState(false);
    const isEnglish = language?.value === 'en';

    useEffect(() => {
        if (!open) {
            return;
        }

        const previousOverflow = document.body.style.overflow;
        const desktopNavigation = window.matchMedia('(min-width: 70.0625rem)');
        document.body.style.overflow = 'hidden';

        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        const closeOnDesktop = (event: MediaQueryListEvent) => {
            if (event.matches) {
                setOpen(false);
            }
        };

        window.addEventListener('keydown', closeOnEscape);
        desktopNavigation.addEventListener('change', closeOnDesktop);

        return () => {
            document.body.style.overflow = previousOverflow;
            window.removeEventListener('keydown', closeOnEscape);
            desktopNavigation.removeEventListener('change', closeOnDesktop);
        };
    }, [open]);

    return (
        <header className="signature-header">
            {open && (
                <button
                    type="button"
                    className="signature-mobile-backdrop"
                    aria-label={
                        isEnglish
                            ? 'Close navigation menu'
                            : 'Tutup menu navigasi'
                    }
                    tabIndex={-1}
                    onClick={() => setOpen(false)}
                />
            )}

            <div
                className={`signature-container signature-nav-shell signature-glass ${open ? 'is-menu-open' : ''}`}
            >
                <a
                    href="#top"
                    className="signature-brand"
                    aria-label={`${isEnglish ? 'Home' : 'Beranda'} ${businessName}`}
                >
                    {logoUrl && (
                        <span className="signature-brand-mark">
                            <img src={logoUrl} alt="" width={60} height={38} />
                        </span>
                    )}
                    <span className="signature-brand-name">{businessName}</span>
                </a>

                <nav
                    className="signature-desktop-nav"
                    aria-label={
                        isEnglish ? 'Main navigation' : 'Navigasi utama'
                    }
                >
                    {links.map((link) => (
                        <a key={link.href} href={link.href}>
                            {link.label}
                        </a>
                    ))}
                </nav>

                <div className="signature-nav-actions">
                    {language && (
                        <div
                            className="signature-language-switch"
                            role="group"
                            aria-label={isEnglish ? 'Language' : 'Bahasa'}
                        >
                            {(['id', 'en'] as const).map((option) => (
                                <button
                                    key={option}
                                    type="button"
                                    className={
                                        language.value === option
                                            ? 'is-active'
                                            : undefined
                                    }
                                    aria-pressed={language.value === option}
                                    onClick={() => language.onChange(option)}
                                >
                                    {option.toUpperCase()}
                                </button>
                            ))}
                        </div>
                    )}

                    {whatsapp && (
                        <SignatureWhatsappButton
                            number={whatsapp}
                            businessName={businessName}
                            className="signature-nav-cta"
                            locale={language?.value}
                        >
                            WhatsApp
                        </SignatureWhatsappButton>
                    )}

                    <button
                        type="button"
                        onClick={() => setOpen((value) => !value)}
                        aria-expanded={open}
                        aria-controls="signature-mobile-menu"
                        aria-label={
                            isEnglish
                                ? open
                                    ? 'Close menu'
                                    : 'Open menu'
                                : open
                                  ? 'Tutup menu'
                                  : 'Buka menu'
                        }
                        className="signature-menu-toggle"
                    >
                        <span
                            className="signature-menu-lines"
                            aria-hidden="true"
                        />
                    </button>
                </div>

                <nav
                    id="signature-mobile-menu"
                    aria-label={
                        isEnglish ? 'Mobile navigation' : 'Navigasi mobile'
                    }
                    className={`signature-mobile-menu ${open ? 'is-open' : ''}`}
                >
                    {links.map((link) => (
                        <a
                            key={link.href}
                            href={link.href}
                            onClick={() => setOpen(false)}
                        >
                            <span>{link.label}</span>
                            <ArrowDownRight aria-hidden="true" />
                        </a>
                    ))}

                    {whatsapp && (
                        <a
                            href={whatsappUrl(
                                whatsapp,
                                businessName,
                                undefined,
                                language?.value,
                            )}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="signature-mobile-wa"
                        >
                            <span>WhatsApp</span>
                            <ArrowUpRight aria-hidden="true" />
                        </a>
                    )}
                </nav>
            </div>
        </header>
    );
}
