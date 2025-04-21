<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid,Info , Archive, Lock ,Contact} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'About',
        href: '/about',
        icon: Info,
    },
    {
        title: 'DRA',
        href: '/dra',
        icon: Archive,
    },
    {
        title: 'TestRole',
        href: '/testrole',
        icon: Lock,
    },
    {
        title: 'Roles',
        href: '/users',
        icon: Contact,
    },
];

const footerNavItems: NavItem[] = [

    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('about')">
                            <AppLogo />
                        </Link>


                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>


            <NavMain :items="mainNavItems" />

        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
