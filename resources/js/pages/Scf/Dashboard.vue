<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { FolderSearch } from 'lucide-vue-next';

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
        <div class="min-h-screen bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-gray-900 font-inter text-gray-800 dark:text-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden transition-all duration-500">
                <div class="p-6 sm:p-8 lg:p-10">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-10 text-[#042B62] dark:text-[#F3B21B] text-center tracking-tight leading-tight">
                        Service Coordination Financière
                    </h1>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10">
                        <Link
                            href="/scf/dras"
                            class="group flex flex-col items-center text-center p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700
                                       transform hover:-translate-y-3 transition-all duration-400 ease-in-out
                                       bg-gradient-to-br from-white to-blue-50 dark:from-gray-700 dark:to-gray-800
                                       hover:shadow-2xl hover:border-blue-500 dark:hover:border-yellow-600
                                       hover:bg-gradient-to-br hover:from-blue-100 hover:to-blue-300 dark:hover:from-blue-950 dark:hover:to-blue-800"
                        >
                            <div class="p-4 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62]
                                            transition-all duration-300 transform group-hover:rotate-6 group-hover:scale-110 shadow-xl
                                            group-hover:ring-4 group-hover:ring-blue-300 group-hover:ring-opacity-50 dark:group-hover:ring-yellow-600 dark:group-hover:ring-opacity-50">
                                <FolderSearch class="w-8 h-8 sm:w-9 sm:h-9" />
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold mb-2 text-[#042B62] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300
                                           transition-colors duration-300 leading-snug">
                                Consulter les DRAs
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base leading-relaxed">
                                Visualisez et gérez toutes les Demandes de Réquisition d'Achat.
                            </p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
