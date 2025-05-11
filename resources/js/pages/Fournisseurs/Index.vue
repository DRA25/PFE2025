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
import { Pencil, Plus, Trash2 } from 'lucide-vue-next'

const props = defineProps<{
    fournisseurs: Array<{
        id_fourn: number,
        nom_fourn: string,
        adress_fourn: string,
        nrc_fourn: string
    }>
}>()

const deleteFournisseur = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')) {
        router.delete(route('fournisseurs.destroy', { fournisseur: id }), {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (errors) => {
                alert('Erreur lors de la suppression: ' + (errors.message || 'Une erreur est survenue'));
            }
        })
    }
}
</script>

<template>
    <Head title="Liste des Fournisseurs" />
    <AppLayout>
        <div class="flex justify-end m-5 mb-0">
            <Link
                href="/fournisseurs/create"
                as="button"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un Fournisseur</span>
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Fournisseurs
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">ID</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Nom</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Adresse</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">NRC</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="fournisseur in fournisseurs"
                        :key="fournisseur.id_fourn"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ fournisseur.id_fourn }}</TableCell>
                        <TableCell>{{ fournisseur.nom_fourn }}</TableCell>
                        <TableCell>{{ fournisseur.adress_fourn }}</TableCell>
                        <TableCell>{{ fournisseur.nrc_fourn }}</TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="`/fournisseurs/${fournisseur.id_fourn}/edit`"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>

                            <button
                                @click="deleteFournisseur(fournisseur.id_fourn)"
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
