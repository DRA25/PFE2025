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
import { type BreadcrumbItem } from '@/types'
import { Pencil, Plus, Lock, FileText, Trash2 } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

const props = defineProps<{
    dras: Array<{
        n_dra: string,
        id_centre:string,
        date_creation: string,
        etat: string,
        total_dra: number,
        created_at: string
    }>
}>()

const sortedDras = computed(() => {
    return [...props.dras].sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    )
})

// Update breadcrumbs to use new route paths
const breadcrumbs: BreadcrumbItem[] = [
    { title:'Achat', href: '/achat'},
    { title: 'Gestion des DRAs', href: '/achat/dras' },
    { title: 'Liste des DRAs', href: '/achat/dras' },
]

const localDras = ref([...sortedDras.value])

watch(() => props.dras, (newDras) => {
    localDras.value = [...newDras].sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    )
}, { deep: true })

const hasActiveDra = computed(() => localDras.value.some(dra => dra.etat === 'actif'))

const handleCreateClick = (e: Event) => {
    if (hasActiveDra.value) {
        e.preventDefault()
        alert("Vous ne pouvez pas créer un DRA tant qu'il existe un DRA actif. Veuillez clôturer le DRA actif d'abord.")
    }
}

const closeDra = (draId: string) => {
    if (confirm('Êtes-vous sûr de vouloir clôturer ce DRA ?')) {
        const updatedDras = localDras.value.map(dra =>
            dra.n_dra === draId ? { ...dra, etat: 'cloture' } : dra
        )
        localDras.value = updatedDras

        router.put(route('achat.dras.close', { dra: draId }), {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (errors) => {
                localDras.value = [...sortedDras.value]
                alert('Erreur lors de la clôture du DRA: ' + (errors.message || 'Une erreur est survenue'));
            }
        })
    }
}

const deleteDra = (draId: string) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce DRA ? Cette action est irréversible et supprimera toutes les factures associées.')) {
        router.delete(route('achat.dras.destroy', { dra: draId }), {
            preserveScroll: true,
            onSuccess: () => {
                localDras.value = localDras.value.filter(dra => dra.n_dra !== draId);
            },
            onError: (errors) => {
                alert('Erreur lors de la suppression du DRA: ' + (errors.message || 'Une erreur est survenue'));
            }
        })
    }
}

// Method to update dra totals after modification
const updateDraTotal = (draId: string) => {
    router.get(route('achat.dras.index'), {
        preserveScroll: true,
        onSuccess: (updatedDras) => {
            localDras.value = updatedDras;
        },
    });
}
</script>

<template>
    <Head title="Liste des DRAs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="route('achat.dras.create')"
                as="button"
                :class="{
                    'bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 cursor-pointer': !hasActiveDra,
                    'bg-gray-400 dark:bg-gray-600 text-gray-700 dark:text-gray-300 cursor-not-allowed': hasActiveDra
                }"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1"
                :disabled="hasActiveDra"
                @click="(e) => hasActiveDra && e.preventDefault()"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un DRA</span>
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des DRAs
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">ID Centre</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">N DRA</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Date de création</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Total DRA</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">État</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="dra in localDras"
                        :key="dra.n_dra"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ dra.id_centre }}</TableCell>
                        <TableCell>{{ dra.n_dra }}</TableCell>
                        <TableCell>{{ new Date(dra.date_creation).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ dra.total_dra.toLocaleString('fr-FR') }} DA</TableCell>
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
                            <!-- Factures Button -->
                            <Link
                                v-if="dra.etat === 'actif'"
                                :href="route('achat.dras.factures.index', { dra: dra.n_dra })"
                                class="bg-[#042B62] dark:bg-[#F3B21B] text-white px-3 py-1 rounded-lg hover:bg-indigo-600 dark:hover:bg-yellow-400 transition flex items-center gap-1"
                            >
                                <FileText class="w-4 h-4" />
                                <span>Factures</span>
                            </Link>

                            <!-- BonAchats Button -->
                            <Link
                                v-if="dra.etat === 'actif'"
                                :href="route('achat.dras.bon-achats.index', { dra: dra.n_dra })"
                                class="bg-[#042B62] text-white px-3 py-1 rounded-lg hover:bg-indigo-600 dark:bg-[#F3B21B] dark:hover:bg-yellow-400 transition flex items-center gap-1"
                            >
                                <FileText class="w-4 h-4" />
                                <span>Bons d'Achat</span>
                            </Link>

                            <!-- Clôturer Button -->
                            <button
                                v-if="dra.etat === 'actif'"
                                @click="closeDra(dra.n_dra)"
                                class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600 transition flex items-center gap-1"
                            >
                                <Lock class="w-4 h-4" />
                                <span>Clôturer</span>
                            </button>

                            <!-- Modifier Button -->
                            <Link
                                v-if="dra.etat === 'actif'"
                                :href="route('achat.dras.edit', { dra: dra.n_dra })"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>

                            <!-- Supprimer Button -->
                            <button
                                v-if="dra.etat === 'actif'"
                                @click="deleteDra(dra.n_dra)"
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
