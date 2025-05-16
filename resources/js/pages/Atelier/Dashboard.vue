<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atelier',
        href: route('atelier.index'),
    },
];
</script>

<template>
    <Head title="Atelier Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-[#042B62FF] dark:text-[#BDBDBDFF]">
                Atelier Dashboard
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Piece Management Card -->
                <Link
                    :href="route('atelier.pieces.index')"
                    class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow hover:shadow-lg transition"
                >
                    <h2 class="text-xl font-semibold mb-3 text-[#042B62FF] dark:text-[#F3B21B]">
                        Gestion des Pièces
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        Gérer l'inventaire des pièces détachées
                    </p>
                </Link>

                <!-- Demandes de Pièces Card -->
                <Link
                    :href="route('atelier.demandes-pieces.index')"
                    class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow hover:shadow-lg transition"
                >
                    <h2 class="text-xl font-semibold mb-3 text-[#042B62FF] dark:text-[#F3B21B]">
                        Demandes de Pièces
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        Gérer les demandes de pièces pour les ateliers
                    </p>
                </Link>

                <!-- Add more dashboard cards as needed -->
            </div>
        </div>
    </AppLayout>
</template>
