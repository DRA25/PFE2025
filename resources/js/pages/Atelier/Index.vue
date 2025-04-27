<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Trash, Pencil } from 'lucide-vue-next';

defineProps<{
    pieces: {
        id_piece: number;
        nom_piece: string;
        prix_piece: number;
        marque_piece: string;
        ref_piece: string;
    }[]
}>();

// Mise à jour du breadcrumb avec la nouvelle route "/atelier"
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atelier', // Titre du breadcrumb modifié
        href: '/atelier', // Route mise à jour
    },
];

function deletePiece(id_piece: number) {
    if (confirm('Voulez-vous vraiment supprimer cette pièce ?')) {
        // Mise à jour de la route pour la suppression
        axios.delete(`/atelier/${id_piece}`)
            .then(() => {
                alert('Pièce supprimée avec succès');

            })
            .catch((error) => {
                console.error(error);
                alert('Une erreur est survenue lors de la suppression');
            });
    }
}
</script>

<template>
    <Head title="Atelier" /> <!-- Titre mis à jour -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <!-- Lien pour ajouter une pièce (nouvelle route "/atelier/create") -->
            <Link
                href="/atelier/create"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Ajouter une Pièce
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Pièces
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">ID</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Nom</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Prix</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Marque</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Référence</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="piece in pieces"
                        :key="piece.id_piece"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900 cursor-pointer"
                        @click="$inertia.visit(`/atelier/${piece.id_piece}/edit`)"
                    ><TableCell>{{ piece.id_piece }}</TableCell>
                    <TableCell>{{ piece.nom_piece }}</TableCell>
                    <TableCell>{{ piece.prix_piece }} €</TableCell>
                    <TableCell>{{ piece.marque_piece }}</TableCell>
                    <TableCell>{{ piece.ref_piece }}</TableCell>

                    <TableCell class="flex space-x-2">
                        <!-- Lien pour modifier une pièce, avec la nouvelle route "/atelier/{id}/edit" -->
                        <Link
                            :href="`/atelier/${piece.id_piece}/edit`"
                            class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition"
                        >
                                <span class="inline-flex items-center space-x-1">
                                    <span>Modifier</span>
                                    <Pencil class="w-4 h-4" />
                                </span>
                        </Link>

                        <!-- Bouton pour supprimer une pièce, avec la nouvelle route "/atelier/{id}" -->
                        <button
                            @click.stop="deletePiece(piece.id_piece)"
                            class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-400 transition"
                        >
                                <span class="inline-flex items-center space-x-1">
                                    <span>Supprimer</span>
                                    <Trash class="w-4 h-4" />
                                </span>
                        </button>
                    </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>


