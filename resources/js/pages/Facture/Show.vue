<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table'
import { type BreadcrumbItem } from '@/types'
import { ArrowLeft, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps<{
    dra: { n_dra: string },
    factures: Array<{
        n_facture: string,
        date_facture: string,
        droit_timbre?: number,
        fournisseur: {
            id_fourn: number,
            nom_fourn: string
        },
        pieces: Array<{
            id_piece: number,
            nom_piece: string,
            prix_piece: number,
            tva: number,
            pivot: {
                qte_f: number
            }
        }>,
        prestations: Array<{ // Add prestations to props
            id_prest: number,
            nom_prest: string,
            prix_prest: number,
            tva: number,
            pivot: {
                qte_fpr: number
            }
        }>,
        charges: Array<{ // Add charges to props
            id_charge: number,
            nom_charge: string,
            prix_charge: number,
            tva: number,
            pivot: {
                qte_fc: number
            }
        }>,
        montant: number // This 'montant' should ideally be calculated client-side for consistency, or ensured to be correctly passed from backend
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Coordination Financière', href: '/scf' },
    { title: 'Gestion des DRAs', href: route('scf.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('scf.dras.show', { dra: props.dra.n_dra }) },
    { title: `Factures pour DRA ${props.dra.n_dra}`, href: route('scf.dras.factures.show', { dra: props.dra.n_dra }) }
]

const expandedFacture = ref<string | null>(null)
const searchQuery = ref('')

function toggleDetails(n_facture: string) {
    expandedFacture.value = expandedFacture.value === n_facture ? null : n_facture
}

// Function to calculate total for an item type (pieces, prestations, charges)
function calculateItemTotal(item: { prix_piece?: number, prix_prest?: number, prix_charge?: number, tva: number, pivot: { qte_f?: number, qte_fpr?: number, qte_fc?: number } }) {
    let price = 0;
    let quantity = 0;

    if (item.prix_piece !== undefined && item.pivot.qte_f !== undefined) {
        price = item.prix_piece;
        quantity = item.pivot.qte_f;
    } else if (item.prix_prest !== undefined && item.pivot.qte_fpr !== undefined) {
        price = item.prix_prest;
        quantity = item.pivot.qte_fpr;
    } else if (item.prix_charge !== undefined && item.pivot.qte_fc !== undefined) {
        price = item.prix_charge;
        quantity = item.pivot.qte_fc;
    }

    const subtotal = price * quantity;
    return subtotal * (1 + (item.tva / 100));
}


// Calculate total amount for a facture including droit_timbre, pieces, prestations, and charges
const calculateMontant = (facture: typeof props.factures[0]) => {
    const totalPieces = facture.pieces.reduce((total, piece) => {
        const subtotal = piece.prix_piece * piece.pivot.qte_f;
        return total + (subtotal * (1 + (piece.tva / 100)));
    }, 0);

    const totalPrestations = facture.prestations.reduce((total, prestation) => {
        const subtotal = prestation.prix_prest * prestation.pivot.qte_fpr;
        return total + (subtotal * (1 + (prestation.tva / 100)));
    }, 0);

    const totalCharges = facture.charges.reduce((total, charge) => {
        const subtotal = charge.prix_charge * charge.pivot.qte_fc;
        return total + (subtotal * (1 + (charge.tva / 100)));
    }, 0);

    const timbre = facture.droit_timbre ?? 0;
    return totalPieces + totalPrestations + totalCharges + timbre;
}


const filteredFactures = computed(() => {
    if (!searchQuery.value) return props.factures

    const query = searchQuery.value.toLowerCase()
    return props.factures.filter(facture =>
        String(facture.n_facture).toLowerCase().includes(query) ||
        String(calculateMontant(facture)).toLowerCase().includes(query) || // Use calculated montant for search
        facture.date_facture.toLowerCase().includes(query) ||
        facture.fournisseur?.nom_fourn?.toLowerCase().includes(query) ||
        facture.pieces?.some(piece => piece.nom_piece.toLowerCase().includes(query)) ||
        facture.prestations?.some(prestation => prestation.nom_prest.toLowerCase().includes(query)) ||
        facture.charges?.some(charge => charge.nom_charge.toLowerCase().includes(query))
    )
})
</script>

<template>
    <Head :title="`Factures pour DRA ${dra.n_dra}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-start m-5 mb-0 gap-4 flex-wrap items-center">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par N° Facture, montant, date, fournisseur, pièce, prestation ou charge..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Factures pour DRA {{ dra.n_dra }}
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead>N° Facture</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Fournisseur</TableHead>
                        <TableHead>Libellé</TableHead>
                        <TableHead>Montant Total</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="filteredFactures.length > 0">
                        <template v-for="facture in filteredFactures" :key="facture.n_facture">
                            <TableRow
                                class="hover:bg-gray-300 dark:hover:bg-gray-900 cursor-pointer"
                                @click="toggleDetails(facture.n_facture)"
                            >
                                <TableCell>{{ facture.n_facture }}</TableCell>
                                <TableCell>{{ new Date(facture.date_facture).toLocaleDateString() }}</TableCell>
                                <TableCell>{{ facture.fournisseur.nom_fourn }}</TableCell>
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
                                <TableCell>{{ calculateMontant(facture).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} DA</TableCell>
                            </TableRow>
                        </template>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell colspan="5" class="text-center py-4 text-gray-500">
                                Aucune facture trouvée.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>

            <div class="mt-4 px-5 pb-5">
                <Link
                    :href="route('scf.dras.show', { dra: props.dra.n_dra })"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                >
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Retour à la DRA
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
