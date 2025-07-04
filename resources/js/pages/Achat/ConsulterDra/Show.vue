<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { TableBody, TableCell, TableHead, TableHeader, TableRow, Table } from '@/components/ui/table';
import { ArrowLeft, FileText } from 'lucide-vue-next';

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
    };
    factures?: Array<{
        id_facture: number;
        fournisseur?: { nom_fourn: string };
        pieces?: Array<{ id_piece: number; nom_piece: string; tva: number; pivot: { qte_f: number; prix_piece: number } }>;
        prestations?: Array<{ id_prest: number; nom_prest: string; tva: number; pivot: { qte_fpr: number; prix_prest: number } }>;
        charges?: Array<{ id_charge: number; nom_charge: string; tva: number; pivot: { qte_fc: number; prix_charge: number } }>;
        droit_timbre?: number;
    }>;
    bonAchats?: Array<{
        id_bon: number;
        fournisseur?: { nom_fourn: string };
        pieces?: Array<{ id_piece: number; nom_piece: string; tva: number; pivot: { qte_ba: number; prix_piece: number } }>;
        // Removed prestations and charges from BonAchat type definition as they are no longer direct relations
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: route('achat.index') },
    { title: 'Consulter DRAs', href: route('achat.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('achat.dras.show', { dra: props.dra.n_dra }) }
];

// Helper function to get the price from the correct location (pivot for pieces/prestations/charges)
const getItemPrice = (item: any, type: 'pieces' | 'prestations' | 'charges') => {
    if (type === 'pieces') return item.pivot?.prix_piece || 0;
    if (type === 'prestations') return item.pivot?.prix_prest || 0;
    if (type === 'charges') return item.pivot?.prix_charge || 0;
    return 0;
};

// Helper function to get the quantity from the correct pivot property
const getItemQuantity = (item: any, type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean) => {
    if (isBonAchat) {
        if (type === 'pieces') return item.pivot?.qte_ba || 0;
        // For BonAchat, prestations and charges are not relevant for quantity here
    } else { // For Facture
        if (type === 'pieces') return item.pivot?.qte_f || 0;
        if (type === 'prestations') return item.pivot?.qte_fpr || 0;
        if (type === 'charges') return item.pivot?.qte_fc || 0;
    }
    return 0;
};

// Helper function to calculate total for a specific item type (HT only)
const calculateItemTypeHtTotal = (items: any[] = [], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    if (!items || items.length === 0) return 0;

    return items.reduce((sum, item) => {
        // If it's a BonAchat and type is not pieces, skip the calculation
        if (isBonAchat && (type === 'prestations' || type === 'charges')) return sum;

        const price = getItemPrice(item, type);
        const quantity = getItemQuantity(item, type, isBonAchat);

        return sum + (price * quantity);
    }, 0);
};

// Helper function to calculate TVA for a specific item type
const calculateItemTypeTVA = (items: any[] = [], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    if (!items || items.length === 0) return 0;

    return items.reduce((sum, item) => {
        // If it's a BonAchat and type is not pieces, skip the calculation
        if (isBonAchat && (type === 'prestations' || type === 'charges')) return sum;

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
} = {}, isBonAchat: boolean = false) => {
    let total = 0;

    // Calculate pieces total (HT + TVA)
    total += (items.pieces || []).reduce((sum, piece) => {
        const price = getItemPrice(piece, 'pieces');
        const quantity = getItemQuantity(piece, 'pieces', isBonAchat);
        return sum + (price * quantity * (1 + ((piece.tva || 0) / 100)));
    }, 0);

    // Calculate prestations total (HT + TVA) - only for Facture
    if (!isBonAchat) {
        total += (items.prestations || []).reduce((sum, prest) => {
            const price = getItemPrice(prest, 'prestations');
            const quantity = getItemQuantity(prest, 'prestations', isBonAchat);
            return sum + (price * quantity * (1 + ((prest.tva || 0) / 100)));
        }, 0);
    }

    // Calculate charges total (HT + TVA) - only for Facture
    if (!isBonAchat) {
        total += (items.charges || []).reduce((sum, charge) => {
            const price = getItemPrice(charge, 'charges');
            const quantity = getItemQuantity(charge, 'charges', isBonAchat);
            return sum + (price * quantity * (1 + ((charge.tva || 0) / 100)));
        }, 0);
    }

    // Add droit_timbre if applicable (only for factures)
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

                <div class="flex justify-between gap-1 mb-6 w-fit h-fit mt-10">

                        <a
                            :href="route('export.bordereau-operations', dra.n_dra)"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2 shadow-md"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            <span>Exporter BOD</span>
                        </a>

                        <a
                            :href="route('export.demande-derogation', dra.n_dra)"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2 shadow-md"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            <span>Exporter Demande de Dérogation</span>
                        </a>

                </div>
            </div>

            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Factures liées</h2>
                <div v-if="factures && factures.length > 0" class="space-y-2">
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
                                    <span v-if="facture.pieces && facture.pieces.length > 0" class="block">Pièces</span>
                                    <span v-if="facture.prestations && facture.prestations.length > 0" class="block">Prestations</span>
                                    <span v-if="facture.charges && facture.charges.length > 0" class="block">Charges</span>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces" v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot?.qte_f || 0 }})
                                    </div>
                                    <div v-if="facture.prestations" v-for="prestation in facture.prestations" :key="prestation.id_prest" class="text-sm">
                                        {{ prestation.nom_prest }} (x{{ prestation.pivot?.qte_fpr || 0 }})
                                    </div>
                                    <div v-if="facture.charges" v-for="charge in facture.charges" :key="charge.id_charge" class="text-sm">
                                        {{ charge.nom_charge }} (x{{ charge.pivot?.qte_fc || 0 }})
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces && facture.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.pieces, 'pieces').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.prestations && facture.prestations.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.prestations, 'prestations').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.charges && facture.charges.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.charges, 'charges').toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces && facture.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.pieces, 'pieces').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.prestations && facture.prestations.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.prestations, 'prestations').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.charges && facture.charges.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.charges, 'charges').toFixed(2) }} DA
                                    </div>
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
                <div v-if="bonAchats && bonAchats.length > 0" class="space-y-2">
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
                                    <span v-if="bon.pieces && bon.pieces.length > 0" class="block">Pièces</span>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces" v-for="piece in bon.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot?.qte_ba || 0 }})
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces && bon.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces && bon.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                    </div>
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
                    :href="route('achat.dras.index')"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la liste des DRAs</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
