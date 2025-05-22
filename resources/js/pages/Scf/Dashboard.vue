<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { FolderSearch } from 'lucide-vue-next'; // Import relevant icons

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Service Coordination Financière',
        href: '/scf',
    },
];
</script>

<template>
    <Head title="Coordination Financière Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 p-8 bg-gray-100 dark:bg-gray-900 rounded-xl shadow-lg">
            <h1 class="text-3xl font-extrabold mb-8 text-[#042B62FF] dark:text-[#F3B21B] text-center">
                Tableau de Bord de la Coordination Financière
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link
                    href="/scf/dras"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <FolderSearch class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Consulter les DRAs
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Visualisez et gérez toutes les Demandes de Réquisition d'Achat.
                    </p>
                </Link>

            </div>
        </div>
    </AppLayout>
</template>
