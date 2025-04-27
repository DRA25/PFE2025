<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Props from Laravel
const props = defineProps<{
    fournisseurs: { id_fourn: number; nom_fourn: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Achat', href: '/achat/dra' },
    { title: 'DRA', href: '/achat/dra' },
    { title: 'Créer', href: '/achat/dra/create' },
];

const form = useForm({
    n_dra: '',
    periode: '',
    etat: '',
    cmp_gen: null,
    cmp_ana: null,
    debit: null,
    libelle_dra: '',
    date_dra: '',
    fourn_dra: '',
    destinataire_dra: '',
});

function submit() {
    form.post(route('dra.store'));
}
</script>

<template>
    <Head title="Créer une DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Créer une nouvelle DRA
                </h1>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Input fields -->
                <div v-for="(label, field) in {
                    n_dra: 'Numéro DRA',
                    periode: 'Période',
                    etat: 'État',
                    cmp_gen: 'Compte Général',
                    cmp_ana: 'Compte Analytique',
                    debit: 'Débit',
                    libelle_dra: 'Libellé',
                    date_dra: 'Date DRA',
                    destinataire_dra: 'Destinataire'
                }" :key="field">
                    <label :for="field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ label }}
                    </label>
                    <input
                        v-model="form[field]"
                        :id="field"
                        :type="field.includes('date') || field === 'periode' ? 'date' : field.includes('debit') || field.includes('cmp') ? 'number' : 'text'"
                        class="mt-1 p-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-[#070736] dark:text-white"
                    />
                </div>

                <!-- Fournisseur Dropdown -->
                <div>
                    <label for="fourn_dra" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fournisseur
                    </label>
                    <select
                        v-model="form.fourn_dra"
                        id="fourn_dra"
                        class="mt-1 p-1 block w-full rounded-md bg-white border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-[#070736] dark:text-white"
                    >
                        <option value="">-- Sélectionnez un fournisseur --</option>
                        <option
                            v-for="fourn in props.fournisseurs"
                            :key="fourn.id_fourn"
                            :value="fourn.id_fourn"
                        >
                            {{ fourn.nom_fourn }}
                        </option>
                    </select>
                </div>

                <!-- Submit button -->
                <div class="md:col-span-2 flex justify-end mt-4">
                    <button
                        type="submit"
                        class="bg-[#042B62] dark:bg-[#F3B21B] dark:hover:bg-yellow-200 dark:text-[#042B62] text-white font-semibold py-2 px-6 rounded hover:bg-blue-900 transition"
                    >
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
