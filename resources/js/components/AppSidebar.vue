<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Boxes,
    ClipboardList,
    LayoutDashboard,
    PackagePlus,
    ShoppingCart,
    Tags,
    TrendingUp,
    UserPlus,
    Users,
    Warehouse,
    type LucideIcon,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

interface NavMenuItem {
    title: string;
    href: string;
    icon: LucideIcon;
}

const sections: { label: string; items: NavMenuItem[] }[] = [
    {
        label: 'Utama',
        items: [{ title: 'Dashboard', href: '/dashboard', icon: LayoutDashboard }],
    },
    {
        label: 'Transaksi',
        items: [
            { title: 'Penjualan', href: '/sales', icon: ShoppingCart },
            { title: 'Pembelian', href: '/purchases', icon: PackagePlus },
        ],
    },
    {
        label: 'Inventori',
        items: [{ title: 'Stock', href: '/stock', icon: Boxes }],
    },
    {
        label: 'Master',
        items: [
            { title: 'Customer', href: '/customers', icon: Users },
            { title: 'Calon Pembeli', href: '/leads', icon: UserPlus },
            { title: 'Tipe Penjualan', href: '/sale-types', icon: Tags },
        ],
    },
    {
        label: 'Laporan',
        items: [
            { title: 'Penjualan', href: '/reports/sales', icon: BarChart3 },
            { title: 'Pembelian', href: '/reports/purchases', icon: ClipboardList },
            { title: 'Profit', href: '/reports/profit', icon: TrendingUp },
            { title: 'Stock', href: '/reports/stock', icon: Warehouse },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :sections="sections" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
