<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    demande_piece: {  // Changed from 'demande' to 'demande_piece'
        id_dp: number;
        date_dp: string;
        etat_dp: string;
        id_piece: number;
        qte_demandep: number;
        id_magasin: number;
        id_atelier: number;
    };
    pieces: {
        id_piece: number;
        nom_piece: string;
    }[];
    magasins: {
        id_magasin: number;
        adresse_magasin: string;
    }[];
    ateliers: {
        id_atelier: number;
        adresse_atelier: string;
    }[];
}>();

const form = useForm({
    date_dp: props.demande_piece.date_dp,
    etat_dp: props.demande_piece.etat_dp,
    id_piece: props.demande_piece.id_piece,
    qte_demandep: props.demande_piece.qte_demandep,
    id_magasin: props.demande_piece.id_magasin,
    id_atelier: props.demande_piece.id_atelier,
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
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier la Demande de Pièces #{{ demande_piece.id_dp }}
                </h1>
            </div>

            <form @submit.prevent="form.put(route('atelier.demandes-pieces.update', demande_piece.id_dp))" class="m-5">
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
                            @change="form.id_magasin = ''"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Sélectionner une piece</option>
                            <option v-for="piece in pieces" :key="piece.id_piece" :value="piece.id_piece">
                                {{ piece.nom_piece }}
                            </option>
                        </select>
                        <p v-if="form.errors.id_piece" class="text-sm text-red-600">{{ form.errors.id_piece }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="id_magasin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magasin</label>
                        <select
                            id="id_magasin"
                            v-model="form.id_magasin"
                            @change="form.id_atelier = ''"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Sélectionner un magasin</option>
                            <option v-for="magasin in magasins" :key="magasin.id_magasin" :value="magasin.id_magasin">
                                {{ magasin.adresse_magasin }}
                            </option>
                        </select>
                        <p v-if="form.errors.id_magasin" class="text-sm text-red-600">{{ form.errors.id_magasin }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="id_atelier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Atelier</label>
                        <select
                            id="id_atelier"
                            v-model="form.id_atelier"
                            @change="form.id_magasin = ''"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Sélectionner un atelier</option>
                            <option v-for="atelier in ateliers" :key="atelier.id_atelier" :value="atelier.id_atelier">
                                {{ atelier.adresse_atelier }}
                            </option>
                        </select>
                        <p v-if="form.errors.id_atelier" class="text-sm text-red-600">{{ form.errors.id_atelier }}</p>
                    </div>

                    <div v-if="form.errors.id_magasin && form.errors.id_atelier" class="text-sm text-red-600">
                        Vous devez sélectionner soit un magasin soit un atelier
                    </div>
                </div>

                <div class="flex justify-end mt-6 space-x-4">
                    <Link
                        :href="route('atelier.demandes-pieces.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-600 dark:text-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#042B62]"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] border border-transparent rounded-md shadow-sm hover:bg-blue-700 dark:hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#042B62]"
                    >
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
