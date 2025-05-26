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
import { ArrowUpDown, Plus, Search } from 'lucide-vue-next'; // Removed Plus, FileText, Trash2
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
    { title: 'Service Paiment', href: '/paiment' },
    { title: 'Liste des dras non remboursé', href: '/paiment/dras' },
]


const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

// Filter DRAs by search query only, and default to 'accepte' state
const filteredDras = computed(() => {
    let data = props.dras.filter(d => d.etat.toLowerCase() === 'accepte'); // Always filter for 'accepte' state

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(dra =>
            dra.n_dra.toLowerCase().includes(query) ||
            dra.etat.toLowerCase().includes(query) ||
            new Date(dra.date_creation).toLocaleDateString().includes(query) ||
            (dra.centre && dra.centre.seuil_centre.toString().includes(query)) || // Include seuil_centre in search
            dra.total_dra.toString().includes(query) // Include total_dra in search
        );
    }
    return data;
});

// Apply sorting
const sortedDras = computed(() => {
    let dras = [...filteredDras.value];

    // Apply sorting
    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        dras.sort((a, b) => {
            // Handle potential undefined values for nested properties
            const valueA = (column === 'centre.seuil_centre' && a.centre) ? a.centre.seuil_centre : a[column as keyof Dra];
            const valueB = (column === 'centre.seuil_centre' && b.centre) ? b.centre.seuil_centre : b[column as keyof Dra];


            if (column === 'date_creation' || column === 'created_at') {
                const dateA = new Date(valueA as string).getTime();
                const dateB = new Date(valueB as string).getTime();
                return direction === 'asc' ? dateA - dateB : dateB - dateA;
            } else if (column === 'total_dra' || column === 'id_centre' || column === 'centre.seuil_centre') {
                return direction === 'asc'
                    ? Number(valueA) - Number(valueB)
                    : Number(valueB) - Number(valueA);
            } else if (column === 'etat') {
                return direction === 'asc'
                    ? String(valueA).localeCompare(String(valueB))
                    : String(valueB).localeCompare(String(valueA));
            } else {
                // Default string comparison for other columns
                const stringA = String(valueA).toLowerCase();
                const stringB = String(valueB).toLowerCase();
                return direction === 'asc' ? stringA.localeCompare(stringB) : stringB.localeCompare(stringA);
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

        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
            <Link
                href="/paiment/remboursements/create"
                as="button"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un Remboursement</span>
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">Liste des DRAs</h1>
            </div>



            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
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
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="dra in sortedDras"
                        :key="dra.n_dra"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ dra.n_dra }}</TableCell>
                        <TableCell>{{ new Date(dra.date_creation).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ dra.total_dra.toLocaleString('fr-FR') }} DA</TableCell>
                        <TableCell>
                            <span
                                class="font-bold"
                                :class="{
                                    'text-green-600': dra.etat === 'accepte',
                                }"
                            >
                                {{ dra.etat === 'accepte' ? 'ACCEPTÉ' :'' }}
                            </span>
                        </TableCell>

                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
