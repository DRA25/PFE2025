<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table'
import { Pencil, Plus, Trash2, ArrowUpDown, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Paiment', href: '/paiment' },
    { title: 'Gestion des Remboursements', href: '/paiment/remboursements' },
]

const props = defineProps<{
    remboursements: Array<{
        n_remb: number,
        date_remb: string,
        method_remb: string,
        n_dra: string,
        id_centre: string,
        seuil_centre: number,
        total_dra: number,
        montant_rembourse: number,
        etat: string
    }>
}>()

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
}

const sortedRemboursements = computed(() => {
    let data = [...props.remboursements];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(remb =>
            String(remb.n_remb).includes(query) ||
            remb.date_remb.toLowerCase().includes(query) ||
            remb.method_remb.toLowerCase().includes(query) ||
            remb.n_dra.toLowerCase().includes(query) ||
            remb.id_centre.toLowerCase().includes(query)
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            const valA = a[column as keyof typeof a] ?? '';
            const valB = b[column as keyof typeof b] ?? '';
            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});

const deleteRemboursement = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce remboursement ?')) {
        router.delete(route('paiment.remboursements.destroy', { remboursement: id }), {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (errors) => {
                alert('Erreur lors de la suppression: ' + (errors.message || 'Une erreur est survenue'));
            }
        });
    }
}
</script>

<template>
    <Head title="Liste des Remboursements" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par numéro, date, méthode ou DRA..."
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
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Remboursements
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('n_remb')">
                            N° Remb.
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('date_remb')">
                            Date
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('method_remb')">
                            Méthode
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('n_dra')">
                            N° DRA
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('id_centre')">
                            Centre
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('montant_rembourse')">
                            A Remboursé
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="remb in sortedRemboursements"
                        :key="remb.n_remb"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ remb.n_remb }}</TableCell>
                        <TableCell>{{ remb.date_remb }}</TableCell>
                        <TableCell>{{ remb.method_remb }}</TableCell>
                        <TableCell>{{ remb.n_dra }}</TableCell>
                        <TableCell>{{ remb.id_centre }}</TableCell>
                        <TableCell>{{ remb.montant_rembourse }}</TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <template v-if="remb.etat !== 'rembourse'">
                                <Link
                                    :href="`/paiment/remboursements/${remb.n_remb}/edit`"
                                    class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                                >
                                    <Pencil class="w-4 h-4" />
                                    <span>Modifier</span>
                                </Link>

                                <button
                                    @click="deleteRemboursement(remb.n_remb)"
                                    class="bg-red-800 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition flex items-center gap-1"
                                >
                                    <Trash2 class="w-4 h-4" />
                                    <span>Supprimer</span>
                                </button>
                            </template>
                        </TableCell>

                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
