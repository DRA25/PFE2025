<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Eye, ArrowUpDown, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    demandes: Array<{
        id_dp: number;
        date_dp: string;
        etat_dp: string;
        qte_demandep: number;
        piece?: { nom_piece: string };
        magasin?: {
            adresse_magasin: string;
            centre?: { id_centre: number; nom_centre?: string };
        };
        atelier?: {
            adresse_atelier: string;
            centre?: { id_centre: number; nom_centre?: string };
        };
    }>;
    etatOptions: string[]; // Re-added prop for etat_dp options
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: route('magasin.index') },
    { title: 'Mes Demandes de Pièces', href: route('magasin.mes-demandes.index') }
];

const selectedEtat = ref<string | null>(null); // Re-added ref for selected state
const searchQuery = ref('');

const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const filteredDemandes = computed(() => {
    let data = props.demandes;

    // Filter by selected state first
    if (selectedEtat.value) {
        data = data.filter(d => d.etat_dp === selectedEtat.value);
    }

    // Then filter by search query (search multiple fields)
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(d =>
            d.etat_dp.toLowerCase().includes(query) ||
            d.piece?.nom_piece?.toLowerCase().includes(query) ||
            d.qte_demandep.toString().includes(query) ||
            d.magasin?.adresse_magasin?.toLowerCase().includes(query) ||
            d.atelier?.adresse_atelier?.toLowerCase().includes(query)
        );
    }
    return data;
});

const sortedDemandes = computed(() => {
    const demandes = [...filteredDemandes.value];

    if (!sortConfig.value) return demandes;

    const { column, direction } = sortConfig.value;
    return demandes.sort((a, b) => {
        const valueA = a[column as keyof typeof a];
        const valueB = b[column as keyof typeof b];

        // Assuming date_dp is in 'YYYY-MM-DD' format from backend, no need for replace
        if (column === 'date_dp') {
            const dateA = new Date(valueA).getTime();
            const dateB = new Date(valueB).getTime();
            return direction === 'asc' ? dateA - dateB : dateB - dateA;
        } else if (column === 'id_dp' || column === 'qte_demandep') {
            return direction === 'asc'
                ? Number(valueA) - Number(valueB)
                : Number(valueB) - Number(valueA);
        } else {
            const stringA = String(valueA).toLowerCase();
            const stringB = String(valueB).toLowerCase();
            return direction === 'asc' ? stringA.localeCompare(stringB) : stringB.localeCompare(stringA);
        }
    });
});

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction =
            sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
};

const exportUrl = computed(() => {
    return selectedEtat.value
        ? route('magasin.mes-demandes.export-list', { etat: selectedEtat.value })
        : route('magasin.mes-demandes.export-list');
});
</script>

<template>
    <Head title="Mes Demandes de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-wrap justify-between items-center m-5 mb-0 gap-4">
            <!-- Search input with icon -->
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher par état, pièce, atelier ou magasin..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

            <!-- Export button -->
            <a
                :href="exportUrl"
                class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-500 transition"
            >
                Exporter la liste PDF
            </a>
        </div>

        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Mes Demandes de Pièces
                </h1>
            </div>



            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead @click="requestSort('id_dp')" class="cursor-pointer text-[#042B62FF] dark:text-[#BDBDBDFF]">
                            ID <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead @click="requestSort('date_dp')" class="cursor-pointer text-[#042B62FF] dark:text-[#BDBDBDFF]">
                            Date <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead @click="requestSort('etat_dp')" class="cursor-pointer text-[#042B62FF] dark:text-[#BDBDBDFF]">
                            État <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead @click="requestSort('qte_demandep')" class="cursor-pointer text-[#042B62FF] dark:text-[#BDBDBDFF]">
                            Quantité <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Pièce</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Origine</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Centre</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="demande in sortedDemandes"
                        :key="demande.id_dp"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ demande.id_dp }}</TableCell>
                        <TableCell>{{ demande.date_dp }}</TableCell>
                        <TableCell>{{ demande.etat_dp }}</TableCell>
                        <TableCell>{{ demande.qte_demandep }}</TableCell>
                        <TableCell>{{ demande.piece?.nom_piece || 'N/A' }}</TableCell>
                        <TableCell>
                            {{
                                demande.magasin
                                    ? `Magasin - ${demande.magasin.adresse_magasin}`
                                    : demande.atelier
                                        ? `Atelier - ${demande.atelier.adresse_atelier}`
                                        : 'N/A'
                            }}
                        </TableCell>
                        <TableCell>
                            {{
                                demande.magasin?.centre?.nom_centre
                                || demande.magasin?.centre?.id_centre
                                || demande.atelier?.centre?.nom_centre
                                || demande.atelier?.centre?.id_centre
                                || 'N/A'
                            }}
                        </TableCell>
                        <TableCell>
                            <Link
                                :href="route('magasin.mes-demandes.show', { demande_piece: demande.id_dp })"
                                class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-400 transition"
                            >
                                <span class="inline-flex items-center space-x-1">
                                    <span>Voir</span>
                                    <Eye class="w-4 h-4" />
                                </span>
                            </Link>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="sortedDemandes.length === 0">
                        <TableCell colspan="8" class="text-center py-4 text-gray-500 dark:text-gray-400">
                            Aucune demande de pièce trouvée.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
