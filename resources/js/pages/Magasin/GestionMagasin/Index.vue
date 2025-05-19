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
    { title: 'Gestion des Magasins', href: '/gestionmagasin' },
]

const props = defineProps<{
    magasins: Array<{
        id_magasin: number,
        adresse_magasin: string,
        id_centre: string | null,
        centre: {
            id_centre: string,
            adresse_centre: string,
        } | null
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

const sortedMagasins = computed(() => {
    let data = [...props.magasins];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(magasin =>
            magasin.adresse_magasin.toLowerCase().includes(query) ||
            (magasin.centre?.adresse_centre?.toLowerCase().includes(query) ?? false)
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

const deleteMagasin = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce magasin ?')) {
        router.delete(route('gestionmagasin.destroy', { gestionmagasin: id }), {
            preserveScroll: true,
            onError: (errors) => {
                alert('Erreur lors de la suppression: ' + (errors.message || 'Une erreur est survenue'));
            }
        })
    }
}
</script>

<template>
    <Head title="Liste des Magasins" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par adresse ou centre..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

            <Link
                href="/gestionmagasin/create"
                as="button"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un Magasin</span>
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">Liste des Magasins</h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="cursor-pointer" @click="requestSort('id_magasin')">
                            ID Magasin
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('adresse_magasin')">
                            Adresse
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Centre</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="magasin in sortedMagasins"
                        :key="magasin.id_magasin"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ magasin.id_magasin }}</TableCell>
                        <TableCell>{{ magasin.adresse_magasin }}</TableCell>
                        <TableCell>{{ magasin.centre?.adresse_centre || 'N/A' }} ({{ magasin.centre?.id_centre || 'N/A' }})</TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="`/gestionmagasin/${magasin.id_magasin}/edit`"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>

                            <button
                                @click="deleteMagasin(magasin.id_magasin)"
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
