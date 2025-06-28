<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2, Pencil } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { TableBody, TableCell, TableHead, TableHeader, TableRow, Table } from '@/components/ui/table'; // Ensure Table is imported

const props = defineProps<{
    dra: { n_dra: string },
    facture: {
        n_facture: string,
        date_facture: string,
        id_fourn: number,
        droit_timbre?: number,
        pieces: Array<{
            id_piece: number,
            nom_piece: string,
            tva: number,
            pivot: {
                qte_f: number,
                prix_piece: number
            }
        }>,
        prestations: Array<{
            id_prest: number,
            nom_prest: string,
            tva: number,
            pivot: {
                qte_fpr: number,
                prix_prest: number
            }
        }>,
        charges: Array<{
            id_charge: number,
            nom_charge: string,
            tva: number,
            pivot: {
                qte_fc: number,
                prix_charge: number
            }
        }>,
    },
    fournisseurs: Array<{
        id_fourn: number,
        nom_fourn: string
    }>,
    allPieces: Array<{
        id_piece: number,
        nom_piece: string,
        tva: number
    }>,
    allPrestations: Array<{
        id_prest: number,
        nom_prest: string,
        tva: number
    }>,
    // The allCharges prop now correctly reflects that prix_charge is not directly on the Charge model
    allCharges: Array<{
        id_charge: number,
        nom_charge: string,
        tva: number
    }>,
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title:'Centre', href: '/scentre'},
    { title: 'Gestion des DRAs', href: route('scentre.dras.index') },
    { title: `Details de DRA ${props.dra.n_dra}`, href: route('scentre.dras.show', { dra: props.dra.n_dra }) },
    { title: `Factures de ${props.dra.n_dra}`, href: route('scentre.dras.factures.index', { dra: props.dra.n_dra }) },
    { title: `Modifier Facture ${props.facture.n_facture}`, href: route('scentre.dras.factures.edit', { dra: props.dra.n_dra, facture: props.facture.n_facture }) },
]

const form = useForm({
    n_facture: props.facture.n_facture,
    date_facture: props.facture.date_facture,
    id_fourn: props.facture.id_fourn,
    pieces: props.facture.pieces.map(p => ({
        id_piece: p.id_piece.toString(),
        qte_f: p.pivot.qte_f,
        prix_piece: p.pivot.prix_piece
    })),
    prestations: props.facture.prestations.map(pr => ({
        id_prest: pr.id_prest.toString(),
        qte_fpr: pr.pivot.qte_fpr,
        prix_prest: pr.pivot.prix_prest
    })),
    // Map existing charges, ensuring prix_charge from pivot is included
    charges: props.facture.charges.map(ch => ({
        id_charge: ch.id_charge.toString(),
        qte_fc: ch.pivot.qte_fc,
        prix_charge: ch.pivot.prix_charge
    })),
    droit_timbre: props.facture.droit_timbre ?? 0,
})

// Determine initial selectedItemType based on existing items
const initialSelectedItemType = computed(() => {
    if (form.pieces.length > 0) return 'piece';
    if (form.prestations.length > 0) return 'prestation';
    if (form.charges.length > 0) return 'charge';
    return 'piece'; // Default if none exist
});

// Ref to control which item type is currently selected for adding
const selectedItemType = ref<'piece' | 'prestation' | 'charge'>(initialSelectedItemType.value);

// Track selected item, quantity and unit price for the add form
const selectedItemId = ref('');
const quantity = ref(1);
const unitPrice = ref(''); // Can be string initially for placeholder, converted to number on add

// Calculate total amount
const totalAmount = computed(() => {
    const piecesTotal = form.pieces.reduce((total, item) => {
        const piece = props.allPieces.find(p => p.id_piece == item.id_piece)
        if (!piece) return total

        const subtotal = item.prix_piece * item.qte_f
        const totalWithTva = subtotal * (1 + (piece.tva / 100))
        return total + totalWithTva
    }, 0)

    const prestationsTotal = form.prestations.reduce((total, item) => {
        const prestation = props.allPrestations.find(pr => pr.id_prest == item.id_prest)
        if (!prestation) return total

        const subtotal = item.prix_prest * item.qte_fpr
        const totalWithTva = subtotal * (1 + (prestation.tva / 100))
        return total + totalWithTva
    }, 0)

    const chargesTotal = form.charges.reduce((total, item) => {
        // Get TVA from allCharges, as prix_charge is now on the item itself (from the pivot)
        const charge = props.allCharges.find(ch => ch.id_charge == item.id_charge)
        if (!charge) return total

        const subtotal = item.prix_charge * item.qte_fc // Use item.prix_charge from the form's charges array
        const totalWithTva = subtotal * (1 + (charge.tva / 100)) // Use TVA from allCharges
        return total + totalWithTva
    }, 0)

    return piecesTotal + prestationsTotal + chargesTotal + (form.droit_timbre || 0)
})

// Generic function to add any item type
function addItem() {
    if (!selectedItemId.value || quantity.value < 1) return;

    // Ensure unit price is valid for all types (now including charges)
    if (Number(unitPrice.value) <= 0) {
        console.error("Le prix unitaire doit être supérieur à zéro.");
        return;
    }

    if (selectedItemType.value === 'piece') {
        const existingIndex = form.pieces.findIndex(p => p.id_piece.toString() === selectedItemId.value);
        if (existingIndex >= 0) {
            form.pieces[existingIndex].qte_f += quantity.value;
            // Optionally update price if desired: form.pieces[existingIndex].prix_piece = Number(unitPrice.value);
        } else {
            form.pieces.push({
                id_piece: selectedItemId.value,
                qte_f: quantity.value,
                prix_piece: Number(unitPrice.value)
            });
        }
    } else if (selectedItemType.value === 'prestation') {
        const existingIndex = form.prestations.findIndex(p => p.id_prest.toString() === selectedItemId.value);
        if (existingIndex >= 0) {
            form.prestations[existingIndex].qte_fpr += quantity.value;
            // Optionally update price if desired: form.prestations[existingIndex].prix_prest = Number(unitPrice.value);
        } else {
            form.prestations.push({
                id_prest: selectedItemId.value,
                qte_fpr: quantity.value,
                prix_prest: Number(unitPrice.value)
            });
        }
    } else if (selectedItemType.value === 'charge') {
        const existingIndex = form.charges.findIndex(c => c.id_charge.toString() === selectedItemId.value);
        if (existingIndex >= 0) {
            form.charges[existingIndex].qte_fc += quantity.value;
            // Optionally update price if desired: form.charges[existingIndex].prix_charge = Number(unitPrice.value);
        } else {
            form.charges.push({
                id_charge: selectedItemId.value,
                qte_fc: quantity.value,
                prix_charge: Number(unitPrice.value)
            });
        }
    }

    selectedItemId.value = '';
    quantity.value = 1;
    unitPrice.value = ''; // Reset unit price after adding
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
    form.put(route('scentre.dras.factures.update', { dra: props.dra.n_dra, facture: props.facture.n_facture }), {
        onSuccess: () => {
            window.location.href = route('scentre.dras.factures.index', { dra: props.dra.n_dra })
        },
        onError: () => {
            console.log('Validation errors:', form.errors)
        }
    })
}

// Placeholder for custom modal confirmation
const showDeleteConfirmModal = ref(false);

function confirmDelete() {
    showDeleteConfirmModal.value = true;
}

function executeDelete() {
    form.delete(route('scentre.dras.factures.destroy', {
        dra: props.dra.n_dra,
        facture: props.facture.n_facture
    }), {
        onSuccess: () => {
            window.location.href = route('scentre.dras.factures.index', { dra: props.dra.n_dra });
        },
        onError: (errors) => {
            console.error("Erreur lors de la suppression:", errors);
            // You might want to display these errors in a more user-friendly way
        },
        onFinish: () => {
            showDeleteConfirmModal.value = false; // Close modal regardless of success/failure
        }
    });
}
</script>

<template>
    <Head title="Modifier Facture" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier la Facture {{ form.n_facture }}
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
                            disabled
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
                                v-for="fournisseur in fournisseurs"
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

                <!-- Radio buttons for item type selection -->
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

                <!-- Conditional Input Section based on selectedItemType -->
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
                                    v-for="piece in allPieces"
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
                                    v-for="prestation in allPrestations"
                                    :key="prestation.id_prest"
                                    :value="prestation.id_prest"
                                >
                                    {{ prestation.nom_prest }} (TVA {{ prestation.tva }}%)
                                </option>
                            </select>
                            <select
                                v-else-if="selectedItemType === 'charge'"
                                v-model="selectedItemId"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une charge --</option>
                                <option
                                    v-for="charge in allCharges"
                                    :key="charge.id_charge"
                                    :value="charge.id_charge"
                                >
                                    {{ charge.nom_charge }} (TVA {{ charge.tva }}%)
                                </option>
                            </select>
                        </div>

                        <!-- unitPrice input is now visible for all item types -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Unitaire</label>
                            <input
                                v-model="unitPrice"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-24 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                placeholder="0.00"
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
                            :disabled="!selectedItemId || quantity < 1 || Number(unitPrice) <= 0"
                            class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 disabled:opacity-50"
                        >
                            <Plus class="w-4 h-4" />
                            Ajouter
                        </button>
                    </div>

                    <!-- Display Table for Pieces -->
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
                                    :key="`piece-${item.id_piece}-${index}`"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900"
                                >
                                    <TableCell>
                                        {{ allPieces.find(p => p.id_piece == item.id_piece)?.nom_piece }}
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.prix_piece"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        /> DA
                                    </TableCell>
                                    <TableCell>
                                        {{ allPieces.find(p => p.id_piece == item.id_piece)?.tva }}%
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
                                                item.prix_piece *
                                                item.qte_f *
                                                (1 + ((allPieces.find(p => p.id_piece == item.id_piece)?.tva ?? 0) / 100))
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

                    <!-- Display Table for Prestations -->
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
                                    :key="`prestation-${item.id_prest}-${index}`"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900"
                                >
                                    <TableCell>
                                        {{ allPrestations.find(pr => pr.id_prest == item.id_prest)?.nom_prest }}
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.prix_prest"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        /> DA
                                    </TableCell>
                                    <TableCell>
                                        {{ allPrestations.find(pr => pr.id_prest == item.id_prest)?.tva }}%
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
                                                item.prix_prest *
                                                item.qte_fpr *
                                                (1 + ((allPrestations.find(pr => pr.id_prest == item.id_prest)?.tva ?? 0) / 100))
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

                    <!-- Display Table for Charges -->
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
                                    :key="`charge-${item.id_charge}-${index}`"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900"
                                >
                                    <TableCell>
                                        {{ allCharges.find(ch => ch.id_charge == item.id_charge)?.nom_charge }}
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.prix_charge"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        /> DA
                                    </TableCell>
                                    <TableCell>
                                        {{ allCharges.find(ch => ch.id_charge == item.id_charge)?.tva }}%
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
                                                item.prix_charge *
                                                item.qte_fc *
                                                (1 + ((allCharges.find(ch => ch.id_charge == item.id_charge)?.tva ?? 0) / 100))
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
                    <button
                        type="button"
                        @click="confirmDelete"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>
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
                        <span>Enregistrer les modifications</span>
                        <span v-if="form.processing" class="animate-spin">↻</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Custom Confirmation Modal (basic example) -->
        <div v-if="showDeleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl text-center">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Êtes-vous sûr de vouloir supprimer cette facture ?</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Cette action est irréversible.</p>
                <div class="flex justify-center space-x-4">
                    <button
                        @click="showDeleteConfirmModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Annuler
                    </button>
                    <button
                        @click="executeDelete"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
