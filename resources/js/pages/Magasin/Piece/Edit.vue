<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Trash2 } from 'lucide-vue-next'; // Ensure this import is available in your project

const props = defineProps<{
    piece: {
        id_piece: number;
        nom_piece: string;
        prix_piece: number;
        marque_piece: string;
        ref_piece: string;
        // The following properties are added to match the target page's form structure and props
        tva?: number; // Optional as it might not always be present from previous forms
        compte_general_code?: string;
        compte_analytique_code?: string;
    };
    // These props are added to match the target page's script setup for select options
    comptesGeneraux?: { code: string; libelle: string }[];
    comptesAnalytiques?: { code: string; libelle: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: route('magasin.index') },
    { title: 'Pièces', href: route('magasin.pieces.index') },
    { title: 'Modifier', href: route('magasin.pieces.edit', props.piece.id_piece) }
];

const form = useForm({
    id_piece: props.piece.id_piece,
    nom_piece: props.piece.nom_piece,
    prix_piece: props.piece.prix_piece,
    marque_piece: props.piece.marque_piece,
    ref_piece: props.piece.ref_piece,
    // Initialize with existing data or sensible defaults for new fields
    tva: props.piece.tva ?? null,
    compte_general_code: props.piece.compte_general_code ?? '',
    compte_analytique_code: props.piece.compte_analytique_code ?? '',
});

function submit() {
    form.put(route('magasin.pieces.update', props.piece.id_piece));
}

function destroyPiece() {
    if (confirm("Êtes-vous sûr de vouloir supprimer cette pièce ?")) {
        form.delete(route('magasin.pieces.destroy', form.id_piece), {
            onSuccess: () => {
                window.location.href = route('magasin.pieces.index');
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
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier la pièce #{{ form.id_piece }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label for="id_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Pièce</label>
                    <input
                        id="id_piece"
                        v-model="form.id_piece"
                        type="number"
                        disabled
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white disabled:opacity-50"
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
                    <label for="prix_piece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix</label>
                    <input
                        id="prix_piece"
                        v-model="form.prix_piece"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="form.errors.prix_piece" class="text-sm text-red-600">{{ form.errors.prix_piece }}</p>
                </div>

                <div class="space-y-2">
                    <label for="tva" class="block text-sm font-medium text-gray-700 dark:text-gray-300">TVA (%)</label>
                    <input
                        id="tva"
                        v-model="form.tva"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
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
                        <option value="">-- Sélectionnez un compte général --</option>
                        <option
                            v-for="compte in props.comptesGeneraux"
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
                        <option value="">-- Sélectionnez un compte analytique --</option>
                        <option
                            v-for="compte in props.comptesAnalytiques"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <p v-if="form.errors.compte_analytique_code" class="text-sm text-red-600">{{ form.errors.compte_analytique_code }}</p>
                </div>

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
                            :href="route('magasin.pieces.index')"
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
