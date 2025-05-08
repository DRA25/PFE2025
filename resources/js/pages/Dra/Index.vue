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
import { Pencil } from 'lucide-vue-next'

defineProps<{ dras: Array<{ n_dra: string, date_creation: string, etat: string }> }>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestion des DRAs', href: '/dras' },
    { title: 'Liste des DRAs', href: '/dras' },
]
</script>

<template>
    <Head title="Liste des DRAs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                href="/dras/create"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Créer un DRA
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des DRAs
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">ID</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Date de création</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">État</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="dra in dras"
                        :key="dra.n_dra"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ dra.n_dra }}</TableCell>
                        <TableCell>{{ dra.date_creation }}</TableCell>
                        <TableCell>{{ dra.etat }}</TableCell>
                        <TableCell class="flex space-x-2">
                            <Link
                                :href="`/dras/${dra.n_dra}/edit`"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition"
                            >
                                <span class="inline-flex items-center space-x-1">
                                    <span>Modifier</span>
                                    <Pencil class="w-4 h-4" />
                                </span>
                            </Link>
                            <Link
                                :href="`/dras/${dra.n_dra}/factures`"
                                class="bg-[#042B62] dark:bg-[#F3B21B] text-white px-3 py-1 rounded-lg hover:bg-[#042B40] dark:hover:bg-yellow-400 transition"
                            >
                                <span class="inline-flex items-center space-x-1">
                                    <span>Gérer les factures</span>
                                </span>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
