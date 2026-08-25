import { router, usePage } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/panel';
import type { Auth } from '@/types';

/**
 * Konteks usaha aktif untuk seluruh halaman panel.
 *
 * Perpindahan selalu melewati Dashboard dengan query `business`. Controller
 * yang sudah ada menyimpan pilihan itu ke sesi, sehingga tautan panel lain
 * tetap memakai aturan kepemilikan dan konteks yang sama seperti sebelumnya.
 */
export function BusinessContextSwitcher() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const business = auth.business;

    if (!business) {
        return null;
    }

    function switchBusiness(slug: string) {
        if (slug === business?.slug) {
            return;
        }

        router.get(dashboard({ query: { business: slug } }).url);
    }

    return (
        <div className="panel-context-switcher">
            <span
                className="panel-context-swatch"
                style={{ backgroundColor: business.accentColor }}
                aria-hidden="true"
            />

            <div className="min-w-0">
                <span className="hidden text-[0.65rem] leading-none tracking-[0.14em] text-muted-foreground uppercase lg:block">
                    Mengelola
                </span>

                {auth.switchableBusinesses ? (
                    <select
                        aria-label="Pilih usaha yang dikelola"
                        value={business.slug}
                        onChange={(event) => switchBusiness(event.target.value)}
                        className="panel-context-select"
                    >
                        {auth.switchableBusinesses.map((item) => (
                            <option key={item.slug} value={item.slug}>
                                {item.name}
                            </option>
                        ))}
                    </select>
                ) : (
                    <p className="panel-context-name">{business.name}</p>
                )}
            </div>

            <span
                className={
                    business.isPublished
                        ? 'panel-status panel-status--published'
                        : 'panel-status panel-status--draft'
                }
            >
                {business.isPublished ? 'Tayang' : 'Belum terbit'}
            </span>

            <Button
                variant="outline"
                size="sm"
                asChild
                className="panel-public-link"
            >
                <a
                    href={business.publicUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={`Lihat website ${business.name}`}
                >
                    <span className="hidden xl:inline">Lihat website</span>
                    <ExternalLink className="size-3.5" />
                </a>
            </Button>
        </div>
    );
}
