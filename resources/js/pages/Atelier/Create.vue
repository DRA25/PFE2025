<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'atelier',
        href: '/atelier',
    },
    {
        title: 'Créer',
        href: route('atelier.create'),

    },
];

const form = useForm({
    id_piece:null,
    nom_piece: '',
    prix_piece: null,
    marque_piece: '',
    ref_piece: '',
});

function submit() {
    // Mise à jour de la route pour l'enregistrement de la pièce
    form.post(route('atelier.store'));
}
</script>

<template>
    <Head title="Ajouter une pièce à l'atelier" /> <!-- Titre mis à jour -->
    <AppLayout>
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <h1 class="text-lg font-bold mb-4 text-[#042B62FF] dark:text-[#BDBDBDFF]">Ajouter une pièce à l'atelier</h1> <!-- Titre modifié -->

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(label, field) in {

                    id_piece: 'id piece',
                    nom_piece: 'Nom de la pièce',
                    prix_piece: 'Prix',
                    marque_piece: 'Marque',
                    ref_piece: 'Référence'
                }" :key="field">
                    <label :for="field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ label }}
                    </label>
                    <input
                        v-model="form[field]"
                        :id="field"
                        :type="field.includes('prix') ? 'number' : 'text'"
                        class="mt-1 p-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-[#070736] dark:text-white"
                    />
                </div>

                <div class="md:col-span-2 flex justify-end mt-4">
                    <button
                        type="submit"
                        class="bg-[#042B62] dark:bg-[#F3B21B] dark:hover:bg-yellow-200 dark:text-[#042B62] text-white font-semibold py-2 px-6 rounded hover:bg-blue-900 transition"
                    >
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
