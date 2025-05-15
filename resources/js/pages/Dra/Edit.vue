<!-- resources/js/Pages/Achat/Dras/Edit.vue -->
<script setup lang="ts">
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Trash2 } from 'lucide-vue-next'

const props = defineProps<{
    dra: { n_dra: string, id_centre: string, date_creation: string }
    centres: { id_centre: string, adresse_centre: string }[]
}>()

const form = useForm({
    id_centre: props.dra.id_centre,
    date_creation: props.dra.date_creation,
})

function submit() {
    form.transform(data => ({
        ...data,
        _method: 'put',
    })).post(route('achat.dras.update', { dra: props.dra.n_dra }))
}

const deleteDra = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce DRA ? Cette action est irréversible et supprimera toutes les factures associées.')) {
        router.delete(route('achat.dras.destroy', { dra: props.dra.n_dra }), {
            preserveScroll: true,
            onSuccess: () => router.visit(route('achat.dras.index')),
            onError: (errors) => {
                alert('Erreur lors de la suppression du DRA: ' + (errors.message || 'Une erreur est survenue'))
            }
        })
    }
}
</script>

<template>
    <Head title="Modifier DRA" />
    <AppLayout>
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier le DRA {{ props.dra.n_dra }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <!-- ID Centre -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Centre</label>
                    <select v-model="form.id_centre" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white">
                        <option value="" disabled selected>Choisir un centre</option>
                        <option v-for="centre in props.centres" :key="centre.id_centre" :value="centre.id_centre">
                            {{ centre.id_centre }}
                        </option>
                    </select>
                    <div v-if="form.errors.id_centre" class="text-red-500 text-sm">{{ form.errors.id_centre }}</div>
                </div>

                <!-- Date de création -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de création</label>
                    <input
                        v-model="form.date_creation"
                        type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_creation" class="text-red-500 text-sm">{{ form.errors.date_creation }}</div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="deleteDra"
                        class="px-4 py-2 bg-red-800 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        <span>Supprimer</span>
                    </button>

                    <div class="flex gap-4">
                        <Link
                            :href="route('achat.dras.index')"
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
                </div>
            </form>
        </div>
    </AppLayout>
</template>
