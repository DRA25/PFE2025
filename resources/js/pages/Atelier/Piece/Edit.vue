<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    piece: {
        id_piece: number;
        nom_piece: string;
        prix_piece: number;
        tva: number;
        marque_piece: string;
        ref_piece: string;
        compte_general_code: string;
        compte_analytique_code: string;
    };
    comptesGeneraux: { code: string; libelle: string }[];
    comptesAnalytiques: { code: string; libelle: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: route('atelier.index') },
    { title: 'Pièces', href: route('atelier.pieces.index') },
    { title: 'Modifier', href: route('atelier.pieces.edit', props.piece.id_piece) },
];

const form = useForm({
    id_piece: props.piece.id_piece,
    nom_piece: props.piece.nom_piece,
    prix_piece: props.piece.prix_piece,
    tva: props.piece.tva,
    marque_piece: props.piece.marque_piece,
    ref_piece: props.piece.ref_piece,
    compte_general_code: props.piece.compte_general_code || '',
    compte_analytique_code: props.piece.compte_analytique_code || '',
});

function submit() {
    form.put(route('atelier.pieces.update', props.piece.id_piece));
}

function destroyPiece() {
    if (confirm("Êtes-vous sûr de vouloir supprimer cette pièce ?")) {
        form.delete(route('atelier.pieces.destroy', form.id_piece), {
            onSuccess: () => {
                window.location.href = route('atelier.pieces.index');
            },
            onError: () => {
                console.log("Erreur lors de la suppression.");
            }
        });
    }
}
</script>

<template>
    <Head title="Modifier Pièce" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <h1 class="text-lg font-bold mb-6 text-[#042B62FF] dark:text-[#BDBDBDFF]">
                Modifier la pièce #{{ form.id_piece }}
            </h1>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <!-- Input fields -->
                <div v-for="(label, field) in {
                    nom_piece: 'Nom',
                    prix_piece: 'Prix',
                    tva: 'TVA (%)',
                    marque_piece: 'Marque',
                    ref_piece: 'Référence'
                }" :key="field" class="space-y-2">
                    <label :for="field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ label }}
                    </label>
                    <input
                        v-model="form[field]"
                        :id="field"
                        :type="field.includes('prix') || field === 'tva' ? 'number' : 'text'"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        :step="field === 'tva' || field.includes('prix') ? 0.01 : undefined"
                        :min="field === 'tva' ? 0 : undefined"
                        :max="field === 'tva' ? 100 : undefined"
                    />
                    <div v-if="form.errors[field]" class="text-red-500 text-sm">{{ form.errors[field] }}</div>
                </div>

                <!-- Compte Général -->
                <div class="space-y-2">
                    <label for="compte_general_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Compte Général
                    </label>
                    <select
                        v-model="form.compte_general_code"
                        id="compte_general_code"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un compte général --</option>
                        <option
                            v-for="compte in props.comptesGeneraux"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <div v-if="form.errors.compte_general_code" class="text-red-500 text-sm">{{ form.errors.compte_general_code }}</div>
                </div>

                <!-- Compte Analytique -->
                <div class="space-y-2">
                    <label for="compte_analytique_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Compte Analytique
                    </label>
                    <select
                        v-model="form.compte_analytique_code"
                        id="compte_analytique_code"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un compte analytique --</option>
                        <option
                            v-for="compte in props.comptesAnalytiques"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <div v-if="form.errors.compte_analytique_code" class="text-red-500 text-sm">{{ form.errors.compte_analytique_code }}</div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="destroyPiece"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>
                    <div class="flex gap-4">
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
                            <span v-if="!form.processing">Enregistrer les modifications</span>
                            <span v-else class="animate-spin">↻</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
