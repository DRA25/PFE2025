<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Plus, Trash2, ArrowUpDown, Search } from 'lucide-vue-next';
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: '/scentre' },
    { title: 'Prestations', href: '/scentre/prestations' },
];

const props = defineProps<{
    prestations: Array<{
        id_prest: number, // Changed from 'id' to 'id_prest' based on your edit page
        nom_prest: string,
        prix_prest: number,
        date_prest: string,
        tva: number, // Added TVA
        compte_general: { code: string; libelle: string } | null, // Updated to object with code and libelle
        compte_analytique: { code: string; libelle: string } | null, // Updated to object with code and libelle

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

const sortedPrestations = computed(() => {
    let data = [...props.prestations];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(prestation =>
            prestation.nom_prest.toLowerCase().includes(query) ||
            String(prestation.prix_prest).toLowerCase().includes(query) ||
            String(prestation.tva).toLowerCase().includes(query) || // Include TVA in search
            (prestation.compte_general?.libelle.toLowerCase().includes(query) ?? false) || // Search by libelle
            (prestation.compte_analytique?.libelle.toLowerCase().includes(query) ?? false) || // Search by libelle
            String(prestation.date_prest).toLowerCase().includes(query) // Search by date
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA: any = a[column as keyof typeof a];
            let valB: any = b[column as keyof typeof b];

            // Handle nested objects for sorting if needed (e.g., compte_general.libelle)
            if (column === 'compte_general') {
                valA = a.compte_general?.libelle ?? '';
                valB = b.compte_general?.libelle ?? '';
            } else if (column === 'compte_analytique') {
                valA = a.compte_analytique?.libelle ?? '';
                valB = b.compte_analytique?.libelle ?? '';
            }

            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});
</script>

<template>
    <Head title="Liste des Prestations" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par nom, prix, TVA, date, compte général ou analytique..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

            <Link
                :href="route('scentre.prestations.create')"
                as="button"
                class="px-4 py-2 rounded-lg transition cursor-pointer flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Ajouter une prestation</span>
            </Link>
        </div>

        <div v-if="success" class="mx-5 mt-2 p-3 bg-green-100 text-green-800 rounded-lg">
            {{ success }}
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Prestations
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('nom_prest')">
                            Nom
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('prix_prest')">
                            Prix
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_prest')">
                            Date
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('tva')">
                            TVA
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('compte_general')">
                            Compte Général
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('compte_analytique')">
                            Compte Analytique
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="prestation in sortedPrestations"
                        :key="prestation.id_prest"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ prestation.nom_prest }}</TableCell>
                        <TableCell>{{ prestation.prix_prest }} DA</TableCell>
                        <TableCell>{{ new Date(prestation.date_prest).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ prestation.tva }} %</TableCell>
                        <TableCell>{{ prestation.compte_general?.code || 'N/A' }}</TableCell>
                        <TableCell>{{ prestation.compte_analytique?.code || 'N/A' }}</TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="route('scentre.prestations.edit', prestation.id_prest)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="sortedPrestations.length === 0">
                        <TableCell colspan="7" class="text-center py-4">
                            No prestations found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
