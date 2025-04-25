<script setup lang="ts">
import { defineProps } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableCaption, TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Trash,Pencil } from 'lucide-vue-next';

defineProps<{
    dras: {
        n_dra: string;
        periode: string;
        etat: string;
        cmp_gen: number;
        cmp_ana: number;
        debit: number;
        libelle_dra: string;
        fourn_dra: string;
    }[]
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'DRA',
        href: '/dra',
    },
];

function deleteDra(n_dra: string) {
    if (confirm('Are you sure you want to delete this DRA?')) {
        // Call delete route (replace with actual delete endpoint)
        // Example route: route('dra.destroy', { id: n_dra })
        axios.delete(`/dra/${n_dra}`).then(() => {
            // Optionally, you can update the table data here or refresh the page
            alert('DRA deleted successfully');
        }).catch((error) => {
            console.error(error);
            alert('An error occurred while deleting the DRA');
        });
    }
}
</script>

<template>
    <Head title="DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex justify-end m-5 mb-0">
            <Link
            href="/dra/create"
            class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
        >
            Créer une DRA
        </Link>
        </div>
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">

            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left  text-[#042B62FF] dark:text-[#BDBDBDFF] ">Liste Des DRAs</h1>

            </div>
            <Table class="m-3 w-39/40">

                <TableHeader>
                    <TableRow>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Numéro DRA</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Période</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">État</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Compte Général</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Compte Analytique</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Débit</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Libellé</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Fournisseur</TableHead>

                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="dra in dras"
                        :key="dra.n_dra"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900 cursor-pointer"
                        @click="$inertia.visit(`/dra/${dra.n_dra}/edit`)"
                    >


                        <TableCell>{{ dra.n_dra }}</TableCell>
                        <TableCell>{{ new Date(dra.periode).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ dra.etat }}</TableCell>
                        <TableCell>{{ dra.cmp_gen }}</TableCell>
                        <TableCell>{{ dra.cmp_ana }}</TableCell>
                        <TableCell>{{ dra.debit.toFixed(2) }}</TableCell>
                        <TableCell>{{ dra.libelle_dra }}</TableCell>
                        <TableCell>{{ dra.fourn_dra }}</TableCell>
                        <TableCell class="flex space-x-2">

                            <Link
                                :href="`/dra/${dra.n_dra}/edit`"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition"
                            >
                               <span class="inline-flex items-center space-x-1">
                                          <span>Modifier</span>
                                          <Pencil class="w-4 h-4" />
                               </span>
                            </Link>
                            <button
                                @click="deleteDra(dra.n_dra)"
                                class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-400 transition"
                            >

                                <span class="inline-flex items-center space-x-1">
                                     <span>Supprimer</span>
                                    <Trash class="w-4 h-4" />
                                </span>
                            </button>
                        </TableCell>
                    </TableRow>

                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
