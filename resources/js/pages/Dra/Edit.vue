<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    dra: {
        n_dra: string
        date_creation: string
        seuil_dra: number
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: `Modifier DRA ${props.dra.n_dra}`, href: `/dras/${props.dra.n_dra}/edit` },
]

const form = useForm({
    date_creation: props.dra.date_creation,
    seuil_dra: props.dra.seuil_dra,
})

function submit() {
    form.transform(data => ({
        ...data,
        _method: 'put',
    })).post(`/dras/${props.dra.n_dra}`)
}
</script>

<template>
    <Head title="Modifier DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier le DRA {{ dra.n_dra }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de création</label>
                    <input
                        v-model="form.date_creation"
                        type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_creation" class="text-red-500 text-sm">{{ form.errors.date_creation }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seuil DRA</label>
                    <input
                        v-model="form.seuil_dra"
                        type="number"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.seuil_dra" class="text-red-500 text-sm">{{ form.errors.seuil_dra }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        :href="`/dras`"
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
            </form>
        </div>
    </AppLayout>
</template>
