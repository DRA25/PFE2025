<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Service Paiment',
        href: '/paiment',
    },
];
</script>

<template>
    <Head title="Achat Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-[#042B62FF] dark:text-[#BDBDBDFF]">
                Paiment Dashboard
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- DRA Management Card -->
                <Link
                    href="/paiment/remboursements"
                    class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow hover:shadow-lg transition"
                >
                    <h2 class="text-xl font-semibold mb-3 text-[#042B62FF] dark:text-[#F3B21B]">
                        Gestion des Rembouresements
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        Gérer les Demandes de Remboursements
                    </p>
                </Link>

                <Link
                    href="/paiment/dras"
                    class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow hover:shadow-lg transition"
                >
                    <h2 class="text-xl font-semibold mb-3 text-[#042B62FF] dark:text-[#F3B21B]">
                        Dras non Rembouresé
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        Gérer les Demandes de Remboursements
                    </p>
                </Link>


            </div>
        </div>
    </AppLayout>
</template>
