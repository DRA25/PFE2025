<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { TableBody, TableCell, TableHead, TableHeader, TableRow, Table } from '@/components/ui/table';
import { ArrowLeft } from 'lucide-vue-next';

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
    factures: Array<{
        id_facture: number;
        fournisseur: { nom_fourn: string };
        pieces: Array<{ id_piece: number; nom_piece: string; tva: number; pivot: { qte_f: number; prix_piece: number } }>;
        prestations: Array<{ id_prest: number; nom_prest: string; tva: number; pivot: { qte_fpr: number; prix_prest: number } }>;
        charges: Array<{ id_charge: number; nom_charge: string; tva: number; pivot: { qte_fc: number; prix_charge: number } }>;
        droit_timbre?: number;
    }>;
    bonAchats: Array<{
        id_bon: number;
        fournisseur: { nom_fourn: string };
        pieces: Array<{ id_piece: number; nom_piece: string; tva: number; pivot: { qte_ba: number; prix_piece: number } }>;
        // Removed prestations and charges from BonAchat type as per new schema
    }>;
}>();

const form = useForm({
    etat: props.dra.etat.toLowerCase()
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Coordination Finnanciere', href: route('scf.index') },
    { title: 'Consulter DRAs', href: route('scf.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('scf.dras.show', { dra: props.dra.n_dra }) }
];

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
        }
    });
};

// Updated calculation functions to correctly get prices and quantities from pivot
const getItemPrice = (item: any, type: 'pieces' | 'prestations' | 'charges') => {
    if (type === 'pieces') return item.pivot?.prix_piece || 0;
    if (type === 'prestations') return item.pivot?.prix_prest || 0;
    return item.pivot?.prix_charge || 0; // Corrected: prix_charge is on pivot
};

const getItemQuantity = (item: any, type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean) => {
    if (isBonAchat) {
        if (type === 'pieces') return item.pivot?.qte_ba || 0;
        // BonAchat no longer has prestations or charges, so these cases are simplified
        return 0; // Should not be reached for bonAchats type 'prestations' or 'charges'
    } else { // For Facture
        if (type === 'pieces') return item.pivot?.qte_f || 0;
        if (type === 'prestations') return item.pivot?.qte_fpr || 0;
        return item.pivot?.qte_fc || 0;
    }
};

const calculateItemTypeHtTotal = (items: any[] = [], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    if (!items || items.length === 0) return 0;

    return items.reduce((sum, item) => {
        const price = getItemPrice(item, type);
        const quantity = getItemQuantity(item, type, isBonAchat);
        return sum + (price * quantity);
    }, 0);
};

const calculateItemTypeTVA = (items: any[] = [], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    if (!items || items.length === 0) return 0;

    return items.reduce((sum, item) => {
        const price = getItemPrice(item, type);
        const quantity = getItemQuantity(item, type, isBonAchat);
        const tvaRate = item.tva || 0;
        return sum + (price * quantity * (tvaRate / 100));
    }, 0);
};

const calculateFullTotal = (items: {
    pieces?: any[],
    prestations?: any[],
    charges?: any[],
    droit_timbre?: number
}, isBonAchat: boolean = false) => {
    let total = 0;

    // Pieces (using pivot price)
    total += (items.pieces || []).reduce((sum, item) => {
        const price = getItemPrice(item, 'pieces');
        const quantity = getItemQuantity(item, 'pieces', isBonAchat);
        const tvaRate = item.tva || 0;
        return sum + (price * quantity * (1 + (tvaRate / 100)));
    }, 0);

    // Prestations (using pivot price) - Only for Facture
    if (!isBonAchat && items.prestations) {
        total += items.prestations.reduce((sum, item) => {
            const price = getItemPrice(item, 'prestations');
            const quantity = getItemQuantity(item, 'prestations', isBonAchat);
            const tvaRate = item.tva || 0;
            return sum + (price * quantity * (1 + (tvaRate / 100)));
        }, 0);
    }

    // Charges (using pivot price) - Only for Facture
    if (!isBonAchat && items.charges) {
        total += items.charges.reduce((sum, item) => {
            const price = getItemPrice(item, 'charges');
            const quantity = getItemQuantity(item, 'charges', isBonAchat);
            const tvaRate = item.tva || 0;
            return sum + (price * quantity * (1 + (tvaRate / 100)));
        }, 0);
    }

    // Droit timbre (only for factures)
    if (!isBonAchat && items.droit_timbre !== undefined) {
        total += items.droit_timbre || 0;
    }

    return total;
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
                        <p class="text-gray-900 dark:text-gray-100">{{ Number(dra.total_dra).toFixed(2) }} DA</p>
                    </div>

                </div>

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
                                :class="{ 'border-red-500': props.dra.errors?.etat }"
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
                            <p v-if="props.dra.errors?.etat" class="mt-2 text-sm text-red-600">
                                {{ props.dra.errors.etat }}
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



            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Factures liées</h2>
                <div v-if="factures?.length > 0" class="space-y-2">
                    <Table class="m-3 w-39/40 bg-white dark:bg-[#111827] rounded-lg">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fournisseur</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Libellé</TableHead>
                                <TableHead>Montant HT</TableHead>
                                <TableHead>TVA</TableHead>
                                <TableHead>Droit Timbre</TableHead>
                                <TableHead>Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="facture in factures"
                                :key="facture.id_facture"
                                class="hover:bg-gray-300 dark:hover:bg-gray-900"
                            >
                                <TableCell>{{ facture.fournisseur?.nom_fourn || '-' }}</TableCell>
                                <TableCell>
                                    <template v-if="facture.pieces?.length > 0">
                                        <span class="block">Pièces</span>
                                    </template>
                                    <template v-if="facture.prestations?.length > 0">
                                        <span class="block">Prestations</span>
                                    </template>
                                    <template v-if="facture.charges?.length > 0">
                                        <span class="block">Charges</span>
                                    </template>
                                </TableCell>
                                <TableCell>
                                    <template v-if="facture.pieces">
                                        <div v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                            {{ piece.nom_piece }} (x{{ piece.pivot?.qte_f || 0 }})
                                        </div>
                                    </template>
                                    <template v-if="facture.prestations">
                                        <div v-for="prestation in facture.prestations" :key="prestation.id_prest" class="text-sm">
                                            {{ prestation.nom_prest }} (x{{ prestation.pivot?.qte_fpr || 0 }})
                                        </div>
                                    </template>
                                    <template v-if="facture.charges">
                                        <div v-for="charge in facture.charges" :key="charge.id_charge" class="text-sm">
                                            {{ charge.nom_charge }} (x{{ charge.pivot?.qte_fc || 0 }})
                                        </div>
                                    </template>
                                </TableCell>
                                <TableCell>
                                    <template v-if="facture.pieces?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeHtTotal(facture.pieces, 'pieces').toFixed(2) }} DA
                                        </div>
                                    </template>
                                    <template v-if="facture.prestations?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeHtTotal(facture.prestations, 'prestations').toFixed(2) }} DA
                                        </div>
                                    </template>
                                    <template v-if="facture.charges?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeHtTotal(facture.charges, 'charges').toFixed(2) }} DA
                                        </div>
                                    </template>
                                </TableCell>
                                <TableCell>
                                    <template v-if="facture.pieces?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeTVA(facture.pieces, 'pieces').toFixed(2) }} DA
                                        </div>
                                    </template>
                                    <template v-if="facture.prestations?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeTVA(facture.prestations, 'prestations').toFixed(2) }} DA
                                        </div>
                                    </template>
                                    <template v-if="facture.charges?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeTVA(facture.charges, 'charges').toFixed(2) }} DA
                                        </div>
                                    </template>
                                </TableCell>
                                <TableCell>{{ (facture.droit_timbre || 0).toFixed(2) }} DA</TableCell>
                                <TableCell>
                                    {{ calculateFullTotal(facture).toFixed(2) }} DA
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucune facture liée à ce DRA.</p>
            </div>


            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Bons d'Achat liés</h2>
                <div v-if="bonAchats?.length > 0" class="space-y-2">
                    <Table class="m-3 w-39/40 bg-white dark:bg-[#111827] rounded-lg">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fournisseur</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Libellé</TableHead>
                                <TableHead>Montant HT</TableHead>
                                <TableHead>TVA</TableHead>
                                <TableHead>Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="bon in bonAchats"
                                :key="bon.id_bon"
                                class="hover:bg-gray-300 dark:hover:bg-gray-900"
                            >
                                <TableCell>{{ bon.fournisseur?.nom_fourn || '-' }}</TableCell>
                                <TableCell>
                                    <template v-if="bon.pieces?.length > 0">
                                        <span class="block">Pièces</span>
                                    </template>
                                    <!-- Removed prestations and charges templates for BonAchat -->
                                </TableCell>
                                <TableCell>
                                    <template v-if="bon.pieces">
                                        <div v-for="piece in bon.pieces" :key="piece.id_piece" class="text-sm">
                                            {{ piece.nom_piece }} (x{{ piece.pivot?.qte_ba || 0 }})
                                        </div>
                                    </template>
                                    <!-- Removed prestations and charges content for BonAchat -->
                                </TableCell>
                                <TableCell>
                                    <template v-if="bon.pieces?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeHtTotal(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                        </div>
                                    </template>
                                    <!-- Removed HT totals for prestations and charges for BonAchat -->
                                </TableCell>
                                <TableCell>
                                    <template v-if="bon.pieces?.length > 0">
                                        <div class="text-sm">
                                            {{ calculateItemTypeTVA(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                        </div>
                                    </template>
                                    <!-- Removed TVA totals for prestations and charges for BonAchat -->
                                </TableCell>
                                <TableCell>
                                    {{ calculateFullTotal(bon, true).toFixed(2) }} DA
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucun bon d'achat lié à ce DRA.</p>
            </div>




            <div class="mt-10">
                <Link
                    :href="route('scf.dras.index')"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retour</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
