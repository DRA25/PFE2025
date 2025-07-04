<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { watch } from 'vue'; // Import watch for reactivity

const props = defineProps<{
    demande: {
        id_dp: number;
        date_dp: string;
        etat_dp: string;
        qte_demandep: number;
        qte_stocke?: number;
        motif?: string | null; // Added motif prop
        piece?: {
            nom_piece: string;
            id_piece: number;
        };
        magasin?: {
            id_magasin: number;
            adresse_magasin: string;
            centre?: { id_centre: number; nom_centre?: string };
        };
        atelier?: {
            adresse_atelier: string;
            centre?: { id_centre: number; nom_centre?: string };
        };
    };
    etatOptions: string[];
}>();

const form = useForm({
    etat_dp: props.demande.etat_dp,
    motif: props.demande.motif || null, // Initialize motif from prop or null
});

// Watch for changes in etat_dp to clear motif if it's not 'refuse'
watch(() => form.etat_dp, (newEtat) => {
    if (newEtat !== 'refuse') {
        form.motif = null;
    }
});

const livrerForm = useForm({});

const livrerPiece = () => {
    if (confirm('Êtes-vous sûr de vouloir livrer cette pièce? Cette action est irréversible.')) {
        livrerForm.post(route('magasin.mes-demandes.livrer', { demande_piece: props.demande.id_dp }), {
            onSuccess: () => {
                window.location.reload();
            },
            onError: () => {
                alert('Une erreur est survenue lors de la livraison.');
            }
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: route('magasin.index') },
    { title: 'Mes Demandes', href: route('magasin.mes-demandes.index') },
    { title: 'Détails', href: route('magasin.mes-demandes.show', { demande_piece: props.demande.id_dp }) }
];
</script>

<template>
    <Head title="Détails Demande de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B]">
                        Détails de la Demande
                    </h2>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">ID</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.id_dp }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.date_dp }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Origine</p>
                        <p class="text-gray-900 dark:text-gray-100">
                            {{
                                demande.magasin
                                    ? `Magasin - ${demande.magasin.adresse_magasin}`
                                    : demande.atelier
                                        ? `Atelier - ${demande.atelier.adresse_atelier}`
                                        : 'N/A'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Centre</p>
                        <p class="text-gray-900 dark:text-gray-100">
                            {{
                                demande.magasin?.centre?.nom_centre
                                || demande.magasin?.centre?.id_centre
                                || demande.atelier?.centre?.nom_centre
                                || demande.atelier?.centre?.id_centre
                                || 'N/A'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quantité Demandée</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.qte_demandep }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quantité Disponible en Stock</p>
                        <p class="text-gray-900 dark:text-gray-100" :class="{
                            'text-red-500 dark:text-red-400': demande.qte_stocke !== undefined && demande.qte_stocke < demande.qte_demandep,
                            'text-green-600 dark:text-green-400': demande.qte_stocke !== undefined && demande.qte_stocke >= demande.qte_demandep
                        }">
                            {{ demande.qte_stocke !== undefined ? demande.qte_stocke : 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nom de la Pièce</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.piece?.nom_piece || 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Statut</p>
                        <p class="text-gray-900 dark:text-gray-100" :class="{
                            'text-yellow-600 dark:text-yellow-400': demande.etat_dp === 'en attente' || demande.etat_dp === 'non disponible',
                            'text-green-600 dark:text-green-400': demande.etat_dp === 'livre',
                            'text-red-600 dark:text-red-400': demande.etat_dp === 'refuse'
                        }">
                            {{ demande.etat_dp }}
                        </p>
                    </div>

                    <!-- Display Motif if available -->
                    <div v-if="demande.motif">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Motif du Refus</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.motif }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">
                            Mettre à jour l'état
                        </h2>

                        <form @submit.prevent="form.put(route('magasin.mes-demandes.update', { demande_piece: demande.id_dp }))">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    État
                                </label>
                                <select v-model="form.etat_dp"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white">
                                    <option v-for="option in etatOptions" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                            </div>

                            <!-- Motif field, conditionally rendered -->
                            <div v-if="form.etat_dp === 'refuse'" class="mb-4">
                                <label for="motif" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Motif du Refus
                                </label>
                                <textarea
                                    id="motif"
                                    v-model="form.motif"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white"
                                    placeholder="Veuillez indiquer la raison du refus..."
                                ></textarea>
                                <p v-if="form.errors.motif" class="text-sm text-red-600">{{ form.errors.motif }}</p>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Mettre à jour
                                </button>
                                <a
                                    :href="route('magasin.mes-demandes.pdf', { demande_piece: demande.id_dp })"
                                    target="_blank"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                >
                                    Exporter en PDF
                                </a>
                            </div>
                        </form>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">
                            Actions
                        </h2>

                        <div class="space-y-4">
                            <button
                                @click="livrerPiece"
                                :disabled="demande.etat_dp === 'livre' || (demande.qte_stocke !== undefined && demande.qte_stocke < demande.qte_demandep) || demande.etat_dp === 'refuse'"
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Livrer Pièce
                            </button>

                            <div v-if="demande.etat_dp === 'livre'" class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md">
                                Cette pièce a déjà été livrée.
                            </div>
                            <div v-else-if="demande.etat_dp === 'refuse'" class="p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-md">
                                Cette demande a été refusée et ne peut pas être livrée.
                            </div>
                            <div v-else-if="demande.qte_stocke !== undefined && demande.qte_stocke < demande.qte_demandep" class="p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-md">
                                Quantité insuffisante en stock pour livrer cette demande.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
