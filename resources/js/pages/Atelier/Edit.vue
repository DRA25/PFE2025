<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    piece: {
        id_piece: number;
        nom_piece: string;
        prix_piece: number;
        marque_piece: string;
        ref_piece: string;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atelier',
        href: '/atelier',
    },
    {
        title: 'Modifier',
        href: `/atelier/${props.piece.id_piece}/edit`,
    },
];

const form = useForm({
    id_piece: props.piece.id_piece,
    nom_piece: props.piece.nom_piece,
    prix_piece: props.piece.prix_piece,
    marque_piece: props.piece.marque_piece,
    ref_piece: props.piece.ref_piece,
});

function submit() {
    form.put(route('atelier.update', props.piece.id_piece));
}
</script>

<template>
    <Head title="Modifier Pièce - Atelier" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-[#042B62FF] dark:text-[#BDBDBDFF]">Modifier Pièce</h1>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Reusable Field Group -->
                <div v-for="(label, field) in {
                    id_piece: 'ID',
                    nom_piece: 'Nom de la pièce',
                    prix_piece: 'Prix',
                    marque_piece: 'Marque',
                    ref_piece: 'Référence'
                }" :key="field">
                    <label :for="field" class="block  text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ label }}
                    </label>
                    <input
                        v-model="form[field]"
                        :id="field"
                        :type="['prix_piece', 'id_piece'].includes(field) ? 'number' : 'text'"
                        class="mt-1 p-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-[#070736] dark:text-white"
                    />
                </div>

                <div class="md:col-span-2 flex justify-end mt-4">
                    <button
                        type="submit"
                        class="bg-[#042B62] dark:bg-[#F3B21B] dark:hover:bg-yellow-200 dark:text-[#042B62] text-white font-semibold py-2 px-6 rounded hover:bg-blue-900 transition"
                    >
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
