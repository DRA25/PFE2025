<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid,Info , Archive, Lock ,Contact,Wrench,Store,ShoppingCart,SearchCheck,Banknote,Building,User} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'À propos',
        href: '/about',
        icon: Info,
    },





];

// Conditionally add "centre"
if (user.value?.roles?.some((role: any) => role.name === 'admin' )) {
    mainNavItems.push({
        title: 'Centre',
        href: '/centres',
        icon: Building,
    });
}

// Conditionally add "atelier"
if (user.value?.roles?.some((role: any) => role.name === 'admin' )) {
    mainNavItems.push({
        title: 'Gestion des Ateliers',
        href: '/gestionatelier',
        icon: Wrench,
    });
}

// Conditionally add "magasin"
if (user.value?.roles?.some((role: any) => role.name === 'admin' )) {
    mainNavItems.push({
        title: 'Gestion des Magasins',
        href: '/gestionmagasin',
        icon: Store,
    });
}

// Conditionally add "fournisseurs"
if (user.value?.roles?.some((role: any) => role.name === 'admin' || role.name === 'service achat' )) {
    mainNavItems.push({
        title: 'Fournisseur',
        href: '/fournisseurs',
        icon: User,
    });
}
// Conditionally add "Users"
if (user.value?.roles?.some((role: any) => role.name === 'admin')) {
    mainNavItems.push({
        title: 'Utilisateur',
        href: '/users',
        icon: Contact,
    });
}

// Conditionally add "Roles"
if (user.value?.roles?.some((role: any) => role.name === 'admin')) {
    mainNavItems.push({
        title: '\tRôles',
        href: '/roles',
        icon: Contact,
    });
}

// Conditionally add "Atelier"
if (user.value?.roles?.some((role: any) => role.name === 'admin' || role.name === 'service atelier')) {
    mainNavItems.push({
        title: 'Atelier',
        href: '/atelier',
        icon: Wrench,
    });
}
// Conditionally add "Magasin"
if (user.value?.roles?.some((role: any) => role.name === 'admin' || role.name === 'service magasin')) {
    mainNavItems.push({
        title: 'Magasin',
        href: '/magasin',
        icon: Store,
    });
}
    // Conditionally add "Achat"
    if (user.value?.roles?.some((role: any) => role.name === 'admin' || role.name === 'service achat' )) {
        mainNavItems.push({
            title: 'Service Achat',
            href: '/achat',
            icon: ShoppingCart,
        });
    }

// Conditionally add "coordination finnanciere"
if (user.value?.roles?.some((role: any) => role.name === 'admin' || role.name === 'service cf')) {
    mainNavItems.push({
        title: 'S.coordination finnanciere',
        href: '/scf',
        icon: SearchCheck,
    });
}

// Conditionally add "paiment"
if (user.value?.roles?.some((role: any) => role.name === 'admin' || role.name === 'service paiment')) {
    mainNavItems.push({
        title: 'Service Paiment',
        href: '/paiment',
        icon: Banknote,
    });
}








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
