<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    dra: { n_dra: string },
    facture: {
        n_facture: string,
        montant_facture: number,
        date_facture: string,
        id_fourn: number,
    },
    fournisseurs: Array
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: route('achat.dras.index') },
    { title: `Factures de ${props.dra.n_dra}`, href: route('achat.dras.factures.index', { dra: props.dra.n_dra }) },
    { title: `Modifier Facture ${props.facture.n_facture}`, href: route('achat.dras.factures.edit', { dra: props.dra.n_dra, facture: props.facture.n_facture }) },
]

const form = useForm({
    n_facture: props.facture.n_facture,
    montant_facture: props.facture.montant_facture,
    date_facture: props.facture.date_facture,
    id_fourn: props.facture.id_fourn,
})

function submit() {
    form.put(route('achat.dras.factures.update', { dra: props.dra.n_dra, facture: props.facture.n_facture }), {
        onSuccess: () => {
            window.location.href = route('achat.dras.factures.index', { dra: props.dra.n_dra })
        },
        onError: () => {
            console.log('Validation errors:', form.errors)
        }
    })
}
</script>

<template>
    <Head title="Modifier Facture" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier la Facture {{ form.n_facture }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Facture</label>
                    <input
                        v-model="form.n_facture"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        disabled
                    />
                    <div v-if="form.errors.n_facture" class="text-red-500 text-sm">{{ form.errors.n_facture }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant Facture</label>
                    <input
                        v-model="form.montant_facture"
                        type="number"
                        step="0.01"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.montant_facture" class="text-red-500 text-sm">{{ form.errors.montant_facture }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Facture</label>
                    <input
                        v-model="form.date_facture"
                        type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_facture" class="text-red-500 text-sm">{{ form.errors.date_facture }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                    <select
                        v-model="form.id_fourn"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
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
                    <div v-if="form.errors.id_fourn" class="text-red-500 text-sm">{{ form.errors.id_fourn }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        :href="route('achat.dras.factures.index', { dra: props.dra.n_dra })"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-800 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <span v-if="!form.processing">Enregistrer les modifications</span>
                        <span v-else class="animate-spin">↻</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
