<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Package, ClipboardList, PackageCheck, Warehouse } from 'lucide-vue-next'; // Import icons

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Magasin',
        href: route('magasin.index'),
    },
];
</script>

<template>
    <Head title="Magasin Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 p-8 bg-gray-100 dark:bg-gray-900 rounded-xl shadow-lg">
            <h1 class="text-3xl font-extrabold mb-8 text-[#042B62FF] dark:text-[#F3B21B] text-center">
                Tableau de Bord du Magasin
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link
                    :href="route('magasin.pieces.index')"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <Package class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Gestion des Pièces
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Gérez et suivez l'inventaire complet de toutes les pièces détachées.
                    </p>
                </Link>

                <Link
                    :href="route('magasin.demandes-pieces.index')"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <ClipboardList class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Mes Demandes de Pièces
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Gérez les demandes de pièces soumises par d'autres magasins.
                    </p>
                </Link>

                <Link
                    :href="route('magasin.mes-demandes.index')"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <PackageCheck class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Demandes de Pièces (Atelier)
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Traitez les demandes de pièces provenant des différents ateliers.
                    </p>
                </Link>

                <Link
                    :href="route('magasin.quantites.index')"
                    class="group bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center"
                >
                    <div class="p-3 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62] transition-colors duration-300">
                        <Warehouse class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-[#042B62FF] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300 transition-colors duration-300">
                        Quantités Stockées
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Consultez et gérez les quantités de pièces disponibles dans votre magasin.
                    </p>
                </Link>

            </div>
        </div>
    </AppLayout>
</template>
