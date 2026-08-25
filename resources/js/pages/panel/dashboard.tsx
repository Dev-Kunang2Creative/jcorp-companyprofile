import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    CircleAlert,
    Images,
    ListPlus,
    PhoneCall,
} from 'lucide-react';
import type { CSSProperties } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/panel';
import { index as catalogIndex } from '@/routes/panel/catalog';
import { index as portfolioIndex } from '@/routes/panel/portfolio';
import { edit as profileEdit } from '@/routes/panel/profile';

type BusinessSummary = {
    slug: string;
    name: string;
    is_published: boolean;
    has_portfolio: boolean;
    catalog_count: number;
    portfolio_count: number;
    accent_color: string;
};

type Props = {
    business: {
        slug: string;
        name: string;
        is_parent: boolean;
        is_published: boolean;
        has_portfolio: boolean;
        has_contact: boolean;
        accent_color: string;
        public_url: string;
    };
    counts: {
        catalog: number;
        portfolio: number;
    };
    switchableBusinesses: BusinessSummary[] | null;
};

export default function PanelDashboard({
    business,
    counts,
    switchableBusinesses,
}: Props) {
    const warnings = [
        !business.is_parent && counts.catalog === 0
            ? 'Katalog atau layanan belum memiliki isi.'
            : null,
        business.has_portfolio && counts.portfolio === 0
            ? 'Galeri aktif, tetapi belum memiliki foto.'
            : null,
        !business.has_contact ? 'Informasi kontak utama masih kosong.' : null,
    ].filter((warning): warning is string => warning !== null);

    return (
        <>
            <Head title="Dashboard" />

            <main
                className="panel-page space-y-7"
                style={
                    {
                        '--panel-accent': business.accent_color,
                    } as CSSProperties
                }
            >
                <div className="panel-page-header">
                    <Heading
                        title="Dashboard"
                        description={`Ruang kerja untuk mengelola ${business.name}`}
                    />

                    <div className="panel-page-actions">
                        <Button variant="outline" asChild>
                            <a
                                href={business.public_url}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Lihat website
                                <ArrowUpRight className="size-4" />
                            </a>
                        </Button>
                    </div>
                </div>

                <section
                    className="panel-stat-grid"
                    aria-label="Ringkasan konten"
                >
                    <StatCard
                        label="Katalog / layanan"
                        value={counts.catalog}
                    />
                    <StatCard
                        label="Galeri"
                        value={business.has_portfolio ? counts.portfolio : '—'}
                        note={
                            business.has_portfolio
                                ? undefined
                                : 'Tidak digunakan'
                        }
                    />
                    <StatCard
                        label="Kontak"
                        value={business.has_contact ? 'Ada' : 'Belum'}
                        compact
                    />
                    <StatCard
                        label="Status publik"
                        value={business.is_published ? 'Tayang' : 'Draf'}
                        compact
                    />
                </section>

                <section aria-labelledby="quick-actions-title">
                    <div className="mb-3">
                        <h2
                            id="quick-actions-title"
                            className="font-legacy-display text-xl font-semibold text-ink"
                        >
                            Aksi cepat
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Buka pekerjaan yang paling sering digunakan.
                        </p>
                    </div>

                    <div className="panel-action-grid">
                        <QuickAction
                            href={catalogIndex()}
                            icon={ListPlus}
                            title="Tambah katalog"
                            description="Kelola nama, kategori, harga, urutan, dan foto."
                        />
                        {business.has_portfolio && (
                            <QuickAction
                                href={portfolioIndex()}
                                icon={Images}
                                title="Tambah galeri"
                                description="Unggah dan susun foto hasil kerja."
                            />
                        )}
                        <QuickAction
                            href={profileEdit()}
                            icon={PhoneCall}
                            title="Ubah kontak"
                            description="Perbarui WhatsApp, media sosial, alamat, dan label."
                        />
                    </div>
                </section>

                {warnings.length > 0 && (
                    <section
                        className="panel-surface border-l-[3px] border-l-amber-600 p-4"
                        aria-labelledby="warnings-title"
                    >
                        <div className="flex items-start gap-3">
                            <CircleAlert className="mt-0.5 size-5 shrink-0 text-amber-700" />
                            <div>
                                <h2
                                    id="warnings-title"
                                    className="font-medium text-ink"
                                >
                                    Perlu dilengkapi
                                </h2>
                                <ul className="mt-2 grid gap-1 text-sm leading-6 text-muted-foreground">
                                    {warnings.map((warning) => (
                                        <li key={warning}>• {warning}</li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </section>
                )}

                {switchableBusinesses && (
                    <BusinessOverview businesses={switchableBusinesses} />
                )}
            </main>
        </>
    );
}

function StatCard({
    label,
    value,
    note,
    compact = false,
}: {
    label: string;
    value: string | number;
    note?: string;
    compact?: boolean;
}) {
    return (
        <dl className="panel-stat-card">
            <dt className="text-xs tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </dt>
            <dd
                className={
                    compact
                        ? 'mt-3 text-xl font-semibold text-ink'
                        : 'panel-stat-value mt-3 text-ink'
                }
            >
                {value}
            </dd>
            {note && (
                <dd className="mt-2 text-xs text-muted-foreground">{note}</dd>
            )}
        </dl>
    );
}

function QuickAction({
    href,
    icon: Icon,
    title,
    description,
}: {
    href: ReturnType<typeof catalogIndex>;
    icon: typeof ListPlus;
    title: string;
    description: string;
}) {
    return (
        <Link href={href} className="panel-action-card group">
            <div>
                <Icon className="mb-5 size-5 text-brass" />
                <h3 className="font-medium text-ink">{title}</h3>
                <p className="mt-1 text-sm leading-5 text-muted-foreground">
                    {description}
                </p>
            </div>
            <ArrowUpRight className="size-4 shrink-0 text-muted-foreground transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
        </Link>
    );
}

function BusinessOverview({ businesses }: { businesses: BusinessSummary[] }) {
    return (
        <section aria-labelledby="business-overview-title">
            <div className="mb-3">
                <h2
                    id="business-overview-title"
                    className="font-legacy-display text-xl font-semibold text-ink"
                >
                    Ringkasan seluruh usaha
                </h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    Status publik dan jumlah konten dalam satu tampilan.
                </p>
            </div>

            <div className="panel-table-shell">
                <table className="panel-table">
                    <thead>
                        <tr>
                            <th>Usaha</th>
                            <th>Status</th>
                            <th>Katalog</th>
                            <th>Galeri</th>
                            <th className="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {businesses.map((item) => (
                            <tr key={item.slug}>
                                <td>
                                    <div className="flex items-center gap-3">
                                        <span
                                            className="h-8 w-1 shrink-0 rounded-full"
                                            style={{
                                                backgroundColor:
                                                    item.accent_color,
                                            }}
                                            aria-hidden="true"
                                        />
                                        <span className="font-medium text-ink">
                                            {item.name}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <StatusBadge
                                        published={item.is_published}
                                    />
                                </td>
                                <td>{item.catalog_count}</td>
                                <td>
                                    {item.has_portfolio
                                        ? item.portfolio_count
                                        : 'Tidak digunakan'}
                                </td>
                                <td className="text-right">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={dashboard({
                                                query: {
                                                    business: item.slug,
                                                },
                                            })}
                                        >
                                            Kelola
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <ul className="panel-mobile-list">
                {businesses.map((item) => (
                    <li key={item.slug} className="panel-mobile-card">
                        <div className="flex items-start justify-between gap-3">
                            <div className="flex min-w-0 items-center gap-3">
                                <span
                                    className="h-10 w-1 shrink-0 rounded-full"
                                    style={{
                                        backgroundColor: item.accent_color,
                                    }}
                                    aria-hidden="true"
                                />
                                <div className="min-w-0">
                                    <p className="truncate font-medium text-ink">
                                        {item.name}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {item.catalog_count} katalog
                                        {item.has_portfolio
                                            ? ` · ${item.portfolio_count} galeri`
                                            : ''}
                                    </p>
                                </div>
                            </div>
                            <StatusBadge published={item.is_published} />
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            className="mt-4 w-full"
                        >
                            <Link
                                href={dashboard({
                                    query: { business: item.slug },
                                })}
                            >
                                Kelola usaha
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </section>
    );
}

function StatusBadge({ published }: { published: boolean }) {
    return (
        <span
            className={
                published
                    ? 'panel-status panel-status--published'
                    : 'panel-status panel-status--draft'
            }
        >
            {published ? 'Tayang' : 'Belum terbit'}
        </span>
    );
}
