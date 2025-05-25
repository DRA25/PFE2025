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
    bonAchats: Array<{
        n_ba: string,
        date_ba: string,
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
                qte_ba: number
            }
        }>,
        montant: number // Calculated from controller
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Coordination Financière', href: '/scf' },
    { title: 'Gestion des DRAs', href: route('scf.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('scf.dras.show', { dra: props.dra.n_dra }) },
    { title: `Bons d'achat pour DRA ${props.dra.n_dra}`, href: route('scf.dras.bon-achats.show', { dra: props.dra.n_dra }) }
]

const expandedBonAchat = ref<string | null>(null)
const searchQuery = ref('');

function toggleDetails(n_ba: string) {
    expandedBonAchat.value = expandedBonAchat.value === n_ba ? null : n_ba
}

function calculatePieceTotal(piece: {
    prix_piece: number,
    tva: number,
    pivot: { qte_ba: number }
}) {
    const subtotal = piece.prix_piece * piece.pivot.qte_ba
    return subtotal * (1 + (piece.tva / 100))
}

const filteredBonAchats = computed(() => {
    if (!searchQuery.value) return props.bonAchats;

    const query = searchQuery.value.toLowerCase();
    return props.bonAchats.filter(bonAchat =>
        String(bonAchat.n_ba).toLowerCase().includes(query) ||
        String(bonAchat.montant).toLowerCase().includes(query) ||
        bonAchat.date_ba.toLowerCase().includes(query) ||
        bonAchat.fournisseur?.nom_fourn?.toLowerCase().includes(query)
    );
});
</script>

<template>
    <Head :title="`Bons d'achat pour DRA ${dra.n_dra}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-start m-5 mb-0 gap-4 flex-wrap items-center">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par N° Bon d'achat, montant, date ou fournisseur..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Bons d'achat pour DRA {{ dra.n_dra }}
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead>N° Bon d'achat</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Fournisseur</TableHead>
                        <TableHead>Montant Total</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-for="bonAchat in filteredBonAchats" :key="bonAchat.n_ba">
                        <TableRow
                            class="hover:bg-gray-300 dark:hover:bg-gray-900 cursor-pointer"
                            @click="toggleDetails(bonAchat.n_ba)"
                        >
                            <TableCell>{{ bonAchat.n_ba }}</TableCell>
                            <TableCell>{{ bonAchat.date_ba }}</TableCell>
                            <TableCell>{{ bonAchat.fournisseur.nom_fourn }}</TableCell>
                            <TableCell>{{ bonAchat.montant.toLocaleString('fr-FR') }} DA</TableCell>
                        </TableRow>
                        <TableRow v-if="expandedBonAchat === bonAchat.n_ba">
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
                                        <tr v-for="(piece, index) in bonAchat.pieces" :key="index">
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
                                                {{ piece.pivot.qte_ba }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ (piece.prix_piece * piece.pivot.qte_ba).toFixed(2) }} DA
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
