<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
    centres: { id_centre: string, adresse_centre: string }[]
}>()

const form = useForm({
    n_dra: '',
    date_creation: '',
})

function submit() {
    form.post(route('achat.dras.store'))
}
</script>

<template>
    <Head title="Créer un DRA" />
    <AppLayout>
        <div class="p-5">
            <h1 class="text-lg font-bold mb-5">Créer un nouveau DRA</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>N° DRA</label>
                    <input v-model="form.n_dra" type="text" class="w-full border p-2 rounded" required />
                    <div v-if="form.errors.n_dra" class="text-red-500">{{ form.errors.n_dra }}</div>
                </div>

                <div>
                    <label>Date de création</label>
                    <input v-model="form.date_creation" type="date" class="w-full border p-2 rounded" required />
                    <div v-if="form.errors.date_creation" class="text-red-500">{{ form.errors.date_creation }}</div>
                </div>

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
