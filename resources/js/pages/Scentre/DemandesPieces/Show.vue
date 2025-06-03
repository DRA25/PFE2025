<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    demande: {
        id_dp: number;
        date_dp: string;
        etat_dp: string;
        qte_demandep: number;
        piece?: { nom_piece: string };
        magasin?: {
            adresse_magasin: string;
            centre?: { id_centre: number };
        };
        atelier?: {
            adresse_atelier: string;
            centre?: { id_centre: number };
        };
    };
}>();

const form = useForm({
    etat_dp: props.demande.etat_dp
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: route('scentre.index') },
    { title: 'Demandes de Pièces', href: route('scentre.demandes-pieces.index') },
    { title: 'Détails', href: route('scentre.demandes-pieces.show', { demande_piece: props.demande.id_dp }) }
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
                                demande.magasin?.centre?.id_centre
                                || demande.atelier?.centre?.id_centre
                                || 'N/A'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quantité</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.qte_demandep }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nom de la Pièce</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ demande.piece?.nom_piece || 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">
                        Mettre à jour l'état
                    </h2>

                    <form @submit.prevent="form.put(route('scentre.demandes-pieces.update', { demande_piece: demande.id_dp }))">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                État
                            </label>
                            <select v-model="form.etat_dp"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-[#042B62] focus:border-[#042B62] dark:bg-gray-700 dark:text-white">
                                <option value="En attente">En attente</option>
                                <option value="Validée">Validée</option>
                                <option value="Refusée">Refusée</option>
                                <option value="Livrée">Livrée</option>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Mettre à jour
                            </button>
                            <a
                                :href="route('scentre.demandes-pieces.export-single-pdf', { demande_piece: demande.id_dp })"
                                target="_blank"
                                class="px-4 py-2 ml-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            >
                                Exporter en PDF
                            </a>
                        </div>
                    </form>

                </div>



            </div>
        </div>
    </AppLayout>
</template>
