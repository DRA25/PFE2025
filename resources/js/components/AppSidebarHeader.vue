<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import MenuForeachRole from '@/components/MenuForeachRole.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import NotificationIcon from '@/components/NotificationIcon.vue';

const page = usePage();
const userHasRole = computed(() => page.props.userHasRole as string);

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
    userHasRole?: string;
}>();


</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />

            <template v-if="breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <MenuForeachRole class="ml-10"/>
        </div>

        <div class="absolute right-0 flex items-center">
            <NotificationIcon class="mr-5" />
            <AppearanceTabs class="ml-5 mr-5" />
        </div>

    </header>

</template>
