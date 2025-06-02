<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

const props = defineProps<{
    quantite: {
        id_magasin: number,
        id_piece: number,
        qte_stocke: number,
        magasin?: {
            id_magasin: number,
            adresse_magasin: string,
        },
        piece?: {
            id_piece: number,
            nom_piece: string,
        },
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: route('magasin.index') },
    { title: 'Quantités Stockées', href: route('magasin.quantites.index') },
    { title: 'Modifier la quantité', href: route('magasin.quantites.edit', { id_magasin: props.quantite.id_magasin, id_piece: props.quantite.id_piece }) },
];

const form = useForm({
    qte_stocke: props.quantite.qte_stocke,
});

function submit() {
    form.put(route('magasin.quantites.update', {
        id_magasin: props.quantite.id_magasin,
        id_piece: props.quantite.id_piece
    }));
}
</script>

<template>
    <Head title="Modifier la quantité stockée" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier la quantité stockée
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magasin</label>
                    <input
                        :value="quantite.magasin?.adresse_magasin ?? '—'"
                        type="text"
                        disabled
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-gray-200 dark:bg-gray-900 text-gray-700 dark:text-gray-300 cursor-not-allowed"
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pièce</label>
                    <input
                        :value="quantite.piece?.nom_piece ?? '—'"
                        type="text"
                        disabled
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-gray-200 dark:bg-gray-900 text-gray-700 dark:text-gray-300 cursor-not-allowed"
                    />
                </div>

                <div class="space-y-2">
                    <label for="qte_stocke" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                    <input
                        v-model.number="form.qte_stocke"
                        type="number"
                        id="qte_stocke"
                        min="0"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
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
