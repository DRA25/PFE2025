<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table'
import { type BreadcrumbItem } from '@/types'
import { Pencil, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    dra: Object,
    bonAchats: Array,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: `Bons d'achat pour DRA ${props.dra.n_dra}`, href: `/dras/${props.dra.n_dra}/bon-achats` },
]
</script>

<template>
    <Head title="Bons d'achat pour DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="`/dras/${props.dra.n_dra}/bon-achats/create`"
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
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">ID Bon Achat</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Montant</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Date</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Fournisseur</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="bonAchat in props.bonAchats"
                        :key="bonAchat.n_ba"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ bonAchat.n_ba }}</TableCell>
                        <TableCell>{{ bonAchat.montant_ba }}</TableCell>
                        <TableCell>{{ bonAchat.date_ba }}</TableCell>
                        <TableCell>{{ bonAchat.id_fourn }}</TableCell>
                        <TableCell>
                            <Link
                                :href="`/dras/${props.dra.n_dra}/bon-achats/${bonAchat.n_ba}/edit`"
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
                    href="/dras"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la liste des DRAs</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
