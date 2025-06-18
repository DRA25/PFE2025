<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, FileText } from 'lucide-vue-next'; // Removed Lock and Trash2 as they don't seem used in the target template

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
        errors?: { // Keeping this just in case, though the update form part is removed
            etat?: string;
        };
    };
    factures: Array<{
        id_facture: number;
        fournisseur: { nom_fourn: string };
        pieces: Array<{ id_piece: number; nom_piece: string; prix_piece: number; tva: number; pivot: { qte_f: number } }>;
        prestations: Array<{ id_prest: number; nom_prest: string; prix_prest: number; tva: number; pivot: { qte_fpr: number } }>;
        charges: Array<{ id_charge: number; nom_charge: string; prix_charge: number; tva: number; pivot: { qte_fc: number } }>;
        droit_timbre?: number;
    }>;
    bonAchats: Array<{
        id_bon: number;
        fournisseur: { nom_fourn: string };
        pieces: Array<{ id_piece: number; nom_piece: string; prix_piece: number; tva: number; pivot: { qte_ba: number } }>;
        prestations: Array<{ id_prest: number; nom_prest: string; prix_prest: number; tva: number; pivot: { qte_bapr: number } }>; // Added for bonAchats
        charges: Array<{ id_charge: number; nom_charge: string; prix_charge: number; tva: number; pivot: { qte_bac: number } }>;    // Added for bonAchats
        // No 'montant' property here, as it will be calculated from pieces, prestations, and charges
    }>;
}>();

// The form and submit function for updating DRA state are removed as per the target template
// const form = useForm({
//     etat: props.dra.etat.toLowerCase()
// });

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Achat', href: route('achat.index') },
    { title: 'Consulter DRAs', href: route('achat.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('achat.dras.show', { dra: props.dra.n_dra }) }
];

// etatOptions and submit function are removed as per the target template
// const etatOptions = [
//     { value: 'cloture', label: 'Clôturé' },
//     { value: 'refuse', label: 'Refusé' },
//     { value: 'accepte', label: 'Accepté' }
// ];

// const submit = () => {
//     form.put(route('achat.dras.update', { dra: props.dra.n_dra }), {
//         preserveScroll: true,
//         onSuccess: () => {
//             router.visit(route('scf.dras.index'), {
//                 preserveScroll: true,
//                 preserveState: true
//             });
//         },
//         onError: () => {
//             // Errors will be automatically available in props.errors
//         }
//     });
// };

// --- Helper Functions for Calculations ---

// Helper function to calculate total HT for a specific item type (pieces, prestations, charges)
const calculateItemTypeHtTotal = (items: any[], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    return items.reduce((sum, item) => {
        let price = 0;
        let quantity = 0;
        if (isBonAchat) {
            if (type === 'pieces') {
                price = item.prix_piece;
                quantity = item.pivot?.qte_ba;
            } else if (type === 'prestations') {
                price = item.prix_prest;
                quantity = item.pivot?.qte_bapr;
            } else if (type === 'charges') {
                price = item.prix_charge;
                quantity = item.pivot?.qte_bac;
            }
        } else { // For Factures
            if (type === 'pieces') {
                price = item.prix_piece;
                quantity = item.pivot?.qte_f;
            } else if (type === 'prestations') {
                price = item.prix_prest;
                quantity = item.pivot?.qte_fpr;
            } else if (type === 'charges') {
                price = item.prix_charge;
                quantity = item.pivot?.qte_fc;
            }
        }
        return sum + (price * (quantity || 1));
    }, 0);
};

// Helper function to calculate TVA for a specific item type
const calculateItemTypeTVA = (items: any[], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    return items.reduce((sum, item) => {
        let price = 0;
        let quantity = 0;
        let tvaRate = 0;

        if (isBonAchat) {
            if (type === 'pieces') {
                price = item.prix_piece;
                quantity = item.pivot?.qte_ba;
                tvaRate = item.tva || 0;
            } else if (type === 'prestations') {
                price = item.prix_prest;
                quantity = item.pivot?.qte_bapr;
                tvaRate = item.tva || 0;
            } else if (type === 'charges') {
                price = item.prix_charge;
                quantity = item.pivot?.qte_bac;
                tvaRate = item.tva || 0;
            }
        } else { // For Factures
            if (type === 'pieces') {
                price = item.prix_piece;
                quantity = item.pivot?.qte_f;
                tvaRate = item.tva || 0;
            } else if (type === 'prestations') {
                price = item.prix_prest;
                quantity = item.pivot?.qte_fpr;
                tvaRate = item.tva || 0;
            } else if (type === 'charges') {
                price = item.prix_charge;
                quantity = item.pivot?.qte_fc;
                tvaRate = item.tva || 0;
            }
        }
        return sum + (price * (quantity || 1) * (tvaRate / 100));
    }, 0);
};

// Function to calculate the total amount (HT + TVA) for a given set of items (pieces, prestations, charges)
const calculateFullTotal = (items: { pieces: any[], prestations: any[], charges: any[], droit_timbre?: number }, isBonAchat: boolean = false) => {
    let total = 0;

    // Calculate pieces total (HT + TVA)
    total += items.pieces.reduce((sum, piece) => sum + (piece.prix_piece * (isBonAchat ? (piece.pivot?.qte_ba || 1) : (piece.pivot?.qte_f || 1)) * (1 + (piece.tva || 0) / 100)), 0);

    // Calculate prestations total (HT + TVA)
    total += items.prestations.reduce((sum, prest) => sum + (prest.prix_prest * (isBonAchat ? (prest.pivot?.qte_bapr || 1) : (prest.pivot?.qte_fpr || 1)) * (1 + (prest.tva || 0) / 100)), 0);

    // Calculate charges total (HT + TVA)
    total += items.charges.reduce((sum, charge) => sum + (charge.prix_charge * (isBonAchat ? (charge.pivot?.qte_bac || 1) : (charge.pivot?.qte_fc || 1)) * (1 + (charge.tva || 0) / 100)), 0);

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

                <div class="mt-20 flex flex-wrap gap-4 justify-center md:justify-end h-fit">
                    <Link
                        v-if="dra.etat === 'actif' || dra.etat === 'refuse' || dra.etat === 'accepte'"
                        :href="route('achat.dras.factures.index', { dra: dra.n_dra })"
                        class="bg-[#042B62] dark:bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-400 transition flex items-center gap-2"
                    >
                        <FileText class="w-4 h-4" />
                        <span>Factures</span>
                    </Link>

                    <Link
                        v-if="dra.etat === 'actif' || dra.etat === 'refuse' || dra.etat === 'accepte'"
                        :href="route('achat.dras.bon-achats.index', { dra: dra.n_dra })"
                        class="bg-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 transition flex items-center gap-2"
                    >
                        <FileText class="w-4 h-4" />
                        <span>Bons d'Achat</span>
                    </Link>
                </div>
            </div>



            <div class="mt-10">
                <h2 class="text-xl font-semibold text-[#042B62FF] dark:text-[#F3B21B] mb-4">Factures liées</h2>
                <div v-if="factures.length > 0" class="space-y-2">
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
                                <TableCell>{{ facture.fournisseur.nom_fourn }}</TableCell>
                                <TableCell>
                                    <span v-if="facture.pieces.length > 0" class="block">Pièces</span>
                                    <span v-if="facture.prestations.length > 0" class="block">Prestations</span>
                                    <span v-if="facture.charges.length > 0" class="block">Charges</span>
                                </TableCell>
                                <TableCell>
                                    <div v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot.qte_f }})
                                    </div>
                                    <div v-for="prestation in facture.prestations" :key="prestation.id_prest" class="text-sm">
                                        {{ prestation.nom_prest }} (x{{ prestation.pivot.qte_fpr }})
                                    </div>
                                    <div v-for="charge in facture.charges" :key="charge.id_charge" class="text-sm">
                                        {{ charge.nom_charge }} (x{{ charge.pivot.qte_fc }})
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.pieces, 'pieces').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.prestations.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.prestations, 'prestations').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.charges.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.charges, 'charges').toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.pieces, 'pieces').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.prestations.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.prestations, 'prestations').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.charges.length > 0" class="text-sm">
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
                <div v-if="bonAchats.length > 0" class="space-y-2">
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
                                <TableCell>{{ bon.fournisseur.nom_fourn }}</TableCell>
                                <TableCell>
                                    <span v-if="bon.pieces.length > 0" class="block">Pièces</span>
                                    <span v-if="bon.prestations.length > 0" class="block">Prestations</span>
                                    <span v-if="bon.charges.length > 0" class="block">Charges</span>
                                </TableCell>
                                <TableCell>
                                    <div v-for="piece in bon.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot.qte_ba }})
                                    </div>
                                    <div v-for="prestation in bon.prestations" :key="prestation.id_prest" class="text-sm">
                                        {{ prestation.nom_prest }} (x{{ prestation.pivot.qte_bapr }})
                                    </div>
                                    <div v-for="charge in bon.charges" :key="charge.id_charge" class="text-sm">
                                        {{ charge.nom_charge }} (x{{ charge.pivot.qte_bac }})
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                    </div>
                                    <div v-if="bon.prestations.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(bon.prestations, 'prestations', true).toFixed(2) }} DA
                                    </div>
                                    <div v-if="bon.charges.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(bon.charges, 'charges', true).toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                    </div>
                                    <div v-if="bon.prestations.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(bon.prestations, 'prestations', true).toFixed(2) }} DA
                                    </div>
                                    <div v-if="bon.charges.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(bon.charges, 'charges', true).toFixed(2) }} DA
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
