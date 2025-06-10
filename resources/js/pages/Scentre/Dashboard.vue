<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ClipboardCheck, PackageSearch } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Centre',
        href: '/scentre',
    },
];
</script>

<template>
    <Head title="Scentre Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 p-8 bg-gray-100 dark:bg-gray-900 rounded-xl shadow-lg">
            <h1 class="text-3xl font-extrabold mb-8 text-[#042B62FF] dark:text-[#F3B21B] text-center">
                Tableau de Bord du Centre
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link
                    href="/scentre/dras"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <ClipboardCheck class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Gestion des DRAs
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Gérez les Demandes de Réquisition.
                    </p>
                </Link>

                <Link
                    :href="route('scentre.demandes-pieces.index')"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <PackageSearch class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Demandes de Pièces (Magasins)
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Traitez les demandes de pièces provenant des magasins.
                    </p>
                </Link>

                <Link
                    href="/encaissements"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <PackageSearch class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Ajouter des Encaissements
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Traitez les demandes de pièces provenant des magasins.
                    </p>
                </Link>

                <Link
                    href="/scentre/boncommandes"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <ClipboardCheck class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Rediger les Bons de Commandes
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Traitez les Bons de Commandes.
                    </p>
                </Link>

                <Link
                    href="/scentre/prestations"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <ClipboardCheck class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Gestion des Prestations
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Traitez les Prestations.
                    </p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
