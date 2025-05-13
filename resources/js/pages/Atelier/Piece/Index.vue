<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Pencil, Trash2 } from 'lucide-vue-next';

defineProps<{
    pieces: {
        id_piece: number;
        nom_piece: string;
        prix_piece: number;
        marque_piece: string;
        ref_piece: string;
    }[]
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: '/atelier' },
    { title: 'Pièces', href: route('atelier.pieces.index') }
];

function deletePiece(id_piece: number) {
    if (confirm('Voulez-vous vraiment supprimer cette pièce ?')) {
        router.delete(route('atelier.pieces.destroy', id_piece), {
            onSuccess: () => {},
            onError: () => alert('Erreur lors de la suppression')
        });
    }
}
</script>

<template>
    <Head title="Liste des Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="route('atelier.pieces.create')"
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
                        <TableHead>ID</TableHead>
                        <TableHead>Nom</TableHead>
                        <TableHead>Prix</TableHead>
                        <TableHead>Marque</TableHead>
                        <TableHead>Référence</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="piece in pieces" :key="piece.id_piece">
                        <TableCell>{{ piece.id_piece }}</TableCell>
                        <TableCell>{{ piece.nom_piece }}</TableCell>
                        <TableCell>{{ piece.prix_piece }} DA</TableCell>
                        <TableCell>{{ piece.marque_piece }}</TableCell>
                        <TableCell>{{ piece.ref_piece }}</TableCell>
                        <TableCell class="flex space-x-2">
                            <Link
                                :href="route('atelier.pieces.edit', piece.id_piece)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>
                            <button
                                @click.stop="deletePiece(piece.id_piece)"
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
