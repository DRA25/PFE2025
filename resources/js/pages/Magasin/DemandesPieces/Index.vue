<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { Pencil, Trash2, ArrowUpDown, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    demandes: {
        id_dp: number;
        date_dp: string;
        etat_dp: string;
        id_piece: number;
        qte_demandep: number;
        id_magasin: number;
        id_atelier: number;
        piece: {
            nom_piece: string;
        };
        magasin?: {
            adresse_magasin: string;
        };
        atelier?: {
            adresse_atelier: string;
        };
    }[];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const isServiceAtelier = computed(() =>
    user.value?.roles?.some((role: any) => role.name === 'service atelier' || role.name === 'admin')
);

const isServiceMagasin = computed(() =>
    user.value?.roles?.some((role: any) => role.name === 'service magasin' || role.name === 'admin')
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: '/magasin' },
    { title: 'Demandes de Pièces', href: route('magasin.demandes-pieces.index') }
];

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);
const selectedEtat = ref<string | null>(null);

const etatOptions = [
    'En attente',
    'Livrée',
    'Validée',
    'Refusée',
];

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
};

const sortedDemandes = computed(() => {
    let data = [...(page.props.demandes as any)];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(d =>
            d.etat_dp.toLowerCase().includes(query) ||
            d.piece?.nom_piece.toLowerCase().includes(query) ||
            d.qte_demandep.toString().includes(query) ||
            d.magasin?.adresse_magasin?.toLowerCase().includes(query) ||
            d.atelier?.adresse_atelier?.toLowerCase().includes(query)
        );
    }

    if (selectedEtat.value) {
        data = data.filter(d => d.etat_dp === selectedEtat.value);
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA = a[column] ?? '';
            let valB = b[column] ?? '';

            if (column === 'piece') {
                valA = a.piece?.nom_piece ?? '';
                valB = b.piece?.nom_piece ?? '';
            } else if (column === 'magasin') {
                valA = a.magasin?.adresse_magasin ?? '';
                valB = b.magasin?.adresse_magasin ?? '';
            } else if (column === 'atelier') {
                valA = a.atelier?.adresse_atelier ?? '';
                valB = b.atelier?.adresse_atelier ?? '';
            }

            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});

function deleteDemande(id_dp: number) {
    if (confirm('Voulez-vous vraiment supprimer cette demande ?')) {
        router.delete(route('magasin.demandes-pieces.destroy', id_dp), {
            onSuccess: () => {},
            onError: () => alert('Erreur lors de la suppression'),
        });
    }
}
</script>

<template>
    <Head title="Liste des Demandes de Pièces" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher par état, pièce, atelier ou magasin..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

            <Link
                :href="route('magasin.demandes-pieces.create')"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Créer une Demande
            </Link>
        </div>

        <!-- État Filter Tags -->
        <div class="flex gap-2 flex-wrap m-5">
            <button
                v-for="etat in etatOptions"
                :key="etat"
                @click="selectedEtat = etat"
                :class="[
                    'px-3 py-1 rounded-full border text-sm',
                    selectedEtat === etat
                        ? 'bg-blue-600 text-white border-blue-700'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:text-white'
                ]"
            >
                {{ etat }}
            </button>
            <button
                v-if="selectedEtat"
                @click="selectedEtat = null"
                class="px-3 py-1 rounded-full border bg-red-100 text-red-700 border-red-300 hover:bg-red-200 dark:bg-red-800 dark:text-white"
            >
                Réinitialiser le filtre
            </button>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Demandes de Pièces
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('id_dp')">
                            ID <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_dp')">
                            Date <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('etat_dp')">
                            État <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('piece')">
                            Nom Pièce <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('qte_demandep')">
                            Quantité <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead v-if="isServiceMagasin" class="cursor-pointer" @click="requestSort('magasin')">
                            Adresse Magasin <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead v-if="isServiceAtelier" class="cursor-pointer" @click="requestSort('atelier')">
                            Adresse Atelier <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="demande in sortedDemandes" :key="demande.id_dp">
                        <TableCell>{{ demande.id_dp }}</TableCell>
                        <TableCell>{{ demande.date_dp }}</TableCell>
                        <TableCell>{{ demande.etat_dp }}</TableCell>
                        <TableCell>{{ demande.piece?.nom_piece }}</TableCell>
                        <TableCell>{{ demande.qte_demandep }}</TableCell>
                        <TableCell v-if="isServiceMagasin">{{ demande.magasin?.adresse_magasin || 'N/A' }}</TableCell>
                        <TableCell v-if="isServiceAtelier">{{ demande.atelier?.adresse_atelier || 'N/A' }}</TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="route('magasin.demandes-pieces.edit', demande.id_dp)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>
                            <button
                                @click.stop="deleteDemande(demande.id_dp)"
                                class="bg-red-800 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition flex items-center gap-1"
                            >
                                <Trash2 class="w-4 h-4" />
                                <span>Supprimer</span>
                            </button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
