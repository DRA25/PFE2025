<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    demande_piece: {
        id_dp: number;
        date_dp: string;
        // etat_dp: string; // Removed as per request
        id_piece: number;
        qte_demandep: number;
        // id_magasin: number; // Removed as per request
        // id_atelier: number; // Removed as per request
    };
    pieces: {
        id_piece: number;
        nom_piece: string;
    }[];
    // etatOptions: string[]; // Removed as etat_dp is no longer a user-selectable field in edit
    // magasins: {  // Removed as per request
    //     id_magasin: number;
    //     adresse_magasin: string;
    // }[];
    // ateliers: {  // Removed as per request
    //     id_atelier: number;
    //     adresse_atelier: string;
    // }[];
}>();

const form = useForm({
    date_dp: props.demande_piece.date_dp,
    // etat_dp: props.demande_piece.etat_dp, // Removed as per request
    id_piece: props.demande_piece.id_piece,
    qte_demandep: props.demande_piece.qte_demandep,
    // id_magasin: props.demande_piece.id_magasin, // Removed as per request
    // id_atelier: props.demande_piece.id_atelier, // Removed as per request
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: route('atelier.index') },
    { title: 'Demandes de Pièces', href: route('atelier.demandes-pieces.index') },
    { title: 'Modifier', href: route('atelier.demandes-pieces.edit', props.demande_piece.id_dp) }
];
</script>

<template>
    <Head title="Modifier une Demande de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier la Demande de Pièces #{{ demande_piece.id_dp }}
                </h1>
            </div>

            <form @submit.prevent="form.put(route('atelier.demandes-pieces.update', demande_piece.id_dp))" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <!-- Error display section (from create page design) -->
                <div v-if="Object.keys(form.errors).length" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <label for="date_dp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input
                        id="date_dp"
                        v-model="form.date_dp"
                        type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_dp" class="text-red-500 text-sm">{{ form.errors.date_dp }}</div>
                </div>

                <!-- Removed the select dropdown for etat_dp -->

                <div class="space-y-2">
                    <label for="qte_demandep" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                    <input
                        id="qte_demandep"
                        v-model="form.qte_demandep"
                        type="number"
                        min="1"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.qte_demandep" class="text-red-500 text-sm">{{ form.errors.qte_demandep }}</div>
                </div>

                <div class="space-y-2">
                    <label for="id_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piece</label>
                    <select
                        id="id_piece"
                        v-model="form.id_piece"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Sélectionner une piece</option>
                        <option v-for="piece in pieces" :key="piece.id_piece" :value="piece.id_piece">
                            {{ piece.nom_piece }}
                        </option>
                    </select>
                    <div v-if="form.errors.id_piece" class="text-red-500 text-sm">{{ form.errors.id_piece }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        :href="route('atelier.demandes-pieces.index')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#042B62] dark:bg-[#F3B21B] text-white dark:text-[#042B62] rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <span>Enregistrer</span>
                        <span v-if="form.processing" class="animate-spin">↻</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
