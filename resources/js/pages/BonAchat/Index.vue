<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
    Table
} from '@/components/ui/table'
import { type BreadcrumbItem } from '@/types'
import { Pencil, ArrowLeft, ArrowUpDown, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps({
    dra: Object,
    bonAchats: Array<{
        n_ba: string
        montant_ba: number
        date_ba: string
        id_fourn: number
        n_dra: string
        fournisseur: {
            id_fourn: number
            nom_fourn: string
        }
        pieces: Array<{
            id_piece: number
            nom_piece: string
            prix_piece: number
            tva: number
            pivot: {
                qte_ba: number
            }
        }>
    }>(),
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: '/achat' },
    { title: 'Gestion des DRAs', href: route('achat.dras.index') },
    { title: `Details de DRA ${props.dra.n_dra}`, href: route('achat.dras.show', { dra: props.dra.n_dra }) },
    { title: `Bons d'achat pour DRA ${props.dra.n_dra}`, href: route('achat.dras.bon-achats.index', { dra: props.dra.n_dra }) },
]

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
}

const calculateMontant = (bonAchat: typeof props.bonAchats[0]) => {
    return bonAchat.pieces.reduce((total, piece) => {
        const subtotal = piece.prix_piece * piece.pivot.qte_ba;
        return total + subtotal * (1 + (piece.tva / 100));
    }, 0);
}

const sortedBonAchats = computed(() => {
    let data = [...props.bonAchats];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(bonAchat =>
            String(bonAchat.n_ba).toLowerCase().includes(query) ||
            String(calculateMontant(bonAchat)).toLowerCase().includes(query) ||
            bonAchat.date_ba.toLowerCase().includes(query) ||
            bonAchat.fournisseur?.nom_fourn?.toLowerCase().includes(query) ||
            bonAchat.pieces?.some(piece => piece.nom_piece.toLowerCase().includes(query))
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA, valB;

            if (column === 'montant') {
                valA = calculateMontant(a);
                valB = calculateMontant(b);
            } else if (column === 'fournisseur.nom_fourn') {
                valA = a.fournisseur?.nom_fourn ?? '';
                valB = b.fournisseur?.nom_fourn ?? '';
            } else {
                valA = a[column as keyof typeof a] ?? '';
                valB = b[column as keyof typeof b] ?? '';
            }

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
    <Head title="Bons d'achat pour DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par ID, montant, date ou fournisseur..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
            <Link
                :href="route('achat.dras.bon-achats.create', { dra: props.dra.n_dra })"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Ajouter un Bon d'achat
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Bons d'achat pour DRA {{ props.dra.n_dra }}
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('n_ba')">
                            ID Bon Achat
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('montant')">
                            Montant (DA)
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_ba')">
                            Date
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('fournisseur.nom_fourn')">
                            Fournisseur
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Libellé</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="bonAchat in sortedBonAchats"
                        :key="bonAchat.n_ba"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ bonAchat.n_ba }}</TableCell>
                        <TableCell>{{ calculateMontant(bonAchat).toFixed(2) }}</TableCell>
                        <TableCell>{{ new Date(bonAchat.date_ba).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ bonAchat.fournisseur?.nom_fourn }}</TableCell>
                        <TableCell>
                            <div v-for="piece in bonAchat.pieces" :key="piece.id_piece" class="text-sm">
                                {{ piece.nom_piece }} (x{{ piece.pivot.qte_ba }})
                            </div>
                        </TableCell>
                        <TableCell>
                            <Link
                                :href="route('achat.dras.bon-achats.edit', { dra: props.dra.n_dra, bonAchat: bonAchat.n_ba })"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition"
                            >
                                <span class="inline-flex items-center space-x-1">
                                    <span>Modifier</span>
                                    <Pencil class="w-4 h-4" />
                                </span>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="m-5">
                <Link
                    :href="route('achat.dras.show', { dra: props.dra.n_dra })"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la DRA</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
