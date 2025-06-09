<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, FileText ,Download} from 'lucide-vue-next' // Added FileText for consistent icon usage
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
        n_bc: string // Use string if your route param is string; change to number if needed
        date_bc: string
        pieces: Array<{
            id_piece: number
            nom_piece: string
            qte_commandep: number
        }>
    }
}>()

// Make breadcrumbs reactive because it depends on props
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Centre', href: '/scentre' },
    { title: 'Gestion des Bons de Commande', href: route('scentre.boncommandes.index') },
    { title: `Bon n° ${props.boncommande.n_bc}`, href: '#' },
])
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
                <!-- Left Column: Bon de Commande Information -->
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
                </div>


            </div>

            <!-- PIÈCES COMMANDÉES Section -->
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

            <!-- Bottom Return Link (Optional, if you want two return buttons) -->
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
