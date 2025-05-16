<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Pencil, Trash2 } from 'lucide-vue-next';

defineProps<{
    demandes: {
        id_dp: number;
        date_dp: string;
        etat_dp: string;
        id_piece:number;
        qte_demandep: number;
        id_magasin: number;
        id_atelier: number;
        piece:{
            nom_piece: string;
        };
        magasin?: {
            adresse_magasin: string;
        };
        atelier?: {
            adresse_atelier: string;
        };
    }[]
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: '/atelier' },
    { title: 'Demandes de Pièces', href: route('atelier.demandes-pieces.index') }
];

function deleteDemande(id_dp: number) {
    if (confirm('Voulez-vous vraiment supprimer cette demande ?')) {
        router.delete(route('atelier.demandes-pieces.destroy', id_dp), {
            onSuccess: () => {},
            onError: () => alert('Erreur lors de la suppression')
        });
    }
}
</script>

<template>
    <Head title="Liste des Demandes de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="route('atelier.demandes-pieces.create')"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Créer une Demande
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Demandes de Pièces
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead>ID</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>État</TableHead>
                        <TableHead>Nom Piece</TableHead>
                        <TableHead>Quantité</TableHead>
                        <TableHead>Adresse Magasin</TableHead>
                        <TableHead>Adresse Atelier</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="demande in demandes" :key="demande.id_dp">
                        <TableCell>{{ demande.id_dp }}</TableCell>
                        <TableCell>{{ demande.date_dp }}</TableCell>
                        <TableCell>{{ demande.etat_dp }}</TableCell>
                        <TableCell>{{ demande.piece?.nom_piece }}</TableCell>
                        <TableCell>{{ demande.qte_demandep }}</TableCell>
                        <TableCell>{{ demande.magasin?.adresse_magasin || 'N/A' }}</TableCell>
                        <TableCell>{{ demande.atelier?.adresse_atelier || 'N/A' }}</TableCell>
                        <TableCell class="flex space-x-2">
                            <Link
                                :href="route('atelier.demandes-pieces.edit', demande.id_dp)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>
                            <button
                                @click.stop="deleteDemande(demande.id_dp)"
                                class="bg-red-800 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition flex items-center gap-1"
                            >
                                <Trash2 class="w-4 h-4" />
                                <span>Supprimer</span>
                            </button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
