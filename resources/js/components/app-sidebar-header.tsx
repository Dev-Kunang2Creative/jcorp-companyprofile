import { Breadcrumbs } from '@/components/breadcrumbs';
import { BusinessContextSwitcher } from '@/components/panel/business-context-switcher';
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
        <header className="glass-veil flex min-h-16 shrink-0 items-center justify-between gap-3 rounded-t-2xl border-b px-3 py-2 transition-[width,height] duration-200 md:px-5">
            <div className="flex min-w-0 items-center gap-2">
                <SidebarTrigger />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <BusinessContextSwitcher />
        </header>
    );
}
