<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { TableBody, TableCell, TableHead, TableHeader, TableRow, Table } from '@/components/ui/table';
import { ArrowLeft, FileText, Lock, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue'; // Import ref for reactive state

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
    factures: Array<{
        id_facture: number;
        fournisseur: { nom_fourn: string };
        pieces: Array<{ id_piece: number; nom_piece: string; tva: number; pivot: { qte_f: number; prix_piece: number } }>;
        // Updated: prix_prest is on pivot
        prestations: Array<{ id_prest: number; nom_prest: string; tva: number; pivot: { qte_fpr: number; prix_prest: number } }>;
        // Updated: prix_charge is on pivot
        charges: Array<{ id_charge: number; nom_charge: string; tva: number; pivot: { qte_fc: number; prix_charge: number } }>;
        droit_timbre?: number;
    }>;
    bonAchats: Array<{
        id_bon: number;
        fournisseur: { nom_fourn: string };
        // prix_piece moved to pivot, tva remains on piece
        pieces: Array<{ id_piece: number; nom_piece: string; tva: number; pivot: { qte_ba: number; prix_piece: number } }>;
        // prestations and charges are removed from BonAchat, so no need to define them here
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: route('scentre.dras.index') },
    { title: 'Gestion des DRAs', href: '/scentre/dras' },
    { title: `Détails de DRA ${props.dra.n_dra}`, href: route('scentre.dras.show', { dra: props.dra.n_dra }) },
];

// Reactive state for the confirmation modal
const showCloseDraConfirmModal = ref(false);

const closeDra = (draId: string, currentEtat: string) => {
    const normalizedEtat = currentEtat.toLowerCase();
    if (normalizedEtat !== 'refuse' && normalizedEtat !== 'actif') {
        console.warn('Seuls les DRAs actifs ou refusés peuvent être clôturés');
        return;
    }
    showCloseDraConfirmModal.value = true; // Show the custom confirmation modal
};

const executeCloseDra = () => {
    router.put(route('scentre.dras.close', { dra: props.dra.n_dra }), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally, refresh the page or update local state if needed
            // For example, if 'dra.etat' is reactive and updated by the backend
            // Inertia will automatically re-render with new props if data changes.
            showCloseDraConfirmModal.value = false; // Close modal on success
        },
        onError: (errors) => {
            console.error('Erreur lors de la clôture du DRA:', errors);
            // Display error to user via a message box or a dedicated error display area
            alert('Erreur lors de la clôture du DRA: ' + (errors.message || 'Une erreur est survenue'));
            showCloseDraConfirmModal.value = false; // Close modal on error
        },
    });
};

// Helper function to get the price from the correct location (pivot or direct)
const getItemPrice = (item: any, type: 'pieces' | 'prestations' | 'charges') => {
    if (type === 'pieces') return item.pivot?.prix_piece || 0;
    if (type === 'prestations') return item.pivot?.prix_prest || 0; // price is on pivot for prestations
    if (type === 'charges') return item.pivot?.prix_charge || 0;   // price is on pivot for charges
    return 0;
};

// Helper function to get the quantity from the correct pivot property
const getItemQuantity = (item: any, type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean) => {
    if (isBonAchat) {
        if (type === 'pieces') return item.pivot?.qte_ba || 0;
        // BonAchat no longer has prestations or charges, so these cases won't be hit for BonAchat
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
        const price = getItemPrice(item, type);
        const quantity = getItemQuantity(item, type, isBonAchat);
        return sum + (price * quantity);
    }, 0);
};

// Helper function to calculate TVA for a specific item type
const calculateItemTypeTVA = (items: any[] = [], type: 'pieces' | 'prestations' | 'charges', isBonAchat: boolean = false) => {
    if (!items || items.length === 0) return 0;

    return items.reduce((sum, item) => {
        const price = getItemPrice(item, type);
        const quantity = getItemQuantity(item, type, isBonAchat);
        const tvaRate = item.tva || 0;
        return sum + (price * quantity * (tvaRate / 100));
    }, 0);
};

// Function to calculate the total amount for a Bon d'Achat (HT + TVA for pieces only)
const calculateBonAchatFullTotal = (bon: typeof props.bonAchats[0]) => {
    let total = 0;
    // Pieces (using pivot price and piece tva)
    total += (bon.pieces || []).reduce((sum, piece) => {
        const price = getItemPrice(piece, 'pieces');
        const quantity = getItemQuantity(piece, 'pieces', true);
        const tvaRate = piece.tva || 0;
        return sum + (price * quantity * (1 + (tvaRate / 100)));
    }, 0);

    // No prestations or charges for BonAchat
    return total;
};

// Function to calculate the total amount for a Facture (HT + TVA for all types + droit_timbre)
const calculateFactureFullTotal = (facture: typeof props.factures[0]) => {
    let total = 0;

    // Pieces (using pivot price and piece tva)
    total += (facture.pieces || []).reduce((sum, piece) => {
        const price = getItemPrice(piece, 'pieces');
        const quantity = getItemQuantity(piece, 'pieces', false);
        const tvaRate = piece.tva || 0;
        return sum + (price * quantity * (1 + (tvaRate / 100)));
    }, 0);

    // Prestations (using pivot price and prestation tva)
    total += (facture.prestations || []).reduce((sum, prest) => {
        const price = getItemPrice(prest, 'prestations');
        const quantity = getItemQuantity(prest, 'prestations', false);
        const tvaRate = prest.tva || 0;
        return sum + (price * quantity * (1 + (tvaRate / 100)));
    }, 0);

    // Charges (using pivot price and charge tva)
    total += (facture.charges || []).reduce((sum, charge) => {
        const price = getItemPrice(charge, 'charges');
        const quantity = getItemQuantity(charge, 'charges', false);
        const tvaRate = charge.tva || 0;
        return sum + (price * quantity * (1 + (tvaRate / 100)));
    }, 0);

    // Droit timbre
    total += facture.droit_timbre || 0;

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
                        <p class="text-900 dark:text-100">{{ dra.date_creation }}</p>
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
                    <a
                        :href="route('export.demande-derogation', dra.n_dra)"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                        <span>Exporter Demande de Dérégation</span>
                    </a>

                    <button
                        v-if="dra.etat === 'actif' || dra.etat === 'refuse'"
                        @click="closeDra(dra.n_dra, dra.etat)"
                        class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition flex items-center gap-2"
                    >
                        <Lock class="w-4 h-4" />
                        <span>Clôturer</span>
                    </button>

                    <Link
                        v-if="dra.etat === 'actif' || dra.etat === 'refuse'"
                        :href="route('scentre.dras.factures.index', { dra: dra.n_dra })"
                        class="bg-[#042B62] dark:bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-400 transition flex items-center gap-2"
                    >
                        <FileText class="w-4 h-4" />
                        <span>Factures</span>
                    </Link>

                    <Link
                        v-if="dra.etat === 'actif' || dra.etat === 'refuse'"
                        :href="route('scentre.dras.bon-achats.index', { dra: dra.n_dra })"
                        class="bg-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 transition flex items-center gap-2"
                    >
                        <FileText class="w-4 h-4" />
                        <span>Bons d'Achat</span>
                    </Link>



                    <Link
                        v-if="dra.etat === 'actif'"
                        :href="route('scentre.dras.destroy', { dra: dra.n_dra })"
                        method="delete"
                        as="button"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-400 transition flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        <span>Supprimer</span>
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
                                <TableCell>{{ facture.fournisseur?.nom_fourn || '-' }}</TableCell>
                                <TableCell>
                                    <span v-if="facture.pieces?.length > 0" class="block">Pièces</span>
                                    <span v-if="facture.prestations?.length > 0" class="block">Prestations</span>
                                    <span v-if="facture.charges?.length > 0" class="block">Charges</span>
                                </TableCell>
                                <TableCell>
                                    <div v-for="piece in facture.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot?.qte_f || 0 }})
                                    </div>
                                    <div v-for="prestation in facture.prestations" :key="prestation.id_prest" class="text-sm">
                                        {{ prestation.nom_prest }} (x{{ prestation.pivot?.qte_fpr || 0 }})
                                    </div>
                                    <div v-for="charge in facture.charges" :key="charge.id_charge" class="text-sm">
                                        {{ charge.nom_charge }} (x{{ charge.pivot?.qte_fc || 0 }})
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces?.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.pieces, 'pieces').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.prestations?.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.prestations, 'prestations').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.charges?.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(facture.charges, 'charges').toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="facture.pieces?.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.pieces, 'pieces').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.prestations?.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.prestations, 'prestations').toFixed(2) }} DA
                                    </div>
                                    <div v-if="facture.charges?.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(facture.charges, 'charges').toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>{{ (facture.droit_timbre || 0).toFixed(2) }} DA</TableCell>
                                <TableCell>
                                    {{ calculateFactureFullTotal(facture).toFixed(2) }} DA
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
                                <TableCell>{{ bon.fournisseur?.nom_fourn || '-' }}</TableCell>
                                <TableCell>
                                    <span v-if="bon.pieces?.length > 0" class="block">Pièces</span>
                                </TableCell>
                                <TableCell>
                                    <div v-for="piece in bon.pieces" :key="piece.id_piece" class="text-sm">
                                        {{ piece.nom_piece }} (x{{ piece.pivot?.qte_ba || 0 }})
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces?.length > 0" class="text-sm">
                                        {{ calculateItemTypeHtTotal(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="bon.pieces?.length > 0" class="text-sm">
                                        {{ calculateItemTypeTVA(bon.pieces, 'pieces', true).toFixed(2) }} DA
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ calculateBonAchatFullTotal(bon).toFixed(2) }} DA
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400">Aucun bon d'achat lié à ce DRA.</p>
            </div>

            <div class="mt-10">
                <Link
                    :href="route('scentre.dras.index')"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center space-x-1"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Retourner à la liste des DRAs</span>
                </Link>
            </div>
        </div>

        <!-- Custom Confirmation Modal for closing DRA -->
        <div v-if="showCloseDraConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl text-center">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Êtes-vous sûr de vouloir clôturer ce DRA ?</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Cette action peut modifier l'état du DRA.</p>
                <div class="flex justify-center space-x-4">
                    <button
                        @click="showCloseDraConfirmModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </button>
                    <button
                        @click="executeCloseDra"
                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition"
                    >
                        Confirmer la clôture
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
