import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    Images,
    LayoutDashboard,
    Phone,
    ShoppingBag,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/panel';
import { index as businessesIndex } from '@/routes/panel/businesses';
import { index as catalogIndex } from '@/routes/panel/catalog';
import { index as portfolioIndex } from '@/routes/panel/portfolio';
import { edit as profileEdit } from '@/routes/panel/profile';
import { index as usersIndex } from '@/routes/panel/users';
import type { Auth, NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;

    const summaryNavItems: NavItem[] = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
    ];

    const contentNavItems: NavItem[] = [
        { title: 'Katalog / Layanan', href: catalogIndex(), icon: ShoppingBag },
    ];

    // Tidak semua anak usaha memamerkan hasil kerja — dessert, logistik, dan
    // properti menampilkan katalog saja (spec §3). Sakelarnya diatur
    // super-admin lewat panel Kelola Anak Usaha.
    if (auth.hasPortfolio) {
        contentNavItems.push({
            title: 'Galeri',
            href: portfolioIndex(),
            icon: Images,
        });
    }

    contentNavItems.push({
        title: 'Profil & Kontak',
        href: profileEdit(),
        icon: Phone,
    });

    const managementNavItems: NavItem[] = [];

    // Menu ini hanya ditampilkan ke super-admin. Yang menahan sebenarnya
    // adalah middleware dan Policy di server — menyembunyikan menu di sini
    // semata soal kerapian tampilan, bukan pengamanan (spec §6).
    if (auth.isSuperAdmin) {
        managementNavItems.push(
            { title: 'Anak Usaha', href: businessesIndex(), icon: Building2 },
            { title: 'Akun & Akses', href: usersIndex(), icon: Users },
        );
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="py-2">
                <NavMain label="Ringkasan" items={summaryNavItems} />
                <NavMain label="Konten" items={contentNavItems} />
                {managementNavItems.length > 0 && (
                    <NavMain label="Manajemen" items={managementNavItems} />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
