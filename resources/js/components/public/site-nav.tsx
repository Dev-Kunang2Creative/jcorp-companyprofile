import { useState } from 'react';
import WhatsappButton from '@/components/public/whatsapp-button';
import { cn } from '@/lib/utils';

/**
 * Navigasi — salah satu dari TIGA panel kaca yang diizinkan per layar
 * (DESIGN_SYSTEM §5.1). Kelas .glass-panel sudah memuat cadangan untuk
 * browser tanpa dukungan backdrop-filter.
 *
 * Mobile: tautan berubah jadi panel geser di bawah tombol menu — bukan
 * tautan mendatar yang dikecilkan (DESIGN_SYSTEM §7).
 */

type NavLink = { label: string; href: string };

type Props = {
    businessName: string;
    logoUrl: string | null;
    whatsapp?: string;
    links: NavLink[];
};

export default function SiteNav({
    businessName,
    logoUrl,
    whatsapp,
    links,
}: Props) {
    const [open, setOpen] = useState(false);

    return (
        <header className="glass-panel sticky top-0 z-30 border-x-0 border-t-0">
            <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 md:px-8">
                <a
                    href="#top"
                    className="flex items-center gap-3"
                    aria-label={`Beranda ${businessName}`}
                >
                    {logoUrl && (
                        <img
                            src={logoUrl}
                            alt=""
                            className="h-9 w-auto md:h-10"
                            width={40}
                            height={40}
                        />
                    )}
                    <span className="font-display text-[15px] font-semibold tracking-tight text-ink md:text-base">
                        {businessName}
                    </span>
                </a>

                <nav
                    className="hidden items-center gap-6 md:flex"
                    aria-label="Navigasi utama"
                >
                    {links.map((link) => (
                        <a
                            key={link.href}
                            href={link.href}
                            className="text-sm font-medium text-ink-soft transition-colors hover:text-gold-deep"
                        >
                            {link.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-2">
                    {whatsapp && (
                        <WhatsappButton
                            number={whatsapp}
                            businessName={businessName}
                            className="hidden px-5 py-2 text-[13.5px] md:inline-flex"
                        >
                            WhatsApp
                        </WhatsappButton>
                    )}

                    <button
                        type="button"
                        onClick={() => setOpen((v) => !v)}
                        aria-expanded={open}
                        aria-controls="nav-mobile"
                        aria-label={open ? 'Tutup menu' : 'Buka menu'}
                        className="flex h-11 w-11 items-center justify-center rounded-lg border border-line bg-white/60 md:hidden"
                    >
                        <span className="sr-only">
                            {open ? 'Tutup menu' : 'Buka menu'}
                        </span>
                        <span aria-hidden="true" className="relative block">
                            <span
                                className={cn(
                                    'block h-px w-4 bg-ink transition-transform',
                                    open && 'translate-y-[5px] rotate-45',
                                )}
                            />
                            <span
                                className={cn(
                                    'mt-[4px] block h-px w-4 bg-ink transition-opacity',
                                    open && 'opacity-0',
                                )}
                            />
                            <span
                                className={cn(
                                    'mt-[4px] block h-px w-4 bg-ink transition-transform',
                                    open && '-translate-y-[5px] -rotate-45',
                                )}
                            />
                        </span>
                    </button>
                </div>
            </div>

            {open && (
                <nav
                    id="nav-mobile"
                    aria-label="Navigasi utama"
                    className="border-t border-line/70 px-4 pb-4 md:hidden"
                >
                    <ul className="flex flex-col">
                        {links.map((link) => (
                            <li key={link.href}>
                                <a
                                    href={link.href}
                                    onClick={() => setOpen(false)}
                                    className="block py-3 text-[15px] font-medium text-ink-soft"
                                >
                                    {link.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>
            )}
        </header>
    );
}
