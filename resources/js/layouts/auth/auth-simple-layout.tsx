import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div
            className="relative flex min-h-svh flex-col items-center justify-center p-6 font-sans md:p-10"
            style={{ background: 'var(--hero-gradient)' }}
        >
            {/* Motif bintang dari logo — benang merah yang sama dengan hero
                halaman publik (DESIGN_SYSTEM §6). */}
            <div
                aria-hidden="true"
                className="pointer-events-none fixed inset-0 opacity-50"
                style={{ backgroundImage: 'var(--star-motif)' }}
            />

            <div className="relative w-full max-w-sm">
                <div className="glass-panel rounded-brand-lg p-6 md:p-8">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="flex flex-col items-center gap-2 font-medium"
                            >
                                <div className="mb-1 flex h-9 w-9 items-center justify-center rounded-brand-sm">
                                    <AppLogoIcon className="size-9 fill-current text-gold-deep" />
                                </div>
                                <span className="sr-only">{title}</span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="font-display text-xl font-semibold text-ink">
                                    {title}
                                </h1>
                                <p className="text-center text-sm text-ink-soft">
                                    {description}
                                </p>
                            </div>
                        </div>
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
