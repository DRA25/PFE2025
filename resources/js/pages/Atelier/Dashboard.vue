<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Wrench, ClipboardList, DollarSign, ListChecks, Handshake, TrendingUp } from 'lucide-vue-next'; // Ensure all potential icons are imported

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atelier',
        href: route('atelier.index'),
    },
];

// Map paths to appropriate icons for better semantic representation for Atelier
const getIconForPath = (path: string) => {
    if (path.includes('atelier.pieces')) return Wrench; // Icon for Gestion des Pièces
    if (path.includes('atelier.demandes-pieces')) return ClipboardList; // Icon for Mes Demandes de Pièces
    // Add other Atelier-specific icons if more links are added in the future
    return Wrench; // Default icon if no specific match
};
</script>

<template>
    <Head title="Atelier" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-gray-900 font-inter text-gray-800 dark:text-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden transition-all duration-500">
                <div class="p-6 sm:p-8 lg:p-10">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-10 text-[#042B62] dark:text-[#F3B21B] text-center tracking-tight leading-tight">
                        Atelier
                    </h1>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10">
                        <!-- Card: Gestion des Pièces -->
                        <Link
                            :href="route('atelier.pieces.index')"
                            class="group flex flex-col items-center text-center p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700
                                       transform hover:-translate-y-3 transition-all duration-400 ease-in-out
                                       bg-gradient-to-br from-white to-blue-50 dark:from-gray-700 dark:to-gray-800
                                       hover:shadow-2xl hover:border-blue-500 dark:hover:border-yellow-600
                                       hover:bg-gradient-to-br hover:from-blue-100 hover:to-blue-300 dark:hover:from-blue-950 dark:hover:to-blue-800"
                        >
                            <div class="p-4 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62]
                                            transition-all duration-300 transform group-hover:rotate-6 group-hover:scale-110 shadow-xl
                                            group-hover:ring-4 group-hover:ring-blue-300 group-hover:ring-opacity-50 dark:group-hover:ring-yellow-600 dark:group-hover:ring-opacity-50">
                                <component :is="getIconForPath('atelier.pieces')" class="w-8 h-8 sm:w-9 sm:h-9" />
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold mb-2 text-[#042B62] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300
                                           transition-colors duration-300 leading-snug">
                                Gestion des Pièces
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base leading-relaxed">
                                Gérez et suivez l'inventaire des pièces détachées de l'atelier.
                            </p>
                        </Link>

                        <!-- Card: Mes Demandes de Pièces -->
                        <Link
                            :href="route('atelier.demandes-pieces.index')"
                            class="group flex flex-col items-center text-center p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700
                                       transform hover:-translate-y-3 transition-all duration-400 ease-in-out
                                       bg-gradient-to-br from-white to-blue-50 dark:from-gray-700 dark:to-gray-800
                                       hover:shadow-2xl hover:border-blue-500 dark:hover:border-yellow-600
                                       hover:bg-gradient-to-br hover:from-blue-100 hover:to-blue-300 dark:hover:from-blue-950 dark:hover:to-blue-800"
                        >
                            <div class="p-4 bg-[#042B62] text-white rounded-full mb-4 group-hover:bg-[#F3B21B] group-hover:text-[#042B62]
                                            transition-all duration-300 transform group-hover:rotate-6 group-hover:scale-110 shadow-xl
                                            group-hover:ring-4 group-hover:ring-blue-300 group-hover:ring-opacity-50 dark:group-hover:ring-yellow-600 dark:group-hover:ring-opacity-50">
                                <component :is="getIconForPath('atelier.demandes-pieces')" class="w-8 h-8 sm:w-9 sm:h-9" />
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold mb-2 text-[#042B62] dark:text-[#F3B21B] group-hover:text-blue-700 dark:group-hover:text-yellow-300
                                           transition-colors duration-300 leading-snug">
                                Mes Demandes de Pièces
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base leading-relaxed">
                                Créez et suivez vos demandes de pièces auprès du magasin.
                            </p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
