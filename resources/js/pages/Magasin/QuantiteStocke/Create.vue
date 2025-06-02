<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: route('magasin.index') },
    { title: 'Quantités Stockées', href: route('magasin.quantites.index') },
    { title: 'Ajouter une quantité', href: route('magasin.quantites.create') },
];

const props = defineProps<{
    defaultMagasin: { id_magasin: number, adresse_magasin: string },
    pieces: Array<{ id_piece: number, nom_piece: string }>,
}>();

const form = useForm({
    id_magasin: props.defaultMagasin.id_magasin,
    id_piece: '',
    qte_stocke: 0,
});

function submit() {
    form.post(route('magasin.quantites.store'));
}
</script>

<template>
    <Head title="Ajouter une quantité stockée" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Ajouter une quantité stockée
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magasin</label>
                    <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-600">
                        {{ props.defaultMagasin.adresse_magasin }}
                    </div>
                    <input type="hidden" v-model="form.id_magasin" />
                </div>

                <div class="space-y-2">
                    <label for="id_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pièce disponible</label>
                    <select
                        id="id_piece"
                        v-model="form.id_piece"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B]"
                        required
                    >
                        <option value="">-- Sélectionner une pièce disponible --</option>
                        <option
                            v-for="piece in props.pieces"
                            :key="piece.id_piece"
                            :value="piece.id_piece"
                        >
                            {{ piece.nom_piece }}
                        </option>
                    </select>
                    <div v-if="form.errors.id_piece" class="text-red-500 text-sm">{{ form.errors.id_piece }}</div>
                    <p v-if="props.pieces.length === 0" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Toutes les pièces ont déjà été assignées à ce magasin.
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="qte_stocke" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                    <input
                        type="number"
                        id="qte_stocke"
                        v-model.number="form.qte_stocke"
                        min="0"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        required
                    />
                    <div v-if="form.errors.qte_stocke" class="text-red-500 text-sm">{{ form.errors.qte_stocke }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        :href="route('magasin.quantites.index')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || props.pieces.length === 0"
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
