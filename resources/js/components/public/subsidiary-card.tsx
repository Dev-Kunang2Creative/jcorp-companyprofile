import { Link } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Kartu anak usaha di etalase induk.
 *
 * Latar PADAT, bukan kaca — sama alasannya dengan kartu katalog
 * (DESIGN_SYSTEM §5.1): kartu dalam grid tidak memakai backdrop-filter.
 */

type Props = {
    slug: string;
    name: string;
    tagline: string | null;
    logoUrl: string | null;
    initials: string;
};

export default function SubsidiaryCard({
    slug,
    name,
    tagline,
    logoUrl,
    initials,
}: Props) {
    const [logoFailed, setLogoFailed] = useState(false);

    const showFallback = !logoUrl || logoFailed;

    return (
        <Link
            href={`/${slug}`}
            className="group flex flex-col rounded-brand-md border border-line bg-white p-5 transition-shadow hover:shadow-[0_6px_20px_rgb(90_72_40/0.10)] md:p-6"
        >
            {showFallback ? (
                // Anak usaha yang belum punya logo tetap tampil rapi lewat
                // lencana inisial, bukan kotak kosong (spec §10).
                <div
                    className="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#F7F1E4] font-display text-lg font-semibold text-gold-deep"
                    aria-hidden="true"
                >
                    {initials}
                </div>
            ) : (
                <img
                    src={logoUrl ?? undefined}
                    alt=""
                    onError={() => setLogoFailed(true)}
                    className="mb-4 h-14 w-14 object-contain"
                />
            )}

            <h3 className="mb-1 font-display text-lg font-semibold text-ink md:text-xl">
                {name}
            </h3>

            {tagline && (
                <p className="mb-4 line-clamp-3 text-[14px] leading-relaxed text-ink-soft">
                    {tagline}
                </p>
            )}

            <span className="mt-auto text-[13px] font-medium text-gold-deep group-hover:underline group-hover:underline-offset-4">
                Lihat profil →
            </span>
        </Link>
    );
}
