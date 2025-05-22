<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    remboursement: {
        n_remb: number,
        date_remb: string,
        method_remb: string,
        n_dra: string
    },
    dras: Array<{
        n_dra: string
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Paiment', href: '/paiment' },
    { title: 'Gestion des Remboursements', href: '/paiment/remboursements' },
    { title: 'Modifier un Remboursement', href: `/paiment/remboursements/${props.remboursement.n_remb}/edit` },
]

const form = useForm({
    n_remb: props.remboursement.n_remb,
    date_remb: props.remboursement.date_remb,
    method_remb: props.remboursement.method_remb,
    n_dra: props.remboursement.n_dra,
})

function submit() {
    form.put(`/paiment/remboursements/${props.remboursement.n_remb}`)
}
</script>

<template>
    <Head title="Modifier un Remboursement" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier un Remboursement
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Remboursement</label>
                    <input
                        v-model="form.n_remb"
                        type="number"
                        disabled
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-gray-200 dark:bg-gray-900 text-gray-700 dark:text-gray-300 cursor-not-allowed"
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input
                        v-model="form.date_remb"
                        type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_remb" class="text-red-500 text-sm">{{ form.errors.date_remb }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Méthode</label>
                    <select
                        v-model="form.method_remb"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="espece">Espèce</option>
                        <option value="cheque">Chèque</option>
                    </select>
                    <div v-if="form.errors.method_remb" class="text-red-500 text-sm">{{ form.errors.method_remb }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">DRA</label>
                    <select
                        v-model="form.n_dra"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un DRA --</option>
                        <option v-for="dra in props.dras" :key="dra.n_dra" :value="dra.n_dra">
                            {{ dra.n_dra }}
                        </option>
                    </select>
                    <div v-if="form.errors.n_dra" class="text-red-500 text-sm">{{ form.errors.n_dra }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        href="/paiment/remboursements"
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
