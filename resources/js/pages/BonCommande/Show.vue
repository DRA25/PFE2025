<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, FileText, Download } from 'lucide-vue-next'
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table'
import { type BreadcrumbItem } from '@/types'
import { computed } from 'vue'

// If route() is not defined in TS context, declare it (replace with your route helper import if needed)
declare function route(name: string, params?: any): string

const props = defineProps<{
    boncommande: {
        n_bc: string
        date_bc: string
        pieces: Array<{
            id_piece: number
            nom_piece: string
            qte_commandep: number
            prix_piece: number // Assuming price is available for total calculation
            tva: number // Assuming TVA is available for total calculation
        }>
        prestations: Array<{ // Add prestations to the boncommande prop
            id_prest: number
            nom_prest: string
            qte_commandepr: number
            prix_prest: number // Assuming price is available for total calculation
            tva: number // Assuming TVA is available for total calculation
        }>
    }
}>()

// Make breadcrumbs reactive because it depends on props
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Centre', href: '/scentre' },
    { title: 'Gestion des Bons de Commande', href: route('scentre.boncommandes.index') },
    { title: `Bon n° ${props.boncommande.n_bc}`, href: '#' },
])

// Calculate total amount for display
const totalAmount = computed(() => {
    let total = 0;

    // Calculate total for pieces
    if (props.boncommande.pieces) {
        total += props.boncommande.pieces.reduce((subTotal, piece) => {
            const itemSubtotal = piece.prix_piece * piece.qte_commandep;
            const itemTotalWithTva = itemSubtotal * (1 + (piece.tva / 100));
            return subTotal + itemTotalWithTva;
        }, 0);
    }


    // Calculate total for prestations
    if (props.boncommande.prestations) {
        total += props.boncommande.prestations.reduce((subTotal, prestation) => {
            const itemSubtotal = prestation.prix_prest * prestation.qte_commandepr;
            const itemTotalWithTva = itemSubtotal * (1 + (prestation.tva / 100));
            return subTotal + itemTotalWithTva;
        }, 0);
    }

    return total;
})
</script>

<template>
    <Head :title="`Bon de Commande n° ${boncommande.n_bc}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-[#042B62FF] dark:text-[#F3B21B]">
                    Bon de Commande n° {{ boncommande.n_bc }}
                </h1>
                <a
                    :href="route('scentre.boncommandes.export-pdf', { n_bc: boncommande.n_bc })"
                    target="_blank"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition inline-flex items-center space-x-2"
                >
                    <Download class="w-4 h-4" />
                    <span>Exporter en PDF</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B]">
                        Informations sur le Bon de Commande
                    </h2>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Numéro BC</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ boncommande.n_bc }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date du Bon de Commande</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ new Date(boncommande.date_bc).toLocaleDateString() }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nombre de Pièces Commandées</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ boncommande.pieces.length }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nombre de Prestations Commandées</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ boncommande.prestations.length }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Pièces Commandées</h2>
                <div v-if="boncommande.pieces.length > 0" class="space-y-2">
                    <Table class="m-3 w-39/40 bg-white dark:bg-[#111827] rounded-lg">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nom de la Pièce</TableHead>
                                <TableHead>Quantité Commandée</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="piece in boncommande.pieces"
                                :key="piece.id_piece"
                                class="hover:bg-gray-300 dark:hover:bg-gray-900"
                            >
                                <TableCell>{{ piece.nom_piece }}</TableCell>
                                <TableCell>{{ piece.qte_commandep }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucune pièce n'est commandée pour ce bon.</p>
            </div>

            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Prestations Commandées</h2>
                <div v-if="boncommande.prestations.length > 0" class="space-y-2">
                    <Table class="m-3 w-39/40 bg-white dark:bg-[#111827] rounded-lg">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nom de la Prestation</TableHead>
                                <TableHead>Quantité Commandée</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="prestation in boncommande.prestations"
                                :key="prestation.id_prest"
                                class="hover:bg-gray-300 dark:hover:bg-gray-900"
                            >
                                <TableCell>{{ prestation.nom_prest }}</TableCell>
                                <TableCell>{{ prestation.qte_commandepr }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucune prestation n'est commandée pour ce bon.</p>
            </div>



            <div class="mt-10">
                <Link
                    :href="route('scentre.boncommandes.index')"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la liste des Bons de Commande</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
