<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    dra: { n_dra: string },
    facture: {
        n_facture: string,  // Changed from number to string
        montant_facture: number,
        date_facture: string,
        id_fourn: number
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: `Factures de ${props.dra.n_dra}`, href: `/dras/${props.dra.n_dra}/factures` },
    { title: `Modifier Facture ${props.facture.n_facture}`, href: `/dras/${props.dra.n_dra}/factures/${props.facture.n_facture}/edit` },
]

const form = useForm({
    n_facture: props.facture.n_facture,  // Added this field
    montant_facture: props.facture.montant_facture,
    date_facture: props.facture.date_facture,
    id_fourn: props.facture.id_fourn,
})

function submit() {
    form.put(`/dras/${props.dra.n_dra}/factures/${props.facture.n_facture}`)
}
</script>

<template>
    <Head title="Modifier Facture" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-5">
            <h1 class="text-lg font-bold mb-5">Modifier la Facture {{ form.n_facture }}</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Numéro Facture</label>
                    <input v-model="form.n_facture" type="text" class="w-full border p-2 rounded" />
                    <div v-if="form.errors.n_facture" class="text-red-500">{{ form.errors.n_facture }}</div>
                </div>

                <div>
                    <label>Montant Facture</label>
                    <input v-model="form.montant_facture" type="number" class="w-full border p-2 rounded" />
                    <div v-if="form.errors.montant_facture" class="text-red-500">{{ form.errors.montant_facture }}</div>
                </div>

                <div>
                    <label>Date Facture</label>
                    <input v-model="form.date_facture" type="date" class="w-full border p-2 rounded" />
                    <div v-if="form.errors.date_facture" class="text-red-500">{{ form.errors.date_facture }}</div>
                </div>

                <div>
                    <label>Fournisseur ID</label>
                    <input v-model="form.id_fourn" type="number" class="w-full border p-2 rounded" />
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
