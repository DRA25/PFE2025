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
import { Plus, Lock, FileText, Trash2, ArrowUpDown, Search } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

const props = defineProps<{
    dras: Array<{
        n_dra: string;
        id_centre: string;
        date_creation: string;
        etat: string;
        total_dra: number;
        created_at: string;
        centre: { seuil_centre: number };
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: '/achat' },
    { title: 'Gestion des DRAs', href: '/achat/dras' },
    { title: 'Liste des DRAs', href: '/achat/dras' },
]

const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);
const searchQuery = ref('');

const sortedDrasComputed = computed(() => {
    let dras = [...props.dras];
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        dras = dras.filter(dra =>
            dra.n_dra.toLowerCase().includes(query) ||
            dra.etat.toLowerCase().includes(query) ||
            new Date(dra.date_creation).toLocaleDateString().includes(query) ||
            dra.centre.seuil_centre.toString().includes(query)
        );
    }
    if (!sortConfig.value) {
        return dras;
    }

    const { column, direction } = sortConfig.value;
    return dras.sort((a, b) => {
        const valueA = a[column as keyof typeof a];
        const valueB = b[column as keyof typeof b];

        if (column === 'date_creation' || column === 'created_at') {
            const dateA = new Date(valueA).getTime();
            const dateB = new Date(valueB).getTime();
            return direction === 'asc' ? dateA - dateB : dateB - dateA;
        } else if (column === 'total_dra' || column === 'id_centre' || column === 'centre.seuil_centre') {
            return direction === 'asc' ? Number(valueA) - Number(valueB) : Number(valueB) - Number(valueA);
        } else if (column === 'etat') {
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

const localDras = ref([...sortedDrasComputed.value]);


watch(
    () => props.dras,
    (newDras) => {
        localDras.value = [...newDras].sort(
            (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        );
    },
    { deep: true }
);

const hasActiveDra = computed(() => localDras.value.some((dra) => dra.etat === 'actif'));

const closeDra = (draId: string) => {
    if (confirm('Êtes-vous sûr de vouloir clôturer ce DRA ?')) {
        const updatedDras = localDras.value.map((dra) =>
            dra.n_dra === draId ? { ...dra, etat: 'cloture' } : dra
        );
        localDras.value = updatedDras;

        router.put(route('achat.dras.close', { dra: draId }), {
            preserveScroll: true,
            onSuccess: () => { },
            onError: (errors) => {
                localDras.value = [...sortedDras.value];
                alert('Erreur lors de la clôture du DRA: ' + (errors.message || 'Une erreur est survenue'));
            },
        });
    }
};

const confirmDeleteDra = (draId: string, etat: string) => {
    if (etat === 'cloture') {
        alert('Vous ne pouvez pas supprimer un DRA clôturé.');
        return; // Stop the deletion process
    }
    if (confirm('Êtes-vous sûr de vouloir supprimer ce DRA ? Cette action est irréversible et supprimera toutes les factures associées.')) {
        router.delete(route('achat.dras.destroy', { dra: draId }));
    }
};



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
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700  dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>
            <Link
                :href="route('achat.dras.create')"
                as="button"
                :class="{
                    'bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 cursor-pointer': !hasActiveDra,
                    'bg-gray-400 dark:bg-gray-600 text-gray-700 dark:text-gray-300 cursor-not-allowed': hasActiveDra
                }"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 ml-auto"
                :disabled="hasActiveDra"
                @click="(e) => hasActiveDra && e.preventDefault()"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un DRA</span>
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
                        <TableHead class="cursor-pointer" @click="requestSort('centre.seuil_centre')">
                            Disponible
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
                        v-for="dra in sortedDrasComputed"
                        :key="dra.n_dra"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ dra.n_dra }}</TableCell>
                        <TableCell>{{ new Date(dra.date_creation).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ dra.total_dra.toLocaleString('fr-FR') }} DA</TableCell>
                        <TableCell>{{ (dra.centre.seuil_centre - dra.total_dra).toLocaleString('fr-FR') }} DA</TableCell>

                        <TableCell>
                            <span
                                class="font-bold"
                                :class="{
                                    'text-green-600': dra.etat === 'actif',
                                    'text-red-600': dra.etat === 'cloture'
                                }"
                            >
                                {{ dra.etat === 'actif' ? 'ACTIF' : 'CLÔTURÉ' }}
                            </span>
                        </TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                v-if="dra.etat === 'actif'"
                                :href="route('achat.dras.factures.index', { dra: dra.n_dra })"
                                class="bg-[#042B62] dark:bg-indigo-500 text-white px-3 py-1 rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-200 transition flex items-center gap-1"
                            >
                                <FileText class="w-4 h-4" />
                                <span>Factures</span>
                            </Link>

                            <Link
                                v-if="dra.etat === 'actif'"
                                :href="route('achat.dras.bon-achats.index', { dra: dra.n_dra })"
                                class="bg-[#042B62] text-white px-3 py-1 rounded-lg hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-200 transition flex items-center gap-1"
                            >
                                <FileText class="w-4 h-4" />
                                <span>Bons d'Achat</span>
                            </Link>

                            <button
                                v-if="dra.etat === 'actif'"
                                @click="closeDra(dra.n_dra)"
                                class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600 transition flex items-center gap-1"
                            >
                                <Lock class="w-4 h-4" />
                                <span>Clôturer</span>
                            </button>

                            <Link
                                v-if="dra.etat === 'actif'"
                                :href="route('achat.dras.destroy', { dra: dra.n_dra })"
                                method="delete"
                                as="button"
                                @click.prevent="confirmDeleteDra(dra.n_dra, dra.etat)"
                                class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-400 transition flex items-center gap-1"
                            >
                                <Trash2 class="w-4 h-4" />
                                <span>Supprimer</span>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
