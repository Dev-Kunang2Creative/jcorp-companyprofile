import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/panel';
import { index as catalogIndex } from '@/routes/panel/catalog';
import { index as portfolioIndex } from '@/routes/panel/portfolio';
import { edit as profileEdit } from '@/routes/panel/profile';

type Props = {
    business: {
        slug: string;
        name: string;
        is_published: boolean;
    };
    counts: {
        catalog: number;
        portfolio: number;
    };
    switchableBusinesses: Array<{ slug: string; name: string }> | null;
};

export default function PanelDashboard({
    business,
    counts,
    switchableBusinesses,
}: Props) {
    return (
        <>
            <Head title="Dashboard" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Dashboard"
                    description={`Mengelola ${business.name}`}
                />

                {business.is_published ? (
                    <p className="rounded-md border border-border bg-muted/50 p-3 text-sm">
                        Profil ini <strong>sudah tayang</strong> dan bisa dibuka
                        publik di{' '}
                        <a
                            href={`/${business.slug}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-gold-deep underline underline-offset-4"
                        >
                            /{business.slug}
                        </a>
                        .
                    </p>
                ) : (
                    <p className="rounded-md border border-gold/40 bg-cream-warm p-3 text-sm">
                        Profil ini <strong>belum diterbitkan</strong> — belum
                        bisa dilihat publik. Kontennya tetap bisa diisi dari
                        sini, dan diterbitkan lewat satu sakelar kalau sudah
                        siap.
                    </p>
                )}

                <dl className="grid gap-4 sm:grid-cols-2">
                    <div className="rounded-md border border-border p-4">
                        <dt className="text-sm text-muted-foreground">
                            Item katalog
                        </dt>
                        <dd className="font-display text-3xl font-semibold">
                            {counts.catalog}
                        </dd>
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            className="mt-3"
                        >
                            <Link href={catalogIndex()}>Kelola katalog</Link>
                        </Button>
                    </div>

                    <div className="rounded-md border border-border p-4">
                        <dt className="text-sm text-muted-foreground">
                            Foto portfolio
                        </dt>
                        <dd className="font-display text-3xl font-semibold">
                            {counts.portfolio}
                        </dd>
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            className="mt-3"
                        >
                            <Link href={portfolioIndex()}>
                                Kelola portfolio
                            </Link>
                        </Button>
                    </div>
                </dl>

                <section className="rounded-md border border-border p-4">
                    <h2 className="mb-1 text-sm font-medium">Info kontak</h2>
                    <p className="mb-3 text-sm text-muted-foreground">
                        Nomor WhatsApp, Instagram, alamat, jam buka, dan sebutan
                        section.
                    </p>
                    <Button variant="outline" size="sm" asChild>
                        <Link href={profileEdit()}>Ubah info kontak</Link>
                    </Button>
                </section>

                {switchableBusinesses && (
                    <section className="rounded-md border border-border p-4">
                        <h2 className="mb-1 text-sm font-medium">
                            Pindah anak usaha
                        </h2>
                        <p className="mb-3 text-sm text-muted-foreground">
                            Sebagai super-admin, Anda bisa mengelola konten anak
                            usaha mana pun.
                        </p>

                        <ul className="flex flex-wrap gap-2">
                            {switchableBusinesses.map((item) => (
                                <li key={item.slug}>
                                    <Button
                                        variant={
                                            item.slug === business.slug
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="sm"
                                        asChild
                                    >
                                        <Link
                                            href={dashboard({
                                                query: { business: item.slug },
                                            })}
                                        >
                                            {item.name}
                                        </Link>
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </>
    );
}
