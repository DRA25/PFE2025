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
    factures: Array,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title:'Achat', href: '/achat'},
    { title: 'Gestion des DRAs', href: route('achat.dras.index') },
    { title: `Factures pour DRA ${props.dra.n_dra}`, href: route('achat.dras.factures.index', { dra: props.dra.n_dra }) },
]
</script>

<template>
    <Head title="Factures pour DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="route('achat.dras.factures.create', { dra: props.dra.n_dra })"
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
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">ID Facture</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Montant</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Date</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Fournisseur</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="facture in props.factures"
                        :key="facture.n_facture"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ facture.n_facture }}</TableCell>
                        <TableCell>{{ facture.montant_facture }}</TableCell>
                        <TableCell>{{ facture.date_facture }}</TableCell>
                        <TableCell>{{ facture.fournisseur.nom_fourn }}</TableCell>
                        <TableCell>
                            <Link
                                :href="route('achat.dras.factures.edit', { dra: props.dra.n_dra, facture: facture.n_facture })"
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
                    :href="route('achat.dras.index')"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la liste des DRAs</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
