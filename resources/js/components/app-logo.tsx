import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-brand-sm bg-white/90 p-0.5 shadow-xs ring-1 ring-black/5">
                <img
                    src="/images/brand/jcorp-badge-48.png"
                    alt="J-Corporate Group"
                    className="size-6 object-contain"
                />
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
