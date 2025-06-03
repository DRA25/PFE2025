<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ClipboardCheck, PackageSearch } from 'lucide-vue-next'; // Import relevant icons

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Achat',
        href: '/achat',
    },
];
</script>

<template>
    <Head title="Achat Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 p-8 bg-gray-100 dark:bg-gray-900 rounded-xl shadow-lg">
            <h1 class="text-3xl font-extrabold mb-8 text-[#042B62FF] dark:text-[#F3B21B] text-center">
                Tableau de Bord des Achats
            </h1>



        </div>
    </AppLayout>
</template>
