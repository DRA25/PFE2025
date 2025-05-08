<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps({
    dra: Object,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: `Créer une Facture pour DRA ${props.dra.n_dra}`, href: `/dras/${props.dra.n_dra}/factures/create` },
]

const form = useForm({
    n_facture: '',
    montant_facture: '',
    date_facture: '',
    id_fourn: '',
})

function submit() {
    form.post(route('dras.factures.store', { dra: props.dra.n_dra }), {
        onSuccess: () => {
            form.reset()
            // Redirect back to facture index of that dra
            window.location.href = `/dras/${props.dra.n_dra}/factures`
        },
        onError: () => {
            console.log('Validation error:', form.errors)
        }
    })
}
</script>

<template>
    <Head title="Créer une Facture pour DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-5">
            <h1 class="text-lg font-bold mb-5">Créer une Facture pour DRA {{ props.dra.n_dra }}</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>N° Facture</label>
                    <input
                        v-model="form.n_facture"
                        type="number"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.n_facture" class="text-red-500">
                        {{ form.errors.n_facture }}
                    </div>
                </div>

                <div>
                    <label>Montant Facture</label>
                    <input
                        v-model="form.montant_facture"
                        type="number"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.montant_facture" class="text-red-500">
                        {{ form.errors.montant_facture }}
                    </div>
                </div>

                <div>
                    <label>Date Facture</label>
                    <input
                        v-model="form.date_facture"
                        type="date"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.date_facture" class="text-red-500">
                        {{ form.errors.date_facture }}
                    </div>
                </div>

                <div>
                    <label>Fournisseur ID</label>
                    <input
                        v-model="form.id_fourn"
                        type="number"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.id_fourn" class="text-red-500">
                        {{ form.errors.id_fourn }}
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800"
                        :disabled="form.processing"
                    >
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
