<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    compteGenerals: Array<{ code: string; libelle: string }>,
    compteAnalytiques: Array<{ code: string; libelle: string }>,
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: route('atelier.index') },
    { title: 'Pièces', href: route('atelier.pieces.index') },
    { title: 'Créer', href: route('atelier.pieces.create') },
];

const form = useForm({
    id_piece: null as number | null,
    nom_piece: '',
    tva: null as number | null,
    marque_piece: '',
    ref_piece: '',
    compte_general_code: '',
    compte_analytique_code: '',
});

function submit() {
    form.post(route('atelier.pieces.store'));
}
</script>

<template>
    <Head title="Ajouter une pièce" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Ajouter une pièce
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label for="id_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Pièce</label>
                    <input
                        id="id_piece"
                        v-model="form.id_piece"
                        type="number"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="form.errors.id_piece" class="text-sm text-red-600">{{ form.errors.id_piece }}</p>
                </div>

                <div class="space-y-2">
                    <label for="nom_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                    <input
                        id="nom_piece"
                        v-model="form.nom_piece"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="form.errors.nom_piece" class="text-sm text-red-600">{{ form.errors.nom_piece }}</p>
                </div>

                <div class="space-y-2">
                    <label for="tva" class="block text-sm font-medium text-gray-700 dark:text-gray-300">TVA (%)</label>
                    <input
                        id="tva"
                        v-model="form.tva"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="form.errors.tva" class="text-sm text-red-600">{{ form.errors.tva }}</p>
                </div>

                <div class="space-y-2">
                    <label for="marque_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marque</label>
                    <input
                        id="marque_piece"
                        v-model="form.marque_piece"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="form.errors.marque_piece" class="text-sm text-red-600">{{ form.errors.marque_piece }}</p>
                </div>

                <div class="space-y-2">
                    <label for="ref_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Référence</label>
                    <input
                        id="ref_piece"
                        v-model="form.ref_piece"
                        type="text"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="form.errors.ref_piece" class="text-sm text-red-600">{{ form.errors.ref_piece }}</p>
                </div>

                <div class="space-y-2">
                    <label for="compte_general_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Compte Général</label>
                    <select
                        id="compte_general_code"
                        v-model="form.compte_general_code"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="" disabled>-- Sélectionnez un compte général --</option>
                        <option
                            v-for="compte in props.compteGenerals"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <p v-if="form.errors.compte_general_code" class="text-sm text-red-600">{{ form.errors.compte_general_code }}</p>
                </div>

                <div class="space-y-2">
                    <label for="compte_analytique_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Compte Analytique</label>
                    <select
                        id="compte_analytique_code"
                        v-model="form.compte_analytique_code"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="" disabled>-- Sélectionnez un compte analytique --</option>
                        <option
                            v-for="compte in props.compteAnalytiques"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <p v-if="form.errors.compte_analytique_code" class="text-sm text-red-600">{{ form.errors.compte_analytique_code }}</p>
                </div>

                <div class="flex justify-end space-x-4 pt-4 md:col-span-2">
                    <Link
                        :href="route('atelier.pieces.index')"
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
            </form>
        </div>
    </AppLayout>
</template>
