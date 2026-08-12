import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { portfolio, published } from '@/routes/panel/businesses';

/**
 * Kelola anak usaha, sakelar terbit, dan sakelar portfolio — super-admin saja.
 *
 * Menerbitkan berarti profil langsung bisa diakses siapa pun, jadi
 * dikonfirmasi lewat dialog yang menyebut nama dan akibatnya — bukan sekali
 * klik tanpa peringatan.
 */

type BusinessRow = {
    id: number;
    slug: string;
    name: string;
    is_parent: boolean;
    is_published: boolean;
    has_portfolio: boolean;
    sort_order: number;
    catalog_count: number;
    portfolio_count: number;
};

type Props = {
    businesses: BusinessRow[];
};

/** Sakelar mana yang sedang dikonfirmasi. */
type PendingToggle = {
    business: BusinessRow;
    kind: 'published' | 'portfolio';
};

export default function BusinessesIndex({ businesses }: Props) {
    const [pending, setPending] = useState<PendingToggle | null>(null);
    const [processing, setProcessing] = useState(false);

    function confirmToggle() {
        if (!pending) {
            return;
        }

        const { business, kind } = pending;

        // Route memakai slug sebagai kunci — lihat Business::getRouteKeyName().
        const url =
            kind === 'published'
                ? published(business.slug).url
                : portfolio(business.slug).url;

        const payload =
            kind === 'published'
                ? { is_published: !business.is_published }
                : { has_portfolio: !business.has_portfolio };

        setProcessing(true);

        router.put(url, payload, {
            preserveScroll: true,
            onFinish: () => {
                setProcessing(false);
                setPending(null);
            },
        });
    }

    return (
        <>
            <Head title="Kelola Anak Usaha" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Kelola Anak Usaha"
                    description="Sakelar terbit menentukan apakah profil bisa diakses publik"
                />

                <ul className="grid gap-2">
                    {businesses.map((business) => (
                        <li
                            key={business.id}
                            className="glass-card flex flex-col gap-3 rounded-brand-md p-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div className="min-w-0">
                                <p className="font-medium">
                                    {business.name}
                                    {business.is_parent && (
                                        <span className="ml-2 rounded bg-muted px-1.5 py-0.5 text-xs font-normal">
                                            induk
                                        </span>
                                    )}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    /{business.slug} · {business.catalog_count}{' '}
                                    item
                                    {business.has_portfolio &&
                                        ` · ${business.portfolio_count} foto`}
                                </p>
                            </div>

                            <div className="flex shrink-0 flex-wrap items-center gap-2">
                                <span
                                    className={
                                        business.is_published
                                            ? 'text-sm font-medium text-gold-deep'
                                            : 'text-sm text-muted-foreground'
                                    }
                                >
                                    {business.is_published
                                        ? 'Tayang'
                                        : 'Belum terbit'}
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        setPending({
                                            business,
                                            kind: 'published',
                                        })
                                    }
                                >
                                    {business.is_published
                                        ? 'Sembunyikan'
                                        : 'Terbitkan'}
                                </Button>

                                {/* Induk tidak punya katalog maupun portfolio
                                    sendiri (spec §3), jadi sakelarnya tidak
                                    ditampilkan. */}
                                {!business.is_parent && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            setPending({
                                                business,
                                                kind: 'portfolio',
                                            })
                                        }
                                    >
                                        {business.has_portfolio
                                            ? 'Matikan portfolio'
                                            : 'Nyalakan portfolio'}
                                    </Button>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            </div>

            <Dialog
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{dialogTitle(pending)}</DialogTitle>
                        <DialogDescription>
                            {dialogBody(pending)}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline" disabled={processing}>
                                Batal
                            </Button>
                        </DialogClose>

                        <Button onClick={confirmToggle} disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Ya, lanjutkan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

function dialogTitle(pending: PendingToggle | null): string {
    if (!pending) {
        return '';
    }

    const { business, kind } = pending;

    if (kind === 'published') {
        return business.is_published
            ? `Sembunyikan "${business.name}"?`
            : `Terbitkan "${business.name}"?`;
    }

    return business.has_portfolio
        ? `Matikan portfolio "${business.name}"?`
        : `Nyalakan portfolio "${business.name}"?`;
}

function dialogBody(pending: PendingToggle | null): string {
    if (!pending) {
        return '';
    }

    const { business, kind } = pending;

    if (kind === 'published') {
        return business.is_published
            ? 'Profilnya akan mengembalikan 404 bagi pengunjung. Kontennya tetap tersimpan dan adminnya masih bisa mengelolanya.'
            : `Profilnya akan langsung bisa dibuka siapa pun di /${business.slug}. Pastikan kontennya sudah siap dilihat.`;
    }

    if (business.has_portfolio) {
        return business.portfolio_count > 0
            ? `Menu Portfolio hilang dari panel admin, dan ${business.portfolio_count} foto yang sudah ada tidak lagi tampil di halaman publik. Fotonya TIDAK dihapus — nyalakan lagi kapan saja untuk memunculkannya kembali.`
            : 'Menu Portfolio hilang dari panel admin dan section-nya tidak muncul di halaman publik.';
    }

    return 'Menu Portfolio muncul di panel admin, dan sectionnya tampil di halaman publik begitu ada foto yang diunggah.';
}
