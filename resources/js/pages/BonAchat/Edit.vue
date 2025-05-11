<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    dra: { n_dra: string },
    bonAchat: {
        n_ba: string,  // ID is a string to match behavior
        montant_ba: number,
        date_ba: string,
        id_fourn: number
    },
    fournisseurs: Array // Add suppliers list
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: `Bons d'achat de ${props.dra.n_dra}`, href: `/dras/${props.dra.n_dra}/bon-achats` },
    { title: `Modifier Bon d'achat ${props.bonAchat.n_ba}`, href: `/dras/${props.dra.n_dra}/bon-achats/${props.bonAchat.n_ba}/edit` },
]

const form = useForm({
    n_ba: props.bonAchat.n_ba,
    montant_ba: props.bonAchat.montant_ba,
    date_ba: props.bonAchat.date_ba,
    id_fourn: props.bonAchat.id_fourn,
})

function submit() {
    form.put(`/dras/${props.dra.n_dra}/bon-achats/${props.bonAchat.n_ba}`, {
        onSuccess: () => {
            window.location.href = `/dras/${props.dra.n_dra}/bon-achats`
        },
        onError: () => {
            console.log('Validation error:', form.errors)
        }
    })
}
</script>

<template>
    <Head title="Modifier Bon d'achat" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-5">
            <h1 class="text-lg font-bold mb-5">Modifier le Bon d'achat {{ form.n_ba }}</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <div>
                    <label>Numéro Bon Achat</label>
                    <input
                        v-model="form.n_ba"
                        type="text"
                        class="w-full border p-2 rounded"
                        disabled
                    />
                    <div v-if="form.errors.n_ba" class="text-red-500">{{ form.errors.n_ba }}</div>
                </div>

                <div>
                    <label>Montant Bon Achat</label>
                    <input
                        v-model="form.montant_ba"
                        type="number"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.montant_ba" class="text-red-500">{{ form.errors.montant_ba }}</div>
                </div>

                <div>
                    <label>Date Bon Achat</label>
                    <input
                        v-model="form.date_ba"
                        type="date"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.date_ba" class="text-red-500">{{ form.errors.date_ba }}</div>
                </div>

                <!-- Fournisseur Select -->
                <div>
                    <label>Fournisseur</label>
                    <select
                        v-model="form.id_fourn"
                        class="w-full border p-2 rounded"
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
                    <div v-if="form.errors.id_fourn" class="text-red-500">{{ form.errors.id_fourn }}</div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-800"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Enregistrement...</span>
                        <span v-else>Enregistrer les modifications</span>
                    </button>
                </div>

            </form>
        </div>
    </AppLayout>
</template>
