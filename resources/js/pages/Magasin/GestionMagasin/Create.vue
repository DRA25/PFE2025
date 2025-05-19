<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des Magasins', href: '/gestionmagasin' },
    { title: 'Créer un Magasin', href: '/gestionmagasin/create' },
]

const props = defineProps<{
    centres: Array<{
        id_centre: string,
        adresse_centre: string,
    }>
}>()

const form = useForm({
    id_magasin: '',
    adresse_magasin: '',
    id_centre: null,
})

function submit() {
    form.post('/gestionmagasin')
}
</script>

<template>
    <Head title="Créer un Magasin" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Créer un Magasin
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Magasin</label>
                    <input
                        v-model="form.id_magasin"
                        type="number"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.id_magasin" class="text-red-500 text-sm">{{ form.errors.id_magasin }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                    <input
                        v-model="form.adresse_magasin"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.adresse_magasin" class="text-red-500 text-sm">{{ form.errors.adresse_magasin }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Centre</label>
                    <select
                        v-model="form.id_centre"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option :value="null">-- Aucun Centre --</option>
                        <option v-for="centre in centres" :key="centre.id_centre" :value="centre.id_centre">
                            {{ centre.adresse_centre }} ({{ centre.id_centre }})
                        </option>
                    </select>
                    <div v-if="form.errors.id_centre" class="text-red-500 text-sm">{{ form.errors.id_centre }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        href="/gestionmagasin"
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
