<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps({
    dra: Object,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: `Créer un Bon d'achat pour DRA ${props.dra.n_dra}`, href: `/dras/${props.dra.n_dra}/bon-achats/create` },
]

const form = useForm({
    n_ba: '',
    montant_ba: '',
    date_ba: '',
    id_fourn: '',
})

function submit() {
    form.post(route('dras.bon-achats.store', { dra: props.dra.n_dra }), {
        onSuccess: () => {
            form.reset()
            // Redirect back to bon-achat index of that dra
            window.location.href = `/dras/${props.dra.n_dra}/bon-achats`
        },
        onError: () => {
            console.log('Validation error:', form.errors)
        }
    })
}
</script>

<template>
    <Head title="Créer un Bon d'achat pour DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-5">
            <h1 class="text-lg font-bold mb-5">Créer un Bon d'achat pour DRA {{ props.dra.n_dra }}</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>N° Bon Achat</label>
                    <input
                        v-model="form.n_ba"
                        type="number"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.n_ba" class="text-red-500">
                        {{ form.errors.n_ba }}
                    </div>
                </div>

                <div>
                    <label>Montant Bon Achat</label>
                    <input
                        v-model="form.montant_ba"
                        type="number"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.montant_ba" class="text-red-500">
                        {{ form.errors.montant_ba }}
                    </div>
                </div>

                <div>
                    <label>Date Bon Achat</label>
                    <input
                        v-model="form.date_ba"
                        type="date"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="form.errors.date_ba" class="text-red-500">
                        {{ form.errors.date_ba }}
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
