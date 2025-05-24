<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Plus, Trash2, ArrowUpDown, Search } from 'lucide-vue-next';
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title:'Atelier', href: '/atelier'},
    { title: 'Gestion des Pièces', href: '/atelier/pieces' },
];

const props = defineProps<{
    pieces: Array<{
        id: number,
        id_piece: number,
        nom_piece: string,
        prix_piece: number,
        marque_piece: string,
        ref_piece: string,
        tva?: number | null,
        compte_general?: string | null,
        compte_analytique?: string | null,
        created_at: string,
        updated_at: string,
    }>,
    success?: string
}>();

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
}

const sortedPieces = computed(() => {
    let data = [...props.pieces];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(piece =>
            String(piece.id_piece).toLowerCase().includes(query) ||
            piece.nom_piece.toLowerCase().includes(query) ||
            String(piece.prix_piece).toLowerCase().includes(query) ||
            piece.marque_piece.toLowerCase().includes(query) ||
            piece.ref_piece.toLowerCase().includes(query) ||
            (piece.compte_general?.toLowerCase().includes(query) ?? false) ||
            (piece.compte_analytique?.toLowerCase().includes(query) ?? false)
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA = a[column as keyof typeof a] ?? '';
            let valB = b[column as keyof typeof b] ?? '';

            // If values are numbers, compare numerically
            if (typeof valA === 'number' && typeof valB === 'number') {
                return direction === 'asc' ? valA - valB : valB - valA;
            }

            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});



</script>

<template>
    <Head title="Liste des pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par ID, nom, prix, marque, référence, compte général ou analytique..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

            <Link
                :href="route('atelier.pieces.create')"
                as="button"
                class="px-4 py-2 rounded-lg transition cursor-pointer flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Ajouter une pièce</span>
            </Link>
        </div>

        <div v-if="success" class="mx-5 mt-2 p-3 bg-green-100 text-green-800 rounded-lg">
            {{ success }}
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des pièces
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('id_piece')">
                            ID Pièce
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('nom_piece')">
                            Nom
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('prix_piece')">
                            Prix
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('marque_piece')">
                            Marque
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('ref_piece')">
                            Référence
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('tva')">
                            TVA
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('compte_general')">
                            Compte Général
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('compte_analytique')">
                            Compte Analytique
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="piece in sortedPieces"
                        :key="piece.id"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ piece.id_piece }}</TableCell>
                        <TableCell>{{ piece.nom_piece }}</TableCell>
                        <TableCell>{{ piece.prix_piece }}</TableCell>
                        <TableCell>{{ piece.marque_piece }}</TableCell>
                        <TableCell>{{ piece.ref_piece }}</TableCell>
                        <TableCell>
                            {{
                                typeof piece.tva === 'number'
                                    ? piece.tva.toFixed(2)
                                    : '-'
                            }}
                        </TableCell>
                        <TableCell>{{ piece.compte_general ?? '-' }}</TableCell>
                        <TableCell>{{ piece.compte_analytique ?? '-' }}</TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="route('atelier.pieces.edit', piece)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>


                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
