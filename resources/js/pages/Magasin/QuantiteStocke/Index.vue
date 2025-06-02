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
} from '@/components/ui/table';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Magasin', href: route('magasin.index') },
    { title: 'Quantités Stockées', href: route('magasin.quantites.index') },
];

const props = defineProps<{
    quantites: Array<{
        id_magasin: number,
        id_piece: number,
        qte_stocke: number,
        magasin?: {
            id_magasin: number,
            adresse_magasin: string,
        },
        piece?: {
            id_piece: number,
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

const deleteQuantite = (id_magasin: number, id_piece: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette quantité stockée ?')) {
        router.delete(route('magasin.quantites.destroy', {
            id_magasin: id_magasin,
            id_piece: id_piece
        }), {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (errors) => {
                alert('Erreur lors de la suppression: ' + (errors.message || 'Une erreur est survenue'));
            }
        });
    }
};
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

            <Link
                :href="route('magasin.quantites.create')"
                as="button"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Ajouter une quantité</span>
            </Link>
        </div>

        <div v-if="props.success" class="mx-5 mt-4 p-3 bg-green-100 text-green-800 rounded-lg dark:bg-green-900 dark:text-green-200">
            {{ props.success }}
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
                        <TableHead>Actions</TableHead>
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
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="route('magasin.quantites.edit', {
                                    id_magasin: q.id_magasin,
                                    id_piece: q.id_piece
                                })"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>
                            <button
                                @click="deleteQuantite(q.id_magasin, q.id_piece)"
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
