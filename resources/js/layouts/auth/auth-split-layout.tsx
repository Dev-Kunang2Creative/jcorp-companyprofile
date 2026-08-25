import { Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSplitLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const { name } = usePage().props;

    return (
        <div className="relative grid h-dvh flex-col items-center justify-center px-8 font-sans sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            {/* Panel kiri: gradasi merek + motif bintang, bukan blok gelap.
                Project ini bertema terang saja (DESIGN_SYSTEM §11). */}
            <div className="relative hidden h-full flex-col p-10 lg:flex">
                <Link
                    href={home()}
                    className="glass-panel relative z-20 flex w-fit items-center rounded-full px-5 py-2.5 font-legacy-display text-lg font-semibold text-ink"
                >
                    <AppLogoIcon className="mr-2 size-8 fill-current text-brass" />
                    {name}
                </Link>
            </div>
            <div className="w-full lg:p-8">
                <div className="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <Link
                        href={home()}
                        className="relative z-20 flex items-center justify-center lg:hidden"
                    >
                        <AppLogoIcon className="h-10 fill-current text-brass sm:h-12" />
                    </Link>
                    <div className="flex flex-col items-start gap-2 text-left sm:items-center sm:text-center">
                        <h1 className="font-legacy-display text-xl font-semibold text-ink">
                            {title}
                        </h1>
                        <p className="text-sm text-balance text-ink-soft">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
