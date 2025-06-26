<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
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
import { Plus, FileText, Trash2, ArrowUpDown, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'

interface Dra {
    n_dra: string;
    id_centre: string;
    date_creation: string;
    etat: string;
    total_dra: number;
    created_at: string;
    centre?: {
        seuil_centre: number;
    };
}

const props = defineProps<{
    dras: Dra[];
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: '/achat' },
    { title: 'Consulter les DRAs', href: '/achat/consulterdra' },
]

// State filter options
const etatOptions = ['cloture', 'refuse', 'accepte','rembourse'];
const selectedEtat = ref<string | null>(null);
const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

// Filter DRAs by selected state
const filteredDras = computed(() => {
    let data = props.dras;

    // Apply state filter
    if (selectedEtat.value) {
        data = data.filter(d => d.etat.toLowerCase() === selectedEtat.value);
    }

    // Apply default filter for only certain states if no state is selected
    if (!selectedEtat.value) {
        data = data.filter(d =>
            ['cloture', 'refuse', 'accepte' ,'rembourse'].includes(d.etat.toLowerCase()))
    }

    return data;
});

// Apply search and sorting
const sortedDras = computed(() => {
    let dras = [...filteredDras.value];

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        dras = dras.filter(dra =>
            dra.n_dra.toLowerCase().includes(query) ||
            dra.etat.toLowerCase().includes(query) ||
            new Date(dra.date_creation).toLocaleDateString().includes(query)
        );
    }

    // Apply sorting
    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        dras.sort((a, b) => {
            const valueA = a[column as keyof Dra];
            const valueB = b[column as keyof Dra];

            if (column === 'date_creation' || column === 'created_at') {
                const dateA = new Date(valueA).getTime();
                const dateB = new Date(valueB).getTime();
                return direction === 'asc' ? dateA - dateB : dateB - dateA;
            } else if (column === 'total_dra' || column === 'id_centre') {
                return direction === 'asc'
                    ? Number(valueA) - Number(valueB)
                    : Number(valueB) - Number(valueA);
            } else if (column === 'etat') {
                return direction === 'asc'
                    ? String(valueA).localeCompare(String(valueB))
                    : String(valueB).localeCompare(String(valueA));
            } else {
                return direction === 'asc'
                    ? String(valueA).toLowerCase().localeCompare(String(valueB).toLowerCase())
                    : String(valueB).toLowerCase().localeCompare(String(valueA).toLowerCase());
            }
        });
    }

    return dras;
});



const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction =
            sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
};
</script>

<template>
    <Head title="Liste des DRAs" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex justify-start m-5 mb-0 gap-4 flex-wrap items-center">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

            <a href="/export/etat-trimestriel-all"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2"

            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
                <span> Export Etat Trimestrielle</span>
            </a>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">Liste des DRAs</h1>
            </div>

            <!-- State filter tags -->
            <div class="flex flex-wrap gap-2 px-5 pb-2">
                <button
                    v-for="etat in etatOptions"
                    :key="etat"
                    @click="selectedEtat = selectedEtat === etat ? null : etat"
                    class="px-4 py-1 rounded-full border text-sm font-medium transition"
                    :class="{
                        'bg-blue-600 text-white': selectedEtat === etat,
                        'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200': selectedEtat !== etat
                    }"
                >
                    {{

                            etat === 'cloture' ? 'Clôturé' :
                                etat === 'refuse' ? 'Refusé' :
                                    etat === 'rembourse' ? 'Remboursé' :
                                    'Accepté'
                    }}
                </button>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('n_dra.id_centre')">
                            Centre
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('n_dra')">
                            N°DRA
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_creation')">
                            Date de création
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('total_dra')">
                            Total DRA
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('etat')">
                            État
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="dra in sortedDras"
                        :key="dra.n_dra"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ dra.id_centre }}</TableCell>
                        <TableCell>{{ dra.n_dra }}</TableCell>
                        <TableCell>{{ new Date(dra.date_creation).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ Number(parseFloat(dra.total_dra).toFixed(2)).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} DA</TableCell>
                        <TableCell>
                            <span
                                class="font-bold"
                                :class="{
                                    'text-green-600': dra.etat === 'accepte',
                                    'text-blue-600': dra.etat === 'cloture',
                                    'text-red-600': dra.etat === 'refuse',
                                     'text-blue-500': dra.etat === 'rembourse'

                                }"
                            >
                                {{
                                    dra.etat === 'accepte' ? 'ACCEPTÉ' :
                                        dra.etat === 'cloture' ? 'CLÔTURÉ' :
                                            dra.etat === 'rembourse' ? 'REMBOURSÉ' :
                                             'REFUSÉ'

                                }}
                            </span>
                        </TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="route('achat.dras.show', { dra: dra.n_dra })"
                                class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition flex items-center gap-1"
                            >
                                <FileText class="w-4 h-4" />
                                <span>Afficher</span>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
