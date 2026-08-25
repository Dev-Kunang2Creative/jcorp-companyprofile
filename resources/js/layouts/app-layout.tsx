import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return (
        // `font-panel` menjaga panel tetap ber-Jost. Halaman publik sudah
        // pindah ke Inter Tight, dan tanpa pembungkus ini panel ikut
        // berganti huruf tanpa satu pun error — lihat catatan di app.css.
        <div className="font-panel panel-shell">
            <AppLayoutTemplate breadcrumbs={breadcrumbs}>
                {children}
            </AppLayoutTemplate>
        </div>
    );
}
