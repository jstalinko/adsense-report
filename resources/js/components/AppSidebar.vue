<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CircleDollarSignIcon, Folder, LayoutGrid, ListCheckIcon, PlusCircleIcon, User2Icon } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { isAdmin } from '@/lib/utils';
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title:'Laporan',
        href:'/laporan',
        icon:ListCheckIcon
    },
    {
        title:'Input Laporan',
        href:'/laporan/add',
        icon:PlusCircleIcon
    },
    {
        title:'Exchange Rates',
        href:'/rates',
        icon:CircleDollarSignIcon
    }
];

const footerNavItems: NavItem[] = [
    {
        title:'Manage User',
        href:'/users',
        icon:User2Icon
    }
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter >
            <NavFooter v-if="isAdmin()" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
