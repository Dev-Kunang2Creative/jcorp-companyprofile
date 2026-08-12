import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    Images,
    LayoutGrid,
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

    const mainNavItems: NavItem[] = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Katalog', href: catalogIndex(), icon: ShoppingBag },
    ];

    // Tidak semua anak usaha memamerkan hasil kerja — dessert, logistik, dan
    // properti menampilkan katalog saja (spec §3). Sakelarnya diatur
    // super-admin lewat panel Kelola Anak Usaha.
    if (auth.hasPortfolio) {
        mainNavItems.push({
            title: 'Portfolio',
            href: portfolioIndex(),
            icon: Images,
        });
    }

    mainNavItems.push({
        title: 'Info Kontak',
        href: profileEdit(),
        icon: Phone,
    });

    // Menu ini hanya ditampilkan ke super-admin. Yang menahan sebenarnya
    // adalah middleware dan Policy di server — menyembunyikan menu di sini
    // semata soal kerapian tampilan, bukan pengamanan (spec §6).
    if (auth.isSuperAdmin) {
        mainNavItems.push(
            { title: 'Kelola Akun', href: usersIndex(), icon: Users },
            {
                title: 'Kelola Anak Usaha',
                href: businessesIndex(),
                icon: Building2,
            },
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

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
