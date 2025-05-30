<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    Table, TableHeader, TableBody, TableRow,
    TableCell, TableHead
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
        centre: {
            seuil_centre: number;
            montant_disponible: number; // Keep this in the type definition as it's passed from the controller
        };
    }>,
    id_centre: string
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: '/achat' },
    { title: 'Gestion des DRAs', href: '/achat/dras' },

]

const etatOptions = ['actif', 'cloture', 'refuse', 'accepte','rembourse'];
const selectedEtat = ref<string | null>(null);
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);
const searchQuery = ref('');

const filteredDras = computed(() => {
    let data = selectedEtat.value
        ? props.dras.filter(dra => dra.etat === selectedEtat.value)
        : props.dras;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(dra =>
                dra.n_dra.toLowerCase().includes(query) ||
                dra.etat.toLowerCase().includes(query) ||
                new Date(dra.date_creation).toLocaleDateString().includes(query) ||
                dra.total_dra.toString().includes(query) ||
                dra.centre.seuil_centre.toString().includes(query)
            // Removed montant_disponible from individual row search as it's now a global stat
        );
    }

    return data;
});

const sortedDrasComputed = computed(() => {
    let dras = [...filteredDras.value];

    if (!sortConfig.value) return dras;

    const { column, direction } = sortConfig.value;

    return dras.sort((a, b) => {
        const getVal = (item: any, key: string) => {
            if (key.includes('.')) {
                const [parent, child] = key.split('.');
                return item[parent]?.[child];
            }
            return item[key];
        };

        const valA = getVal(a, column);
        const valB = getVal(b, column);

        if (typeof valA === 'number' && typeof valB === 'number') {
            return direction === 'asc' ? valA - valB : valB - valA;
        }

        if (valA instanceof Date && valB instanceof Date) {
            return direction === 'asc'
                ? valA.getTime() - valB.getTime()
                : valB.getTime() - valA.getTime();
        }

        return direction === 'asc'
            ? String(valA).localeCompare(String(valB))
            : String(valB).localeCompare(String(valA));
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

const hasActiveDra = computed(() => localDras.value.some(dra => dra.etat === 'actif'));

// Computed property for the total available amount for the current center
const availableAmountForCenter = computed(() => {
    // Assuming all DRAs belong to the same center and thus share the same available amount.
    // We can just take the montant_disponible from the first DRA, or handle if dras is empty.
    if (props.dras.length > 0) {
        return props.dras[0].centre.montant_disponible;
    }
    return 0; // Default if no DRAs are present
});


const closeDra = (draId: string, currentEtat: string) => {
    const normalizedEtat = currentEtat.toLowerCase();
    if (normalizedEtat !== 'refuse' && normalizedEtat !== 'actif') {
        alert('Seuls les DRAs actifs ou refusés peuvent être clôturés');
        return;
    }

    if (confirm('Êtes-vous sûr de vouloir clôturer ce DRA ?')) {
        router.put(route('achat.dras.close', { dra: draId }), {
            preserveScroll: true,
            onSuccess: () => {
                localDras.value = localDras.value.map(dra =>
                    dra.n_dra === draId ? { ...dra, etat: 'cloture' } : dra
                );
            },
            onError: (errors) => {
                alert('Erreur lors de la clôture du DRA: ' + (errors.message || 'Une erreur est survenue'));
            },
        });
    }
};

const confirmDeleteDra = (draId: string, etat: string) => {
    if (etat === 'cloture') {
        alert('Vous ne pouvez pas supprimer un DRA clôturé.');
        return;
    }

    if (confirm('Êtes-vous sûr de vouloir supprimer ce DRA ? Cette action est irréversible.')) {
        router.delete(route('achat.dras.destroy', { dra: draId }));
    }
};

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
};

const createDra = () => {
    if (hasActiveDra.value) return;

    const draCount = props.dras.filter(dra => dra.id_centre === props.id_centre).length;
    const newNDra = `${props.id_centre}-${String(draCount + 1).padStart(3, '0')}`;
    const today = new Date().toISOString().slice(0, 10);

    router.post(route('achat.dras.store'), {
        n_dra: newNDra,
        date_creation: today,
    });
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
            <button
                @click="createDra"
                :disabled="hasActiveDra"
                :class="{
                    'bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 cursor-pointer': !hasActiveDra,
                    'bg-gray-400 dark:bg-gray-600 text-gray-700 dark:text-gray-300 cursor-not-allowed': hasActiveDra
                }"
                class="px-4 py-2 rounded-lg transition flex items-center gap-1 ml-auto"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un DRA</span>
            </button>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6 relative">


            <div class="flex justify-between mb-3 "> <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">Liste des DRAs</h1>
                <div class="absolute top-4 right-4 bg-white dark:bg-gray-700 p-4 rounded-lg shadow-md text-right">
                    <h2 class="text-md font-semibold text-[#042B62FF] dark:text-[#BDBDBDFF] mb-2">Statistiques Centre</h2>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-bold">Montant Disponible:</span>
                        {{ availableAmountForCenter.toLocaleString('fr-FR') }} DA
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 px-5 pb-2">
                <button
                    v-for="etat in etatOptions"
                    :key="etat"
                    @click="selectedEtat = selectedEtat === etat ? null : etat"
                    class="px-4 py-1 rounded-full border text-sm font-medium transition"
                    :class="{
                        'bg-blue-600 text-white': selectedEtat === etat,
                        'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200': selectedEtat !== etat
                    }"
                >
                    {{ etat === 'actif' ? 'Actif' :
                    etat === 'cloture' ? 'Clôturé' :
                        etat === 'refuse' ? 'Refusé' :
                            etat === 'accepte' ?'Accepté' :
                                'Remboursé'
                    }}
                </button>
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
                        <TableCell>
                            <span
                                class="font-bold"
                                :class="{
                                    'text-green-600':dra.etat === 'actif' || dra.etat === 'accepte',
                                    'text-red-600': dra.etat === 'cloture' || dra.etat === 'refuse',
                                    'text-blue-600': dra.etat === 'rembourse'
                                }"
                            >
                                {{
                                    dra.etat === 'actif' ? 'ACTIF' :
                                        dra.etat === 'cloture' ? 'CLÔTURÉ' :
                                            dra.etat === 'refuse' ? 'REFUSÉ' :
                                               dra.etat === 'accepte' ? 'ACCEPTÉ' :
                                                   'REMBOURSÉ'
                                }}
                            </span>
                        </TableCell>
                        <TableCell class="flex flex-wrap gap-2">


                            <Link
                                :href="route('achat.dras.show', dra.n_dra)"
                                class="bg-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 transition flex items-center gap-2"
                            >
                                <FileText class="w-4 h-4" />
                                <span>Afficher</span>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
