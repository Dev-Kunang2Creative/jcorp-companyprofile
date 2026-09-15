import { Link } from '@inertiajs/react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative flex min-h-svh flex-col items-center justify-center p-6 font-sans md:p-10">
            <div className="relative w-full max-w-sm">
                <div className="glass-panel rounded-brand-lg p-6 md:p-8">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="group flex flex-col items-center gap-2 font-medium transition-transform duration-200 hover:scale-105"
                            >
                                <div className="mb-1 flex items-center justify-center">
                                    <img
                                        src="/images/brand/jcorp-logo-300.webp"
                                        alt="J-Corporate Group"
                                        className="h-16 w-auto object-contain drop-shadow-xs"
                                        width={300}
                                        height={293}
                                    />
                                </div>
                                <span className="sr-only">
                                    J-Corporate Group
                                </span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="font-legacy-display text-xl font-semibold text-ink">
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
