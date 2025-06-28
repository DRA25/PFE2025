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
    factures: Array<{
        n_facture: string
        date_facture: string
        id_fourn: number
        n_dra: string
        droit_timbre?: number
        fournisseur: {
            id_fourn: number
            nom_fourn: string
        }
        pieces: Array<{
            id_piece: number
            nom_piece: string
            tva: number
            pivot: {
                qte_f: number,
                prix_piece: number
            }
        }>,
        prestations: Array<{
            id_prest: number
            nom_prest: string
            tva: number
            pivot: {
                qte_fpr: number,
                prix_prest: number // prix_prest is now exclusively from the pivot
            }
        }>,
        charges: Array<{
            id_charge: number
            nom_charge: string
            prix_charge: number
            tva: number
            pivot: {
                qte_fc: number
            }
        }>,
    }>(),
})

const breadcrumbs: BreadcrumbItem[] = [
    { title:'Centre', href: '/scentre'},
    { title: 'Gestion des DRAs', href: route('scentre.dras.index') },
    { title: `Details de DRA ${props.dra.n_dra}`, href: route('scentre.dras.show', { dra: props.dra.n_dra }) },
    { title: `Factures pour DRA ${props.dra.n_dra}`, href: route('scentre.dras.factures.index', { dra: props.dra.n_dra }) },
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

// Calculate total amount for a facture including droit_timbre, pieces, prestations, and charges
const calculateMontant = (facture: typeof props.factures[0]) => {
    // Ensure facture.pieces is an array; default to empty if null/undefined
    const totalPieces = (facture.pieces ?? []).reduce((total, piece) => {
        // Use nullish coalescing to default to 0 if any value is null or undefined
        const prixPiece = piece.pivot.prix_piece ?? 0;
        const qteF = piece.pivot.qte_f ?? 0;
        const tva = piece.tva ?? 0;

        const subtotal = prixPiece * qteF;
        return total + (subtotal * (1 + (tva / 100)));
    }, 0);

    // Ensure facture.prestations is an array; default to empty if null/undefined
    const totalPrestations = (facture.prestations ?? []).reduce((total, prestation) => {
        // Use nullish coalescing to default to 0 if any value is null or undefined
        // *** IMPORTANT CHANGE HERE: prix_prest is now accessed from prestation.pivot.prix_prest ***
        const prixPrest = prestation.pivot.prix_prest ?? 0;
        const qteFpr = prestation.pivot.qte_fpr ?? 0;
        const tva = prestation.tva ?? 0; // TVA is still on the main prestation object

        const subtotal = prixPrest * qteFpr;
        return total + (subtotal * (1 + (tva / 100)));
    }, 0);

    // Ensure facture.charges is an array; default to empty if null/undefined
    const totalCharges = (facture.charges ?? []).reduce((total, charge) => {
        // Use nullish coalescing to default to 0 if any value is null or undefined
        const prixCharge = charge.prix_charge ?? 0;
        const qteFc = charge.pivot.qte_fc ?? 0;
        const tva = charge.tva ?? 0;

        const subtotal = prixCharge * qteFc;
        return total + (subtotal * (1 + (tva / 100)));
    }, 0);

    // Default droit_timbre to 0 if null or undefined
    const timbre = facture.droit_timbre ?? 0;
    return totalPieces + totalPrestations + totalCharges + timbre;
}

const sortedFactures = computed(() => {
    let data = [...props.factures];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(facture =>
            String(facture.n_facture).toLowerCase().includes(query) ||
            String(calculateMontant(facture)).toLowerCase().includes(query) ||
            facture.date_facture.toLowerCase().includes(query) ||
            facture.fournisseur?.nom_fourn?.toLowerCase().includes(query) ||
            facture.pieces?.some(piece => piece.nom_piece.toLowerCase().includes(query)) ||
            facture.prestations?.some(prestation => prestation.nom_prest.toLowerCase().includes(query)) ||
            facture.charges?.some(charge => charge.nom_charge.toLowerCase().includes(query))
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
                // Ensure to handle cases where properties might be missing gracefully
                valA = (a as any)[column] ?? '';
                valB = (b as any)[column] ?? '';
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
    <Head title="Factures pour DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par ID, montant, date, fournisseur, pièce, prestation ou charge..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
            <Link
                :href="route('scentre.dras.factures.create', { dra: props.dra.n_dra })"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Ajouter une Facture
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Factures pour DRA {{ props.dra.n_dra }}
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('n_facture')">
                            ID Facture
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('montant')">
                            Montant (DA)
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_facture')">
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
                    <template v-if="sortedFactures.length > 0">
                        <TableRow
                            v-for="facture in sortedFactures"
                            :key="facture.n_facture"
                            class="hover:bg-gray-300 dark:hover:bg-gray-900"
                        >
                            <TableCell>{{ facture.n_facture }}</TableCell>
                            <TableCell>{{ calculateMontant(facture).toFixed(2) }}</TableCell>
                            <TableCell>{{ new Date(facture.date_facture).toLocaleDateString() }}</TableCell>
                            <TableCell>{{ facture.fournisseur?.nom_fourn }}</TableCell>
                            <TableCell>
                                <div v-if="facture.pieces && facture.pieces.length > 0" class="mb-1">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">Pièces:</h4>
                                    <div v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot.qte_f }})
                                    </div>
                                </div>
                                <div v-if="facture.prestations && facture.prestations.length > 0" class="mb-1">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">Prestations:</h4>
                                    <div v-for="prestation in facture.prestations" :key="prestation.id_prest" class="text-sm">
                                        {{ prestation.nom_prest }} (x{{ prestation.pivot.qte_fpr }})
                                    </div>
                                </div>
                                <div v-if="facture.charges && facture.charges.length > 0">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">Charges:</h4>
                                    <div v-for="charge in facture.charges" :key="charge.id_charge" class="text-sm">
                                        {{ charge.nom_charge }} (x{{ charge.pivot.qte_fc }})
                                    </div>
                                </div>
                                <div v-if="(!facture.pieces || facture.pieces.length === 0) && (!facture.prestations || facture.prestations.length === 0) && (!facture.charges || facture.charges.length === 0)" class="text-sm text-gray-500 dark:text-gray-400">
                                    Aucune pièce, prestation ou charge
                                </div>
                            </TableCell>
                            <TableCell>
                                <Link
                                    :href="route('scentre.dras.factures.edit', { dra: props.dra.n_dra, facture: facture.n_facture })"
                                    class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition"
                                >
                                    <span class="inline-flex items-center space-x-1">
                                        <span>Modifier</span>
                                        <Pencil class="w-4 h-4" />
                                    </span>
                                </Link>
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell colspan="6" class="text-center py-4 text-gray-500">
                                Aucune facture trouvée pour ce DRA.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>

            <div class="m-5">
                <Link
                    :href="route('scentre.dras.show', { dra: props.dra.n_dra })"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la DRA</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
