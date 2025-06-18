<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2, Pencil } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { TableBody, TableCell, TableHead, TableHeader, TableRow, Table } from '@/components/ui/table'

const props = defineProps<{
    dra: { n_dra: string },
    bonAchat: {
        n_ba: string,
        date_ba: string,
        id_fourn: number,
        pieces: Array<{
            id_piece: number,
            nom_piece: string,
            prix_piece: number,
            tva: number,
            pivot: {
                qte_ba: number
            }
        }>,
        prestations: Array<{
            id_prest: number,
            nom_prest: string,
            prix_prest: number,
            tva: number,
            pivot: {
                qte_bapr: number
            }
        }>,
        charges: Array<{
            id_charge: number,
            nom_charge: string,
            prix_charge: number,
            tva: number,
            pivot: {
                qte_bac: number
            }
        }>
    },
    fournisseurs: Array<{
        id_fourn: number,
        nom_fourn: string
    }>,
    allPieces: Array<{
        id_piece: number,
        nom_piece: string,
        prix_piece: number,
        tva: number
    }>,
    allPrestations: Array<{
        id_prest: number,
        nom_prest: string,
        prix_prest: number,
        tva: number
    }>,
    allCharges: Array<{
        id_charge: number,
        nom_charge: string,
        prix_charge: number,
        tva: number
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title:'Centre', href: '/scentre'},
    { title: 'Gestion des DRAs', href: route('scentre.dras.index') },
    { title: `Details de DRA ${props.dra.n_dra}`, href: route('scentre.dras.show', { dra: props.dra.n_dra }) },
    { title: `Bons d'achat de ${props.dra.n_dra}`, href: route('scentre.dras.bon-achats.index', { dra: props.dra.n_dra }) },
    { title: `Modifier Bon d'achat ${props.bonAchat.n_ba}`, href: route('scentre.dras.bon-achats.edit', { dra: props.dra.n_dra, bonAchat: props.bonAchat.n_ba }) },
]

const form = useForm({
    n_ba: props.bonAchat.n_ba,
    date_ba: props.bonAchat.date_ba,
    id_fourn: props.bonAchat.id_fourn,
    pieces: props.bonAchat.pieces.map(p => ({
        id_piece: p.id_piece.toString(),
        qte_ba: p.pivot.qte_ba
    })),
    prestations: props.bonAchat.prestations.map(pr => ({
        id_prest: pr.id_prest.toString(),
        qte_bapr: pr.pivot.qte_bapr
    })),
    charges: props.bonAchat.charges.map(ch => ({
        id_charge: ch.id_charge.toString(),
        qte_bac: ch.pivot.qte_bac
    }))
})

const initialSelectedItemType = computed(() => {
    if (form.pieces.length > 0) return 'piece'
    if (form.prestations.length > 0) return 'prestation'
    if (form.charges.length > 0) return 'charge'
    return 'piece'
})

const selectedItemType = ref<'piece' | 'prestation' | 'charge'>(initialSelectedItemType.value)
const selectedItemId = ref('')
const quantity = ref(1)

const totalAmount = computed(() => {
    const piecesTotal = form.pieces.reduce((total, item) => {
        const piece = props.allPieces.find(p => p.id_piece == item.id_piece)
        if (!piece) return total
        const subtotal = piece.prix_piece * item.qte_ba
        const totalWithTva = subtotal * (1 + (piece.tva / 100))
        return total + totalWithTva
    }, 0)

    const prestationsTotal = form.prestations.reduce((total, item) => {
        const prestation = props.allPrestations.find(pr => pr.id_prest == item.id_prest)
        if (!prestation) return total
        const subtotal = prestation.prix_prest * item.qte_bapr
        const totalWithTva = subtotal * (1 + (prestation.tva / 100))
        return total + totalWithTva
    }, 0)

    const chargesTotal = form.charges.reduce((total, item) => {
        const charge = props.allCharges.find(ch => ch.id_charge == item.id_charge)
        if (!charge) return total
        const subtotal = charge.prix_charge * item.qte_bac
        const totalWithTva = subtotal * (1 + (charge.tva / 100))
        return total + totalWithTva
    }, 0)

    return piecesTotal + prestationsTotal + chargesTotal
})

function addItem() {
    if (!selectedItemId.value || quantity.value < 1) return

    if (selectedItemType.value === 'piece') {
        const existingIndex = form.pieces.findIndex(p => p.id_piece === selectedItemId.value)
        if (existingIndex >= 0) {
            form.pieces[existingIndex].qte_ba += quantity.value
        } else {
            form.pieces.push({
                id_piece: selectedItemId.value,
                qte_ba: quantity.value
            })
        }
    } else if (selectedItemType.value === 'prestation') {
        const existingIndex = form.prestations.findIndex(p => p.id_prest === selectedItemId.value)
        if (existingIndex >= 0) {
            form.prestations[existingIndex].qte_bapr += quantity.value
        } else {
            form.prestations.push({
                id_prest: selectedItemId.value,
                qte_bapr: quantity.value
            })
        }
    } else if (selectedItemType.value === 'charge') {
        const existingIndex = form.charges.findIndex(c => c.id_charge === selectedItemId.value)
        if (existingIndex >= 0) {
            form.charges[existingIndex].qte_bac += quantity.value
        } else {
            form.charges.push({
                id_charge: selectedItemId.value,
                qte_bac: quantity.value
            })
        }
    }

    selectedItemId.value = ''
    quantity.value = 1
}

function removeItem(type: 'piece' | 'prestation' | 'charge', index: number) {
    if (type === 'piece') {
        form.pieces.splice(index, 1)
    } else if (type === 'prestation') {
        form.prestations.splice(index, 1)
    } else if (type === 'charge') {
        form.charges.splice(index, 1)
    }
}

function submit() {
    form.put(route('scentre.dras.bon-achats.update', {
        dra: props.dra.n_dra,
        bonAchat: props.bonAchat.n_ba
    }), {
        onSuccess: () => {
            window.location.href = route('scentre.dras.bon-achats.index', { dra: props.dra.n_dra })
        },
        onError: () => {
            console.log('Validation errors:', form.errors)
        }
    })
}

function destroyBonAchat() {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce bon d'achat ?")) {
        form.delete(route('scentre.dras.bon-achats.destroy', {
            dra: props.dra.n_dra,
            bonAchat: props.bonAchat.n_ba
        }), {
            onSuccess: () => {
                window.location.href = route('scentre.dras.bon-achats.index', { dra: props.dra.n_dra })
            },
            onError: () => {
                console.log("Erreur lors de la suppression.")
            }
        })
    }
}
</script>

<template>
    <Head :title="`Modifier Bon d'achat ${bonAchat.n_ba}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier le Bon d'achat {{ form.n_ba }}
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Bon d'achat</label>
                        <input
                            v-model="form.n_ba"
                            type="text"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            disabled
                        />
                        <div v-if="form.errors.n_ba" class="text-red-500 text-sm">{{ form.errors.n_ba }}</div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Bon d'achat</label>
                        <input
                            v-model="form.date_ba"
                            type="date"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        />
                        <div v-if="form.errors.date_ba" class="text-red-500 text-sm">{{ form.errors.date_ba }}</div>
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
                                    {{ piece.nom_piece }} ({{ piece.prix_piece }} DA, TVA {{ piece.tva }}%)
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
                                    {{ prestation.nom_prest }} ({{ prestation.prix_prest }} DA, TVA {{ prestation.tva }}%)
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
                                    {{ charge.nom_charge }} ({{ charge.prix_charge }} DA, TVA {{ charge.tva }}%)
                                </option>
                            </select>
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
                            :disabled="!selectedItemId || quantity < 1"
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
                                        {{ allPieces.find(p => p.id_piece == item.id_piece)?.prix_piece }} DA
                                    </TableCell>
                                    <TableCell>
                                        {{ allPieces.find(p => p.id_piece == item.id_piece)?.tva }}%
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.qte_ba"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                (allPieces.find(p => p.id_piece == item.id_piece)?.prix_piece ?? 0) * item.qte_ba *
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
                                        {{ allPrestations.find(pr => pr.id_prest == item.id_prest)?.prix_prest }} DA
                                    </TableCell>
                                    <TableCell>
                                        {{ allPrestations.find(pr => pr.id_prest == item.id_prest)?.tva }}%
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.qte_bapr"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                (allPrestations.find(pr => pr.id_prest == item.id_prest)?.prix_prest ?? 0) * item.qte_bapr *
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
                                        {{ allCharges.find(ch => ch.id_charge == item.id_charge)?.prix_charge }} DA
                                    </TableCell>
                                    <TableCell>
                                        {{ allCharges.find(ch => ch.id_charge == item.id_charge)?.tva }}%
                                    </TableCell>
                                    <TableCell>
                                        <input
                                            v-model.number="item.qte_bac"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                (allCharges.find(ch => ch.id_charge == item.id_charge)?.prix_charge ?? 0) * item.qte_bac *
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

                <!-- Total Amount -->
                <div class="flex justify-end">
                    <div class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Montant Total: {{ totalAmount.toFixed(2) }} DA
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="destroyBonAchat"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>
                    <div class="flex gap-4">
                        <Link
                            :href="route('scentre.dras.bon-achats.index', { dra: props.dra.n_dra })"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || (form.pieces.length === 0 && form.prestations.length === 0 && form.charges.length === 0)"
                            class="px-4 py-2 bg-[#042B62] dark:bg-[#F3B21B] text-white dark:text-[#042B62] rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition flex items-center gap-2 disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Enregistrer les modifications</span>
                            <span v-else class="animate-spin">↻</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
