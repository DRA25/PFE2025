<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    dra: { n_dra: string },
    bonAchat: {
        n_ba: string,
        montant_ba: number,
        date_ba: string,
        id_fourn: number
    },
    fournisseurs: Array
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: route('achat.dras.index') },
    { title: `Bons d'achat de ${props.dra.n_dra}`, href: route('achat.dras.bon-achats.index', { dra: props.dra.n_dra }) },
    { title: `Modifier Bon d'achat ${props.bonAchat.n_ba}`, href: route('achat.dras.bon-achats.edit', { dra: props.dra.n_dra, bonAchat: props.bonAchat.n_ba }) },
]

const form = useForm({
    n_ba: props.bonAchat.n_ba,
    montant_ba: props.bonAchat.montant_ba,
    date_ba: props.bonAchat.date_ba,
    id_fourn: props.bonAchat.id_fourn,
})

function submit() {
    form.put(route('achat.dras.bon-achats.update', {
        dra: props.dra.n_dra,
        bonAchat: props.bonAchat.n_ba
    }), {
        onSuccess: () => {
            window.location.href = route('achat.dras.bon-achats.index', { dra: props.dra.n_dra })
        },
        onError: () => {
            console.log('Validation error:', form.errors)
        }
    })
}

function destroyBonAchat() {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce bon d'achat ?")) {
        form.delete(route('achat.dras.bon-achats.destroy', {
            dra: props.dra.n_dra,
            bonAchat: props.bonAchat.n_ba
        }), {
            onSuccess: () => {
                window.location.href = route('achat.dras.bon-achats.index', { dra: props.dra.n_dra });
            },
            onError: () => {
                console.log("Erreur lors de la suppression.");
            }
        });
    }
}
</script>

<template>
    <Head :title="`Modifier Bon d'achat ${form.n_ba}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier le Bon d'achat {{ form.n_ba }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Bon d'achat</label>
                    <input
                        v-model="form.n_ba"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        disabled
                    />
                    <div v-if="form.errors.n_ba" class="text-red-500 text-sm">{{ form.errors.n_ba }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant</label>
                    <input
                        v-model="form.montant_ba"
                        type="number"
                        step="0.01"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.montant_ba" class="text-red-500 text-sm">{{ form.errors.montant_ba }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input
                        v-model="form.date_ba"
                        type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_ba" class="text-red-500 text-sm">{{ form.errors.date_ba }}</div>
                </div>

                <label class=" text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                <div class="mt-1 flex gap-3">
                    <select
                        v-model="form.id_fourn"
                        class="w-1/3 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un fournisseur --</option>
                        <option
                            v-for="fournisseur in fournisseurs"
                            :key="fournisseur.id_fourn"
                            :value="fournisseur.id_fourn"
                        >
                            {{ fournisseur.nom_fourn }}
                        </option>
                    </select>
                    <Link
                        href="/fournisseurs/create"
                        as="button"
                        class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
                    >
                        <Plus class="w-4 h-4" />
                    </Link>
                    <div v-if="form.errors.id_fourn" class="text-red-500 text-sm">{{ form.errors.id_fourn }}</div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="destroyBonAchat"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>

                    <div class="flex gap-4">
                    <Link
                        :href="route('achat.dras.bon-achats.index', { dra: props.dra.n_dra })"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#042B62] dark:bg-[#F3B21B] text-white dark:text-[#042B62] rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <span>Enregistrer les modifications</span>
                        <span v-if="form.processing" class="animate-spin">↻</span>
                    </button>
                    </div>
                </div>

            </form>
        </div>
    </AppLayout>
</template>
