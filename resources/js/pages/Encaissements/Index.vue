<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
    Table,
} from '@/components/ui/table'
import { Pencil, Plus, Trash2, ArrowUpDown, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: route('scentre.index') },
    { title: 'Gestion des Encaissements', href: '/encaissements' },
]

const props = defineProps<{
    encaissements: Array<{
        id_centre: string,
        n_remb: string,
        centre: { adresse_centre: string, id_centre: string },
        remboursement: { n_remb: string, dra: { n_dra: string } },
        montant_enc: number,
        date_enc: string
    }>,
    userCentre: string
}>()

const searchQuery = ref('')
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null)

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' }
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc'
    }
}

const sortedEncaissements = computed(() => {
    let data = [...props.encaissements]
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        data = data.filter((enc) =>
            enc.centre.adresse_centre.toLowerCase().includes(query) ||
            enc.remboursement.n_remb.toLowerCase().includes(query) ||
            enc.remboursement.dra.n_dra.toLowerCase().includes(query) ||
            String(enc.montant_enc).toLowerCase().includes(query) ||
            enc.date_enc.toLowerCase().includes(query))
    }


    if (sortConfig.value) {
        const { column, direction } = sortConfig.value
        data.sort((a, b) => {
            let valA, valB;
            if (column === 'centre') {
                valA = a.centre.adresse_centre;
                valB = b.centre.adresse_centre;
            } else if (column === 'remboursement') {
                valA = a.remboursement.n_remb;
                valB = b.remboursement.n_remb;
            } else if (column === 'n_dra') {
                valA = a.remboursement.dra.n_dra;
                valB = b.remboursement.dra.n_dra;
            } else {
                valA = a[column as keyof typeof a] ?? '';
                valB = b[column as keyof typeof b] ?? '';
            }

            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA))
        })
    }

    return data
})

const filteredEncaissements = computed(() => {
    return props.encaissements.filter(enc => enc.centre.id_centre === props.userCentre);
})




</script>

<template>
    <Head title="Liste des Encaissements" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher..."
                    class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2 w-full"
                />
            </div>

            <Link
                href="/encaissements/create"
                as="button"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un Encaissement</span>
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Encaissements
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('centre')">
                            Centre
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('remboursement')">
                            Remboursement
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('montant_enc')">
                            Montant
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_enc')">
                            Date
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('n_dra')">
                            N° DRA
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>

                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="encaissement in sortedEncaissements"
                        :key="`${encaissement.id_centre}-${encaissement.n_remb}`"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ encaissement.centre.adresse_centre }}</TableCell>
                        <TableCell>{{ encaissement.remboursement.n_remb }}</TableCell>
                        <TableCell>{{ Number(encaissement.montant_enc?.toLocaleString('fr-FR')).toFixed(2) }} DA</TableCell>
                        <TableCell>{{ encaissement.date_enc }}</TableCell>
                        <TableCell>{{ encaissement.remboursement.dra.n_dra }}</TableCell>

                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
