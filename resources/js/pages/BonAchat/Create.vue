<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps({
    dra: Object,
    fournisseurs: Array,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/achat/dras' },
    { title: `Créer un Bon d'achat pour DRA ${props.dra.n_dra}`, href: `/achat/dras/${props.dra.n_dra}/bon-achats/create` },
]

const form = useForm({
    n_ba: '',
    montant_ba: '',
    date_ba: '',
    id_fourn: '',
})

function submit() {
    form.post(route('achat.dras.bon-achats.store', { dra: props.dra.n_dra }), {
        onSuccess: () => {
            form.reset()
            window.location.href = `/achat/dras/${props.dra.n_dra}/bon-achats`
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
                    <label for="n_ba" class="block text-gray-700 text-sm font-bold mb-2">N° Bon Achat</label>
                    <input
                        id="n_ba"
                        v-model="form.n_ba"
                        type="number"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
                    <div v-if="form.errors.n_ba" class="text-red-500 text-xs italic">
                        {{ form.errors.n_ba }}
                    </div>
                </div>

                <div>
                    <label for="montant_ba" class="block text-gray-700 text-sm font-bold mb-2">Montant Bon Achat</label>
                    <input
                        id="montant_ba"
                        v-model="form.montant_ba"
                        type="number"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
                    <div v-if="form.errors.montant_ba" class="text-red-500 text-xs italic">
                        {{ form.errors.montant_ba }}
                    </div>
                </div>

                <div>
                    <label for="date_ba" class="block text-gray-700 text-sm font-bold mb-2">Date Bon Achat</label>
                    <input
                        id="date_ba"
                        v-model="form.date_ba"
                        type="date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
                    <div v-if="form.errors.date_ba" class="text-red-500 text-xs italic">
                        {{ form.errors.date_ba }}
                    </div>
                </div>

                <div>
                    <label for="id_fourn" class="block text-gray-700 text-sm font-bold mb-2">Fournisseur</label>
                    <select
                        id="id_fourn"
                        v-model="form.id_fourn"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                        <option value="">-- Sélectionnez un fournisseur --</option>
                        <option
                            v-for="fournisseur in props.fournisseurs"
                            :key="fournisseur.id_fourn"
                            :value="fournisseur.id_fourn"
                        >
                            {{ fournisseur.nom_fourn }}
                        </option>
                    </select>
                    <div v-if="form.errors.id_fourn" class="text-red-500 text-xs italic">
                        {{ form.errors.id_fourn }}
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        :disabled="form.processing"
                    >
                        Enregistrer
                    </button>
                    <Link
                        :href="`/achat/dras/${props.dra.n_dra}/bon-achats`"
                        class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
                    >
                        Annuler
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
