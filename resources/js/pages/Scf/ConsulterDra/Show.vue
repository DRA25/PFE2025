<script setup lang="ts">
import { Head, Link, router,useForm, } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, FileText, Lock, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    dra: {
        n_dra: string;
        date_creation: string;
        etat: string;
        total_dra: number;
        centre: {
            seuil_centre: number;
            montant_disponible: number;
        };
        errors?: {
            etat?: string;
        };
    };
    factures: Array<any>;
    bonAchats: Array<any>;
}>();



const form = useForm({
    etat: props.dra.etat.toLowerCase() // Ensure lowercase to match validation
});


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Coordination Finnanciere', href: route('scf.index') },
    { title: 'Consulter DRAs', href: route('scf.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('scf.dras.show', { dra: props.dra.n_dra }) }

];

// Ensure these values exactly match your Laravel validation
const etatOptions = [
    { value: 'cloture', label: 'Clôturé' },
    { value: 'refuse', label: 'Refusé' },
    { value: 'accepte', label: 'Accepté' }
];

const submit = () => {
    form.put(route('scf.dras.update', { dra: props.dra.n_dra }), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('scf.dras.index'), {
                preserveScroll: true,
                preserveState: true
            });
        },
        onError: () => {
            // Errors will be automatically available in props.errors
        }
    });
};


</script>

<template>
    <Head title="Détails du DRA" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B]">
                        Informations sur le DRA
                    </h2>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Numéro DRA</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ dra.n_dra }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date de création</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ dra.date_creation }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">État</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ dra.etat }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total DRA</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ dra.total_dra }}</p>
                    </div>

                </div>

                <!-- Right Column - State Update Form -->
                <div>
                    <h2 class="text-xl font-semibold text-primary-600 dark:text-yellow-400 mb-4">
                        Mettre à jour l'état
                    </h2>

                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                État
                            </label>
                            <select
                                v-model="form.etat"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': errors?.etat }"
                                :disabled="form.processing"
                            >
                                <option
                                    v-for="option in etatOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <p v-if="errors?.etat" class="mt-2 text-sm text-red-600">
                                {{ errors.etat }}
                            </p>


                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Mettre à jour</span>
                            </button>

                                <Link
                                    :href="route('scf.dras.index')"
                                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                                >
                                    <ArrowLeft class="w-4 h-4" />
                                    <span>Retour</span>
                                </Link>

                        </div>
                    </form>
                </div>

            </div>



            <!-- FACTURES -->
            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Factures liées</h2>
                <div v-if="factures.length > 0" class="space-y-2">
                    <Table class="m-3 w-39/40 bg-white dark:bg-[#111827] rounded-lg">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fournisseur</TableHead>
                                <TableHead>Libellé</TableHead>
                                <TableHead>Montant</TableHead>
                                <TableHead>TVA</TableHead>
                                <TableHead>Droit Timbre</TableHead>
                                <TableHead>Nombre de pièces</TableHead>
                                <TableHead>Total</TableHead>

                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="facture in factures"
                                :key="facture.id_facture"
                                class="hover:bg-gray-300 dark:hover:bg-gray-900"
                            >
                                <TableCell>{{ facture.fournisseur.nom_fourn }}</TableCell>
                                <TableCell>
                                    <div v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot.qte_f }})
                                    </div>
                                </TableCell>
                                <TableCell>{{ Number(facture.montant).toFixed(2) }} DA</TableCell>
                                <TableCell>
                                    <div v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.tva }}%
                                    </div>
                                </TableCell>
                                <TableCell>{{ facture.droit_timbre }} DA</TableCell>
                                <TableCell>{{ facture.pieces.length }}</TableCell>
                                <TableCell>{{ Number(facture.montant + facture.droit_timbre).toFixed(2) }} DA</TableCell>

                            </TableRow>
                        </TableBody>
                    </Table>

                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucune facture liée à ce DRA.</p>
            </div>

            <!-- BON ACHATS -->
            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Bons d'Achat liés</h2>
                <div v-if="bonAchats.length > 0" class="space-y-2">
                    <Table class="m-3 w-39/40 bg-white dark:bg-[#111827] rounded-lg">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fournisseur</TableHead>
                                <TableHead>Libellé</TableHead>
                                <TableHead>Montant</TableHead>
                                <TableHead>TVA</TableHead>
                                <TableHead>Nombre de pièces</TableHead>
                                <TableHead>Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="bon in bonAchats"
                                :key="bon.id_bon"
                                class="hover:bg-gray-300 dark:hover:bg-gray-900"
                            >
                                <TableCell>{{ bon.fournisseur.nom_fourn }}</TableCell>
                                <TableCell>
                                    <div v-for="piece in bon.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot.qte_ba }})
                                    </div>
                                </TableCell>
                                <TableCell>{{ Number(bon.montant).toFixed(2) }} DA</TableCell>
                                <TableCell>
                                    <div v-for="piece in bon.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.tva }}%
                                    </div>
                                </TableCell>
                                <TableCell>{{ bon.pieces.length }}</TableCell>
                                <TableCell>{{ Number(bon.montant).toFixed(2) }} DA</TableCell>

                            </TableRow>
                        </TableBody>
                    </Table>


                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucun bon d'achat lié à ce DRA.</p>
            </div>


        </div>
    </AppLayout>
</template>
