<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Plus, Trash2, ArrowUpDown, Search } from 'lucide-vue-next';
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
    Table
} from '@/components/ui/table'; // Assuming Table is also exported from here
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: '/magasin' },
    { title: 'Quantités Stockées', href: '/quantites' },
];

const props = defineProps<{
    quantites: Array<{
        id_magasin: number,
        id_piece: number,
        qte_stocke: number,
        magasin?: {
            adresse_magasin: string,
        },
        piece?: {
            nom_piece: string,
        },
    }>,
    success?: string
}>();

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
}

const sortedQuantities = computed(() => {
    let data = [...props.quantites];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(q =>
            (q.magasin?.adresse_magasin ?? '').toLowerCase().includes(query) ||
            (q.piece?.nom_piece ?? '').toLowerCase().includes(query) ||
            String(q.qte_stocke).toLowerCase().includes(query)
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA, valB;
            if (column === 'adresse_magasin') {
                valA = a.magasin?.adresse_magasin ?? '';
                valB = b.magasin?.adresse_magasin ?? '';
            } else if (column === 'nom_piece') {
                valA = a.piece?.nom_piece ?? '';
                valB = b.piece?.nom_piece ?? '';
            } else {
                valA = a[column as keyof typeof a] ?? '';
                valB = b[column as keyof typeof b] ?? '';
            }

            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});

// No delete function for quantities as per the original page.
// If you need to add this functionality, you would uncomment and adapt the deletePiece logic.

</script>

<template>
    <Head title="Quantités Stockées" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par magasin, pièce ou quantité..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

        </div>

        <div v-if="success" class="mx-5 mt-2 p-3 bg-green-100 text-green-800 rounded-lg">
            {{ success }}
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Quantités Stockées
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('adresse_magasin')">
                            Magasin
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('nom_piece')">
                            Pièce
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('qte_stocke')">
                            Quantité
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="q in sortedQuantities"
                        :key="`${q.id_magasin}-${q.id_piece}`"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ q.magasin?.adresse_magasin ?? '—' }}</TableCell>
                        <TableCell>{{ q.piece?.nom_piece ?? '—' }}</TableCell>
                        <TableCell>{{ q.qte_stocke }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
