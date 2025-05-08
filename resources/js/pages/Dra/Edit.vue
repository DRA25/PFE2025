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
        <div class="p-5">
            <h1 class="text-lg font-bold mb-5">Modifier le DRA</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Date de création</label>
                    <input v-model="form.date_creation" type="date" class="w-full border p-2 rounded" />
                    <div v-if="form.errors.date_creation" class="text-red-500">{{ form.errors.date_creation }}</div>
                </div>

                <div>
                    <label>Seuil DRA</label>
                    <input v-model="form.seuil_dra" type="number" class="w-full border p-2 rounded" />
                    <div v-if="form.errors.seuil_dra" class="text-red-500">{{ form.errors.seuil_dra }}</div>
                </div>

                <div>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-800">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
