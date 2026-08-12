import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        // rounded-t-2xl wajib: SidebarInset kini bersudut membulat tanpa
        // overflow-hidden, jadi latar header yang bersudut siku akan
        // menyembul keluar dari lengkungnya.
        //
        // Sengaja TIDAK sticky — yang menggulir di layout ini dokumennya,
        // bukan inset-nya, jadi `sticky` di sini tidak akan pernah aktif.
        <header className="glass-veil flex h-16 shrink-0 items-center gap-2 rounded-t-2xl border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
        </header>
    );
}
