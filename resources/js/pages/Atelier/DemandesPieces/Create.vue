<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';


const page = usePage();


defineProps<{
    pieces: {
        id_piece: number;
        nom_piece: string;
    }[];

}>();

const maxDate = computed(() => {
    const today = new Date();
    const year = today.getFullYear();
    const month = (today.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-indexed
    const day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
});


const form = useForm({
    date_dp: new Date().toISOString().split('T')[0],
    // etat_dp field removed as it will be set by default in the backend
    id_piece: '',
    qte_demandep: 1,
    // id_magasin: null, // Removed - keeping original fields
    // id_atelier: null, // Removed - keeping original fields
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: '/atelier' },
    { title: 'Demandes de Pièces', href: route('atelier.demandes-pieces.index') },
    { title: 'Créer', href: route('atelier.demandes-pieces.create') }
];

// Original submit function retained
function submit() {
    form.post(route('atelier.demandes-pieces.store'));
}
</script>

<template>
    <Head title="Créer une Demande de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Créer une Demande de Pièces
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <!-- Error display section (from target design) -->
                <div v-if="Object.keys(form.errors).length" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>


                <div class="space-y-2">
                    <label for="date_dp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input
                        id="date_dp"
                        v-model="form.date_dp"
                        type="date"
                        :max="maxDate"
                    class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.date_dp" class="text-red-500 text-sm">{{ form.errors.date_dp }}</div>
                </div>


                <div class="space-y-2">
                    <label for="qte_demandep" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                    <input
                        id="qte_demandep"
                        v-model="form.qte_demandep"
                        type="number"
                        min="1"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <div v-if="form.errors.qte_demandep" class="text-red-500 text-sm">{{ form.errors.qte_demandep }}</div>
                </div>

                <div class="space-y-2">
                    <label for="id_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piece</label>
                    <select
                        id="id_piece"
                        v-model="form.id_piece"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Sélectionner une piece</option>
                        <option v-for="piece in pieces" :key="piece.id_piece" :value="piece.id_piece">
                            {{ piece.nom_piece }}
                        </option>
                    </select>
                    <div v-if="form.errors.id_piece" class="text-red-500 text-sm">{{ form.errors.id_piece }}</div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        :href="route('atelier.demandes-pieces.index')"
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
