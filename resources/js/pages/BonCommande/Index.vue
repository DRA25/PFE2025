<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
    Table
} from '@/components/ui/table'
import { type BreadcrumbItem } from '@/types'
import { Pencil, ArrowLeft, ArrowUpDown, Search, Trash2, FileText } from 'lucide-vue-next';
import { ref, computed } from 'vue'

const props = defineProps({
    boncommandes: Array<{
        n_bc: string
        date_bc: string
        pieces: Array<{
            id_piece: number
            nom_piece: string
            qte_commandep: number
        }>,
        prestations: Array<{
            id_prest: number
            nom_prest: string
            qte_commandepr: number
        }>,
        charges: Array<{
            id_charge: number
            nom_charge: string
            qte_commandec: number
        }>,
    }>,
    success: String,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: '/scentre' },
    { title: 'Gestion des Bons de Commande', href: route('scentre.boncommandes.index') },
]

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
}

const sortedBonCommandes = computed(() => {
    let data = [...props.boncommandes];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(bc =>
            String(bc.n_bc).toLowerCase().includes(query) ||
            bc.date_bc.toLowerCase().includes(query) ||
            bc.pieces?.some(piece => piece.nom_piece.toLowerCase().includes(query)) ||
            bc.prestations?.some(prestation => prestation.nom_prest.toLowerCase().includes(query)) ||
            bc.charges?.some(charge => charge.nom_charge.toLowerCase().includes(query))
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA: any, valB: any;

            if (column === 'n_bc' || column === 'date_bc') {
                valA = a[column];
                valB = b[column];
            } else {
                valA = '';
                valB = '';
            }

            if (typeof valA === 'number' && typeof valB === 'number') {
                return direction === 'asc' ? valA - valB : valB - valA;
            }

            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});

const form = useForm({});

const deleteBonCommande = (n_bc: string) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce bon de commande ?')) {
        form.delete(route('scentre.boncommandes.destroy', { n_bc }), {
            onSuccess: () => {},
            onError: (errors) => {
                console.error('Error deleting bon de commande:', errors);
                alert('Une erreur est survenue lors de la suppression.');
            }
        });
    }
};
</script>

<template>
    <Head title="Liste des Bons de Commande" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par numéro, date, pièce, prestation ou charge..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
            <Link
                href="/scentre/boncommandes/create"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                + Créer un Bon de Commande
            </Link>
        </div>

        <div v-if="props.success" class="mx-5 mt-2 p-3 bg-green-100 text-green-800 rounded-lg">
            {{ props.success }}
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Bons de Commande
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('n_bc')">
                            Numéro BC
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_bc')">
                            Date
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Détails (Pièces, Prestations & Charges)</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="sortedBonCommandes.length > 0">
                        <TableRow
                            v-for="bc in sortedBonCommandes"
                            :key="bc.n_bc"
                            class="hover:bg-gray-300 dark:hover:bg-gray-900"
                        >
                            <TableCell>{{ bc.n_bc }}</TableCell>
                            <TableCell>{{ new Date(bc.date_bc).toLocaleDateString() }}</TableCell>
                            <TableCell>
                                <div v-if="bc.pieces && bc.pieces.length > 0" class="mb-2">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">Pièces:</h4>
                                    <div v-for="piece in bc.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} — Qté: {{ piece.qte_commandep }}
                                    </div>
                                </div>
                                <div v-if="bc.prestations && bc.prestations.length > 0" class="mb-2">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">Prestations:</h4>
                                    <div v-for="prestation in bc.prestations" :key="prestation.id_prest" class="text-sm">
                                        {{ prestation.nom_prest }} — Qté: {{ prestation.qte_commandepr }}
                                    </div>
                                </div>
                                <div v-if="bc.charges && bc.charges.length > 0">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">Charges:</h4>
                                    <div v-for="charge in bc.charges" :key="charge.id_charge" class="text-sm">
                                        {{ charge.nom_charge }} — Qté: {{ charge.qte_commandec }}
                                    </div>
                                </div>
                                <div v-if="(!bc.pieces || bc.pieces.length === 0) && (!bc.prestations || bc.prestations.length === 0) && (!bc.charges || bc.charges.length === 0)" class="text-sm text-gray-500 dark:text-gray-400">
                                    Aucune pièce, prestation ou charge
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center space-x-2">
                                    <Link
                                        :href="route('scentre.boncommandes.edit', { n_bc: bc.n_bc })"
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-400 transition flex items-center gap-2"
                                        title="Modifier"
                                    >
                                        <Pencil class="w-4 h-4" />
                                        <span class="hidden sm:inline">Modifier</span>
                                    </Link>

                                    <Link
                                        :href="route('scentre.boncommandes.show', { n_bc: bc.n_bc })"
                                        class="bg-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 transition flex items-center gap-2"
                                        title="Afficher"
                                    >
                                        <FileText class="w-4 h-4" />
                                        <span>Afficher</span>
                                    </Link>


                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell colspan="4" class="text-center py-4 text-gray-500">
                                Aucun bon de commande trouvé.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
