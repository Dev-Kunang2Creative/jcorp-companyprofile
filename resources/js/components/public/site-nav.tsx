import { useState } from 'react';
import WhatsappButton from '@/components/public/whatsapp-button';
import { cn } from '@/lib/utils';

/**
 * Navigasi — putih 88% dengan garis rambut di bawahnya.
 *
 * Sejak arah A+B (23 Agustus 2026) bar ini tidak lagi berpermukaan kaca.
 * Alasannya sederhana: latar halaman sudah putih, dan kaca di atas putih
 * polos tidak membiaskan apa pun — efeknya sia-sia sambil tetap membayar
 * biaya render (DESIGN_SYSTEM §5.5).
 *
 * Yang dipertahankan dari kaca cuma satu: sedikit blur, supaya teks yang
 * lewat di belakangnya saat digulir tidak terbaca mengganggu.
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
        <header className="sticky top-0 z-30 border-b border-hair bg-paper/[.88] backdrop-blur-md">
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
                    <span className="text-[11.5px] font-semibold tracking-[0.13em] text-ink uppercase md:text-xs">
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
                            className="text-[11px] tracking-[0.1em] text-ink-soft uppercase transition-colors hover:text-brass"
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
                        className="flex h-11 w-11 items-center justify-center rounded-sm border border-hair md:hidden"
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
                    className="border-t border-hair px-4 pb-4 md:hidden"
                >
                    <ul className="flex flex-col">
                        {links.map((link) => (
                            <li key={link.href}>
                                <a
                                    href={link.href}
                                    onClick={() => setOpen(false)}
                                    className="block border-b border-hair-soft py-3 text-[13px] tracking-[0.08em] text-ink-soft uppercase last:border-b-0"
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
