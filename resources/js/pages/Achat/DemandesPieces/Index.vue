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
import { Eye, ArrowUpDown } from 'lucide-vue-next';
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
            centre?: { id_centre: number };
        };
        atelier?: {
            adresse_atelier: string;
            centre?: { id_centre: number };
        };
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: route('achat.index') },
    { title: 'Demandes de Pièces', href: route('achat.demandes-pieces.index') }
];

const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const sortedDemandes = computed(() => {
    const demandes = [...props.demandes]; // Create a copy to avoid mutating the original props

    if (!sortConfig.value) {
        return demandes;
    }

    const { column, direction } = sortConfig.value;
    return demandes.sort((a, b) => {
        const valueA = a[column as keyof typeof a];
        const valueB = b[column as keyof typeof b];

        if (column === 'date_dp') {
            const dateA = new Date(valueA.replace(/(\d{2})\/(\d{2})\/(\d{4})/, '$3-$2-$1')).getTime();
            const dateB = new Date(valueB.replace(/(\d{2})\/(\d{2})\/(\d{4})/, '$3-$2-$1')).getTime();
            return direction === 'asc' ? dateA - dateB : dateB - dateA;
        } else if (column === 'id_dp' || column === 'qte_demandep') {
            return direction === 'asc'
                ? Number(valueA) - Number(valueB)
                : Number(valueB) - Number(valueA);
        } else if (column === 'etat_dp') {
            return direction === 'asc'
                ? String(valueA).localeCompare(String(valueB))
                : String(valueB).localeCompare(String(valueA));
        }
        else {
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
</script>

<template>
    <Head title="Demandes de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5">
            <a :href="route('achat.demandes-pieces.export-pdf')"
               class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-500 transition">
                Exporter la liste PDF
            </a>
        </div>
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Demandes de Pièces
                </h1>
            </div>


            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead
                            class="text-[#042B62FF] dark:text-[#BDBDBDFF] cursor-pointer"
                            @click="requestSort('id_dp')"
                        >
                            ID
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead
                            class="text-[#042B62FF] dark:text-[#BDBDBDFF] cursor-pointer"
                            @click="requestSort('date_dp')"
                        >
                            Date
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead
                            class="text-[#042B62FF] dark:text-[#BDBDBDFF] cursor-pointer"
                            @click="requestSort('etat_dp')"
                        >
                            État
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead
                            class="text-[#042B62FF] dark:text-[#BDBDBDFF] cursor-pointer"
                            @click="requestSort('qte_demandep')"
                        >
                            Quantité
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
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
                                demande.magasin?.centre?.id_centre
                                || demande.atelier?.centre?.id_centre
                                || 'N/A'
                            }}
                        </TableCell>
                        <TableCell>
                            <Link
                                :href="route('achat.demandes-pieces.show', { demande_piece: demande.id_dp })"
                                class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-400 transition"
                            >
        <span class="inline-flex items-center space-x-1">
          <span>Voir</span>
          <Eye class="w-4 h-4" />
        </span>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>

            </Table>
        </div>
    </AppLayout>
</template>
