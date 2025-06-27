<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue'; // Import ref for modal state

const props = defineProps<{
    piece: {
        id_piece: number;
        nom_piece: string;
        // prix_piece: number; // Removed as requested
        marque_piece: string;
        ref_piece: string;
        tva?: number; // Optional as it might not always be present from previous forms
        compte_general_code?: string;
        compte_analytique_code?: string;
    };
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
    // prix_piece: props.piece.prix_piece, // Removed from form initialization
    marque_piece: props.piece.marque_piece,
    ref_piece: props.piece.ref_piece,
    tva: props.piece.tva ?? null,
    compte_general_code: props.piece.compte_general_code ?? '',
    compte_analytique_code: props.piece.compte_analytique_code ?? '',
});

// Modal state for custom confirmation dialogs
const showConfirmationModal = ref(false);
const modalMessage = ref('');
const modalAction = ref<(() => void) | null>(null); // Function to execute on confirm or acknowledge

/**
 * Opens a custom confirmation modal.
 * @param message The message to display in the modal.
 * @param action The function to execute if the user confirms the action (e.g., delete).
 */
function openConfirmationModal(message: string, action: () => void) {
    modalMessage.value = message;
    modalAction.value = action;
    showConfirmationModal.value = true;
}

/**
 * Closes the custom confirmation modal and resets its state.
 */
function closeConfirmationModal() {
    showConfirmationModal.value = false;
    modalMessage.value = '';
    modalAction.value = null;
}

/**
 * Executes the modal's associated action if it exists, then closes the modal.
 */
function confirmAction() {
    if (modalAction.value) {
        modalAction.value();
    }
    closeConfirmationModal();
}

/**
 * Handles the form submission for updating the piece.
 * Displays a custom error modal if validation errors occur.
 */
function submit() {
    form.put(route('magasin.pieces.update', props.piece.id_piece), {
        onError: (errors) => {
            let errorMessage = 'Une erreur est survenue lors de la mise à jour.';
            if (Object.keys(errors).length > 0) {
                // Concatenate all error messages for display
                errorMessage += '\n' + Object.values(errors).join('\n');
            }
            // Display error message in the custom modal without an action on OK
            openConfirmationModal(errorMessage, () => {});
        }
    });
}

/**
 * Performs the actual delete operation after user confirmation.
 * Handles success by redirecting, and displays errors in a custom modal.
 */
function performDelete() {
    form.delete(route('magasin.pieces.destroy', form.id_piece), {
        onSuccess: () => {
            // Redirect to the pieces index page upon successful deletion
            window.location.href = route('magasin.pieces.index');
        },
        onError: (errors) => {
            let errorMessage = 'Erreur lors de la suppression.';
            if (Object.keys(errors).length > 0) {
                // Concatenate all error messages for display
                errorMessage += '\n' + Object.values(errors).join('\n');
            }
            // Display error message in the custom modal without an action on OK
            openConfirmationModal(errorMessage, () => {});
        }
    });
}

/**
 * Initiates the delete process by opening a custom confirmation modal.
 */
function destroyPiece() {
    openConfirmationModal("Êtes-vous sûr de vouloir supprimer cette pièce ? Cette action est irréversible.", performDelete);
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

                <!-- Removed prix_piece field as requested -->
                <!--
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
                -->

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

        <!-- Custom Confirmation Modal -->
        <div v-if="showConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-sm w-full mx-4">
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ modalMessage }}</p>
                <div class="flex justify-end space-x-4">
                    <button
                        @click="closeConfirmationModal"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                    >
                        Annuler
                    </button>
                    <button
                        @click="confirmAction"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
                    >
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
