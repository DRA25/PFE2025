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
        montant: number
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

function calculatePieceTotal(piece: {
    prix_piece: number,
    tva: number,
    pivot: { qte_f: number }
}) {
    const subtotal = piece.prix_piece * piece.pivot.qte_f
    return subtotal * (1 + (piece.tva / 100))
}

const filteredFactures = computed(() => {
    if (!searchQuery.value) return props.factures

    const query = searchQuery.value.toLowerCase()
    return props.factures.filter(facture =>
        String(facture.n_facture).toLowerCase().includes(query) ||
        String(facture.montant).toLowerCase().includes(query) ||
        facture.date_facture.toLowerCase().includes(query) ||
        facture.fournisseur?.nom_fourn?.toLowerCase().includes(query)
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
                    placeholder="Rechercher par N° Facture, montant, date ou fournisseur..."
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
                        <TableHead>Montant Total</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-for="facture in filteredFactures" :key="facture.n_facture">
                        <TableRow
                            class="hover:bg-gray-300 dark:hover:bg-gray-900 cursor-pointer"
                            @click="toggleDetails(facture.n_facture)"
                        >
                            <TableCell>{{ facture.n_facture }}</TableCell>
                            <TableCell>{{ facture.date_facture }}</TableCell>
                            <TableCell>{{ facture.fournisseur.nom_fourn }}</TableCell>
                            <TableCell>{{ facture.montant.toLocaleString('fr-FR') }} DA</TableCell>
                        </TableRow>
                        <TableRow v-if="expandedFacture === facture.n_facture">
                            <TableCell colspan="4" class="px-6 py-4 bg-gray-50 dark:bg-gray-800">
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Détails des pièces</h3>
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pièce</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prix Unitaire</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">TVA</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sous-total</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total avec TVA</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(piece, index) in facture.pieces" :key="index">
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ piece.nom_piece }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ piece.prix_piece.toFixed(2) }} DA
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ piece.tva }}%
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ piece.pivot.qte_f }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ (piece.prix_piece * piece.pivot.qte_f).toFixed(2) }} DA
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ calculatePieceTotal(piece).toFixed(2) }} DA
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
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
