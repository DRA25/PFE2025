<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    centre: {
        id_centre: string,
        adresse_centre: string,
        seuil_centre: number,
        type_centre: string,
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Espace admin', href: '/espace-admin' },
    { title: 'Gestion des Centres', href: '/centres' },
    { title: `Modifier Centre ${props.centre.id_centre}`, href: `/centres/${props.centre.id_centre}/edit` },
]

const form = useForm({
    id_centre: props.centre.id_centre,
    adresse_centre: props.centre.adresse_centre,
    seuil_centre: props.centre.seuil_centre,
    type_centre: props.centre.type_centre,
})

function submit() {
    form.transform(data => ({
        ...data,
        _method: 'put',
    })).post(`/centres/${props.centre.id_centre}`)
}
</script>

<template>
    <Head title="Modifier Centre" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier le Centre {{ centre.id_centre }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Centre</label>
                    <input
                        v-model="form.id_centre"
                        type="text"
                        disabled
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                    <input
                        v-model="form.adresse_centre"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.adresse_centre" class="text-red-500 text-sm">{{ form.errors.adresse_centre }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seuil</label>
                    <input
                        v-model="form.seuil_centre"
                        type="number"
                        step="0.01"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.seuil_centre" class="text-red-500 text-sm">{{ form.errors.seuil_centre }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select
                        v-model="form.type_centre"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un type --</option>
                        <option value="Aviation">Aviation</option>
                        <option value="Marine">Marine</option>
                    </select>
                    <div v-if="form.errors.type_centre" class="text-red-500 text-sm">{{ form.errors.type_centre }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        href="/centres"
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
