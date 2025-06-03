<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: route('scentre.index') },
    { title: 'Gestion des Encaissements', href: '/encaissements' },
    { title: 'Créer un Encaissement', href: '/encaissements/create' },
]

const props = defineProps<{
    centres: Array<{
        id_centre: string,
        adresse_centre: string
    }>,
    remboursements: Array<{
        n_remb: string,
        n_dra: string,
        total_dra: number
    }>,
    userCentre: string
}>()

const form = useForm({
    id_centre: props.userCentre,
    n_remb: '',
    date_enc: ''
})

// Computed montant_enc from selected remboursement's total_dra
const montantEncaissement = computed(() => {
    const remb = props.remboursements.find(r => r.n_remb === form.n_remb)
    return remb ? remb.total_dra : 0
})

function submit() {
    form.post('/encaissements', {
        data: {
            ...form.data,
            montant_enc: montantEncaissement.value
        }
    })
}
</script>

<template>
    <Head title="Créer un Encaissement" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Créer un Encaissement
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label for="id_centre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Centre</label>
                    <select
                        v-model="form.id_centre"
                        id="id_centre"
                        disabled
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-white cursor-not-allowed"
                    >
                        <option v-for="centre in props.centres" :key="centre.id_centre" :value="centre.id_centre">
                            {{ centre.adresse_centre }}
                        </option>
                    </select>
                    <div v-if="form.errors.id_centre" class="text-red-500 text-sm">{{ form.errors.id_centre }}</div>
                </div>

                <div class="space-y-2">
                    <label for="n_remb" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remboursement</label>
                    <select
                        v-model="form.n_remb"
                        id="n_remb"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un remboursement --</option>
                        <option
                            v-for="remb in props.remboursements"
                            :key="remb.n_remb"
                            :value="remb.n_remb"
                        >
                            {{ remb.n_remb }} - {{ remb.n_dra }}
                        </option>
                    </select>

                    <div v-if="form.errors.n_remb" class="text-red-500 text-sm">{{ form.errors.n_remb }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant (calculé)</label>
                    <input
                        type="number"
                        :value="montantEncaissement"
                        readonly
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                    />
                </div>

                <div class="space-y-2">
                    <label for="date_enc" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input
                        v-model="form.date_enc"
                        type="date"
                        id="date_enc"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_enc" class="text-red-500 text-sm">{{ form.errors.date_enc }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        href="/encaissements"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#042B62] dark:bg-[#F3B21B] text-white dark:text-[#042B62] rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <span>Créer</span>
                        <span v-if="form.processing" class="animate-spin">↻</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
