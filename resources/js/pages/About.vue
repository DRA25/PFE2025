<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'About',
        href: '/about',
    },

];
</script>

<template>
    <Head title="About" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl underline">Overview</h1>
            <p>
    Naftal is a major state-owned Algerian company that operates in the oil and gas distribution sector,
    primarily focusing on fuel, lubricants, and gas products.
    It plays a crucial role in the domestic energy supply chain of Algeria.
</p>
    <h1 class="text-2xl underline">What Naftal Does</h1>
            <p>
    Fuel Distribution

    Supplies gasoline, diesel, and other petroleum-based fuels across Algeria.

    Operates a large network of service stations nationwide.

    LPG (Butane & Propane)

    Distributes liquefied petroleum gas for cooking and heating, especially in rural areas.

    Lubricants

    Produces and distributes motor oils and industrial lubricants under its own brand.

    CNG & NGV Development

    Working on expanding compressed natural gas (CNG) infrastructure for vehicles.
</p>

            <img src="/images/station.jpg" class="w-1/2 border border-2 border-blue-950 rounded-lg">


        </div>
    </AppLayout>
</template>
