<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const breadcrumbs = [
    { title: 'À propos', href: '/about' },
    { title: 'Erreur 403', href: '' },
];
</script>

<template>
    <Head title="Accès non autorisé" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col items-center justify-center text-center py-20 space-y-6">
            <h1 class="text-6xl font-extrabold text-red-600">403</h1>
            <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                Accès non autorisé
            </p>
            <p class="text-gray-600 dark:text-gray-300 max-w-md">
                Vous n'avez pas les permissions nécessaires pour accéder à cette page.
                Veuillez contacter l’administrateur si vous pensez qu’il s’agit d’une erreur.
            </p>
            <a
                href="/about"
                class="inline-block bg-[#042B62] text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition dark:bg-[#F3B21B] dark:text-[#042B62] dark:hover:bg-yellow-300"
            >
                Retour à la page à propos
            </a>
        </div>
    </AppLayout>
</template>
