<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const form = useForm({
    date_dp: new Date().toISOString().split('T')[0],
    etat_dp: 'En attente',
    id_piece: '',
    qte_demandep: 1,
    // id_magasin: null, // Removed
    // id_atelier: null, // Removed
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: '/magasin' },
    { title: 'Demandes de Pièces', href: route('magasin.demandes-pieces.index') },
    { title: 'Créer', href: route('magasin.demandes-pieces.create') }
];

defineProps<{
    pieces: {
        id_piece: number;
        nom_piece: string;
    }[];
}>();
</script>

<template>
    <Head title="Créer une Demande de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Créer une Demande de Pièces
                </h1>
            </div>

            <form @submit.prevent="form.post(route('magasin.demandes-pieces.store'))" class="m-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="date_dp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input
                            id="date_dp"
                            v-model="form.date_dp"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        />
                        <p v-if="form.errors.date_dp" class="text-sm text-red-600">{{ form.errors.date_dp }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="etat_dp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">État</label>
                        <select
                            id="etat_dp"
                            v-model="form.etat_dp"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        >
                            <option value="En attente">En attente</option>
                            <option value="Validée">Validée</option>
                            <option value="Refusée">Refusée</option>
                            <option value="Livrée">Livrée</option>
                        </select>
                        <p v-if="form.errors.etat_dp" class="text-sm text-red-600">{{ form.errors.etat_dp }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="qte_demandep" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                        <input
                            id="qte_demandep"
                            v-model="form.qte_demandep"
                            type="number"
                            min="1"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        />
                        <p v-if="form.errors.qte_demandep" class="text-sm text-red-600">{{ form.errors.qte_demandep }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="id_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piece</label>
                        <select
                            id="id_piece"
                            v-model="form.id_piece"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Sélectionner une piece</option>
                            <option v-for="piece in pieces" :key="piece.id_piece" :value="piece.id_piece">
                                {{ piece.nom_piece }}
                            </option>
                        </select>
                        <p v-if="form.errors.id_piece" class="text-sm text-red-600">{{ form.errors.id_piece }}</p>
                    </div>
                </div>

                <div class="flex justify-end mt-6 space-x-4">
                    <Link
                        :href="route('magasin.demandes-pieces.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-600 dark:text-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#042B62]"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] border border-transparent rounded-md shadow-sm hover:bg-blue-700 dark:hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#042B62]"
                    >
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
