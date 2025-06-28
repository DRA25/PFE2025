<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { TableBody, TableCell, TableHead, TableHeader, TableRow, Table } from '@/components/ui/table'; // Ensure Table is imported

const props = defineProps({
    dra: Object,
    fournisseurs: Array,
    pieces: Array,
    prestations: Array,
    charges: Array, // This 'charges' prop now only contains static charge details (nom, tva, etc.), NOT prix_charge
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: '/scentre' },
    { title: 'Gestion des DRAs', href: route('scentre.dras.index') },
    { title: `Details de DRA ${props.dra.n_dra}`, href: route('scentre.dras.show', { dra: props.dra.n_dra }) },
    { title: `Factures pour DRA ${props.dra.n_dra}`, href: route('scentre.dras.factures.index', { dra: props.dra.n_dra }) },
    { title: `Créer une Facture pour DRA ${props.dra.n_dra}`, href: route('scentre.dras.factures.create', { dra: props.dra.n_dra }) },
]

const form = useForm({
    n_facture: '',
    date_facture: '',
    id_fourn: '',
    droit_timbre: 0,
    pieces: [] as Array<{ id_piece: string, qte_f: number, prix_piece: number }>,
    prestations: [] as Array<{ id_prest: string, qte_fpr: number, prix_prest: number }>,
    charges: [] as Array<{ id_charge: string, qte_fc: number, prix_charge: number }>, // Added prix_charge here
})

// Ref to control which item type is currently selected for adding
const selectedItemType = ref<'piece' | 'prestation' | 'charge'>('piece'); // Default to piece

// Track selected item and quantity for the add form
const selectedItemId = ref('');
const quantity = ref(1);
const unitPrice = ref(0); // This will now apply to pieces, prestations, and charges

// Calculate total amount including pieces, prestations, charges, and droit_timbre
const totalAmount = computed(() => {
    const piecesTotal = form.pieces.reduce((total, item) => {
        const pieceDetails = props.pieces.find(p => p.id_piece == item.id_piece)
        if (!pieceDetails) return total
        const subtotal = item.prix_piece * item.qte_f // Use item.prix_piece from the form
        const totalWithTva = subtotal * (1 + (pieceDetails.tva / 100))
        return total + totalWithTva
    }, 0)

    const prestationsTotal = form.prestations.reduce((total, item) => {
        const prestationDetails = props.prestations.find(p => p.id_prest == item.id_prest)
        if (!prestationDetails) return total
        const subtotal = item.prix_prest * item.qte_fpr // Use item.prix_prest from the form
        const totalWithTva = subtotal * (1 + (prestationDetails.tva / 100))
        return total + totalWithTva
    }, 0)

    const chargesTotal = form.charges.reduce((total, item) => {
        const chargeDetails = props.charges.find(c => c.id_charge == item.id_charge) // Get TVA from original charge details
        if (!chargeDetails) return total
        const subtotal = item.prix_charge * item.qte_fc // Use item.prix_charge from the form
        const totalWithTva = subtotal * (1 + (chargeDetails.tva / 100)) // Use TVA from original charge
        return total + totalWithTva
    }, 0)

    return piecesTotal + prestationsTotal + chargesTotal + Number(form.droit_timbre || 0)
})

// Generic function to add any item type
function addItem() {
    if (!selectedItemId.value || quantity.value < 1) return;

    if (unitPrice.value <= 0) { // All item types (piece, prestation, charge) now require a unitPrice
        // You might want a more user-friendly message or visual indicator here
        console.error("Le prix unitaire doit être supérieur à zéro.");
        return;
    }

    if (selectedItemType.value === 'piece') {
        const existingIndex = form.pieces.findIndex(p => p.id_piece === selectedItemId.value);
        if (existingIndex >= 0) {
            form.pieces[existingIndex].qte_f += quantity.value;
            // Optionally update price if desired: form.pieces[existingIndex].prix_piece = unitPrice.value;
        } else {
            form.pieces.push({ id_piece: selectedItemId.value, qte_f: quantity.value, prix_piece: unitPrice.value });
        }
    } else if (selectedItemType.value === 'prestation') {
        const existingIndex = form.prestations.findIndex(p => p.id_prest === selectedItemId.value);
        if (existingIndex >= 0) {
            form.prestations[existingIndex].qte_fpr += quantity.value;
            // Optionally update price if desired: form.prestations[existingIndex].prix_prest = unitPrice.value;
        } else {
            form.prestations.push({ id_prest: selectedItemId.value, qte_fpr: quantity.value, prix_prest: unitPrice.value });
        }
    } else if (selectedItemType.value === 'charge') {
        const existingIndex = form.charges.findIndex(c => c.id_charge === selectedItemId.value);
        if (existingIndex >= 0) {
            form.charges[existingIndex].qte_fc += quantity.value;
            // Optionally update price if desired: form.charges[existingIndex].prix_charge = unitPrice.value;
        } else {
            form.charges.push({ id_charge: selectedItemId.value, qte_fc: quantity.value, prix_charge: unitPrice.value }); // Add prix_charge
        }
    }

    selectedItemId.value = '';
    quantity.value = 1;
    unitPrice.value = 0; // Reset unit price after adding
}

// Generic function to remove any item type
function removeItem(type: 'piece' | 'prestation' | 'charge', index: number) {
    if (type === 'piece') {
        form.pieces.splice(index, 1);
    } else if (type === 'prestation') {
        form.prestations.splice(index, 1);
    } else if (type === 'charge') {
        form.charges.splice(index, 1);
    }
}

function submit() {
    form.post(route('scentre.dras.factures.store', { dra: props.dra.n_dra }), {
        onSuccess: () => {
            form.reset();
            window.location.href = route('scentre.dras.factures.index', { dra: props.dra.n_dra });
        },
        onError: () => {
            console.log('Validation error:', form.errors);
        }
    });
}
</script>

<template>
    <Head :title="`Créer une Facture pour DRA ${props.dra.n_dra}`" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Créer une Facture pour DRA {{ props.dra.n_dra }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div v-if="Object.keys(form.errors).length" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Facture</label>
                        <input
                            v-model="form.n_facture"
                            type="text"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        />
                        <div v-if="form.errors.n_facture" class="text-red-500 text-sm">{{ form.errors.n_facture }}</div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Facture</label>
                        <input
                            v-model="form.date_facture"
                            type="date"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        />
                        <div v-if="form.errors.date_facture" class="text-red-500 text-sm">{{ form.errors.date_facture }}</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                    <div class="flex gap-3">
                        <select
                            v-model="form.id_fourn"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">-- Sélectionnez un fournisseur --</option>
                            <option
                                v-for="fournisseur in props.fournisseurs"
                                :key="fournisseur.id_fourn"
                                :value="fournisseur.id_fourn"
                            >
                                {{ fournisseur.nom_fourn }}
                            </option>
                        </select>
                        <Link
                            href="/fournisseurs/create"
                            as="button"
                            class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
                        >
                            <Plus class="w-4 h-4" />
                        </Link>
                    </div>
                    <div v-if="form.errors.id_fourn" class="text-red-500 text-sm">{{ form.errors.id_fourn }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type d'article à ajouter</label>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="radio"
                                v-model="selectedItemType"
                                value="piece"
                                class="form-radio h-4 w-4 text-[#042B62] dark:text-[#F3B21B]"
                            />
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Pièce</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="radio"
                                v-model="selectedItemType"
                                value="prestation"
                                class="form-radio h-4 w-4 text-[#042B62] dark:text-[#F3B21B]"
                            />
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Prestation</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="radio"
                                v-model="selectedItemType"
                                value="charge"
                                class="form-radio h-4 w-4 text-[#042B62] dark:text-[#F3B21B]"
                            />
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Charge</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1 space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ selectedItemType === 'piece' ? 'Pièce' : selectedItemType === 'prestation' ? 'Prestation' : 'Charge' }}
                            </label>
                            <select
                                v-if="selectedItemType === 'piece'"
                                v-model="selectedItemId"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une pièce --</option>
                                <option
                                    v-for="piece in props.pieces"
                                    :key="piece.id_piece"
                                    :value="piece.id_piece"
                                >
                                    {{ piece.nom_piece }} (TVA {{ piece.tva }}%)
                                </option>
                            </select>
                            <select
                                v-else-if="selectedItemType === 'prestation'"
                                v-model="selectedItemId"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une prestation --</option>
                                <option
                                    v-for="prestation in props.prestations"
                                    :key="prestation.id_prest"
                                    :value="prestation.id_prest"
                                >
                                    {{ prestation.nom_prest }} (TVA {{ prestation.tva }}%) </option>
                            </select>
                            <select
                                v-else-if="selectedItemType === 'charge'"
                                v-model="selectedItemId"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une charge --</option>
                                <option
                                    v-for="charge in props.charges"
                                    :key="charge.id_charge"
                                    :value="charge.id_charge"
                                >
                                    {{ charge.nom_charge }} (TVA {{ charge.tva }}%)
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Unitaire (DA)</label>
                            <input
                                v-model.number="unitPrice"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-24 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                            <input
                                v-model.number="quantity"
                                type="number"
                                min="1"
                                class="w-24 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <button
                            type="button"
                            @click="addItem"
                            :disabled="!selectedItemId || quantity < 1 || unitPrice <= 0"
                            class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 disabled:opacity-50"
                        >
                            <Plus class="w-4 h-4" />
                            Ajouter
                        </button>
                    </div>

                    <div v-if="form.pieces.length > 0" class="overflow-x-auto bg-gray-100 dark:bg-gray-800 rounded-lg m-5">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 p-3">Pièces sélectionnées:</h4>
                        <Table class="w-full">
                            <TableHeader>
                                <TableRow class="bg-gray-50 dark:bg-gray-700">
                                    <TableHead>Pièce</TableHead>
                                    <TableHead>Prix Unitaire</TableHead>
                                    <TableHead>TVA</TableHead>
                                    <TableHead>Quantité</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="(item, index) in form.pieces"
                                    :key="`piece-${index}`"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900"
                                >
                                    <TableCell>
                                        {{ props.pieces.find(p => p.id_piece === item.id_piece)?.nom_piece }}
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.prix_piece"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                        DA
                                    </TableCell>
                                    <TableCell>
                                        {{ props.pieces.find(p => p.id_piece === item.id_piece)?.tva }}%
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.qte_f"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                item.prix_piece * // Use item.prix_piece from the form
                                                item.qte_f *
                                                (1 + ((props.pieces.find(p => p.id_piece === item.id_piece)?.tva ?? 0) / 100))
                                            ).toFixed(2)
                                        }} DA
                                    </TableCell>
                                    <TableCell>
                                        <button
                                            type="button"
                                            @click="removeItem('piece', index)"
                                            class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                            aria-label="Supprimer pièce"
                                        >
                                            <Trash2 class="w-5 h-5" />
                                        </button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="form.prestations.length > 0" class="overflow-x-auto bg-gray-100 dark:bg-gray-800 rounded-lg m-5">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 p-3">Prestations sélectionnées:</h4>
                        <Table class="w-full">
                            <TableHeader>
                                <TableRow class="bg-gray-50 dark:bg-gray-700">
                                    <TableHead>Prestation</TableHead>
                                    <TableHead>Prix Unitaire</TableHead>
                                    <TableHead>TVA</TableHead>
                                    <TableHead>Quantité</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="(item, index) in form.prestations"
                                    :key="`prestation-${index}`"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900"
                                >
                                    <TableCell>
                                        {{ props.prestations.find(p => p.id_prest === item.id_prest)?.nom_prest }}
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.prix_prest" type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                        DA
                                    </TableCell>
                                    <TableCell>
                                        {{ props.prestations.find(p => p.id_prest === item.id_prest)?.tva }}%
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.qte_fpr"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                item.prix_prest * // Use item.prix_prest from the form
                                                item.qte_fpr *
                                                (1 + ((props.prestations.find(p => p.id_prest === item.id_prest)?.tva ?? 0) / 100))
                                            ).toFixed(2)
                                        }} DA
                                    </TableCell>
                                    <TableCell>
                                        <button
                                            type="button"
                                            @click="removeItem('prestation', index)"
                                            class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                            aria-label="Supprimer prestation"
                                        >
                                            <Trash2 class="w-5 h-5" />
                                        </button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="form.charges.length > 0" class="overflow-x-auto bg-gray-100 dark:bg-gray-800 rounded-lg m-5">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 p-3">Charges sélectionnées:</h4>
                        <Table class="w-full">
                            <TableHeader>
                                <TableRow class="bg-gray-50 dark:bg-gray-700">
                                    <TableHead>Charge</TableHead>
                                    <TableHead>Prix Unitaire</TableHead>
                                    <TableHead>TVA</TableHead>
                                    <TableHead>Quantité</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="(item, index) in form.charges"
                                    :key="`charge-${index}`"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900"
                                >
                                    <TableCell>
                                        {{ props.charges.find(c => c.id_charge === item.id_charge)?.nom_charge }}
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.prix_charge"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                        DA
                                    </TableCell>
                                    <TableCell>
                                        {{ props.charges.find(c => c.id_charge === item.id_charge)?.tva }}%
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.qte_fc"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                item.prix_charge * // Use item.prix_charge from the form
                                                item.qte_fc *
                                                (1 + ((props.charges.find(c => c.id_charge === item.id_charge)?.tva ?? 0) / 100))
                                            ).toFixed(2)
                                        }} DA
                                    </TableCell>
                                    <TableCell>
                                        <button
                                            type="button"
                                            @click="removeItem('charge', index)"
                                            class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                            aria-label="Supprimer charge"
                                        >
                                            <Trash2 class="w-5 h-5" />
                                        </button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="form.pieces.length === 0 && form.prestations.length === 0 && form.charges.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                        Aucun article sélectionné. Veuillez ajouter au moins une pièce, prestation ou charge.
                    </div>
                </div>

                <div class="flex justify-end items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Droit Timbre (DA):</label>
                    <input
                        v-model.number="form.droit_timbre"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-32 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="flex justify-end">
                    <div class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Montant Total: {{ totalAmount.toFixed(2) }} DA
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <Link
                        :href="route('scentre.dras.factures.index', { dra: props.dra.n_dra })"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || (form.pieces.length === 0 && form.prestations.length === 0 && form.charges.length === 0)"
                        class="px-4 py-2 bg-[#042B62] dark:bg-[#F3B21B] text-white dark:text-[#042B62] rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <span>Créer</span>
                        <span v-if="form.processing" class="animate-spin">↻</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
