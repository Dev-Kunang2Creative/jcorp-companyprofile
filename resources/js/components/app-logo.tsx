import { usePage } from '@inertiajs/react';

import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-brand-sm bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current text-white" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="truncate font-display leading-tight font-semibold text-ink">
                    {name}
                </span>
                <span className="truncate text-[0.66rem] leading-tight tracking-[0.12em] text-muted-foreground uppercase">
                    Panel Konten
                </span>
            </div>
        </>
    );
}
