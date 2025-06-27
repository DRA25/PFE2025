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
        // prix_piece is no longer a direct property of piece here as it's moved to pivot
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
    // prix_piece is intentionally excluded from the form as it's now handled on pivot tables
    tva: props.piece.tva,
    marque_piece: props.piece.marque_piece,
    ref_piece: props.piece.ref_piece,
    compte_general_code: props.piece.compte_general_code || '',
    compte_analytique_code: props.piece.compte_analytique_code || '',
});

// Modal state for custom confirmation dialogs
const showConfirmationModal = ref(false);
const modalMessage = ref('');
const modalAction = ref<(() => void) | null>(null); // Function to execute on confirm

/**
 * Opens a custom confirmation modal.
 * @param message The message to display in the modal.
 * @param action The function to execute if the user confirms the action.
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
    form.put(route('atelier.pieces.update', props.piece.id_piece), {
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
    form.delete(route('atelier.pieces.destroy', form.id_piece), {
        onSuccess: () => {
            // Redirect to the pieces index page upon successful deletion
            window.location.href = route('atelier.pieces.index');
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
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <h1 class="text-lg font-bold mb-6 text-[#042B62FF] dark:text-[#BDBDBDFF]">
                Modifier la pièce #{{ form.id_piece }}
            </h1>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <!-- Input fields for piece properties (excluding prix_piece) -->
                <div v-for="(label, field) in {
                    nom_piece: 'Nom',
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
                        :type="field === 'tva' ? 'number' : 'text'"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        :step="field === 'tva' ? 0.01 : undefined"
                        :min="field === 'tva' ? 0 : undefined"
                        :max="field === 'tva' ? 100 : undefined"
                    />
                    <div v-if="form.errors[field]" class="text-red-500 text-sm">{{ form.errors[field] }}</div>
                </div>

                <!-- Compte Général Select -->
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

                <!-- Compte Analytique Select -->
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

                <!-- Actions Buttons -->
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

        <!-- Custom Confirmation Modal Component -->
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
