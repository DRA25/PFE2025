<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2, Pencil } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
    dra: { n_dra: string },
    facture: {
        n_facture: string,
        date_facture: string,
        id_fourn: number,
        pieces: Array<{
            id_piece: number,
            nom_piece: string,
            prix_piece: number,
            tva: number,
            pivot: {
                qte_f: number
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
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title:'Achat', href: '/achat'},
    { title: 'Gestion des DRAs', href: route('achat.dras.index') },
    { title: `Factures de ${props.dra.n_dra}`, href: route('achat.dras.factures.index', { dra: props.dra.n_dra }) },
    { title: `Modifier Facture ${props.facture.n_facture}`, href: route('achat.dras.factures.edit', { dra: props.dra.n_dra, facture: props.facture.n_facture }) },
]

const form = useForm({
    n_facture: props.facture.n_facture,
    date_facture: props.facture.date_facture,
    id_fourn: props.facture.id_fourn,
    pieces: props.facture.pieces.map(p => ({
        id_piece: p.id_piece,
        qte_f: p.pivot.qte_f
    })),
    droit_timbre: (props.facture as any).droit_timbre ?? 0,
})

// Track selected piece for the add form
const selectedPiece = ref('')
const quantity = ref(1)

// Calculate total amount
const totalAmount = computed(() => {
    const piecesTotal = form.pieces.reduce((total, item) => {
        const piece = props.allPieces.find(p => p.id_piece == item.id_piece)
        if (!piece) return total

        const subtotal = piece.prix_piece * item.qte_f
        const totalWithTva = subtotal * (1 + (piece.tva / 100))
        return total + totalWithTva
    }, 0)
    return piecesTotal + (form.droit_timbre || 0)
})

// Add a piece to the invoice
function addPiece() {
    if (!selectedPiece.value) return

    // Check if piece already exists
    const existingIndex = form.pieces.findIndex(p => p.id_piece === Number(selectedPiece.value))

    if (existingIndex >= 0) {
        // Update quantity if piece already exists
        form.pieces[existingIndex].qte_f += quantity.value
    } else {
        // Add new piece
        form.pieces.push({
            id_piece: Number(selectedPiece.value),
            qte_f: quantity.value
        })
    }

    // Reset form
    selectedPiece.value = ''
    quantity.value = 1
}

// Remove a piece from the invoice
function removePiece(index: number) {
    form.pieces.splice(index, 1)
}

function submit() {
    form.put(route('achat.dras.factures.update', { dra: props.dra.n_dra, facture: props.facture.n_facture }), {
        onSuccess: () => {
            window.location.href = route('achat.dras.factures.index', { dra: props.dra.n_dra })
        },
        onError: () => {
            console.log('Validation errors:', form.errors)
        }
    })
}

function destroyFacture() {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet facture ?")) {
        form.delete(route('achat.dras.factures.destroy', {
            dra: props.dra.n_dra,
            facture: props.facture.n_facture
        }), {
            onSuccess: () => {
                window.location.href = route('achat.dras.factures.index', { dra: props.dra.n_dra });
            },
            onError: () => {
                console.log("Erreur lors de la suppression.");
            }
        });
    }
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

                <!-- Piece Selection Section -->
                <div class="space-y-4">
                    <h3 class="text-md font-medium text-gray-700 dark:text-gray-300">Pièces</h3>

                    <div class="flex gap-3 items-end">
                        <div class="flex-1 space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pièce</label>
                            <select
                                v-model="selectedPiece"
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
                            @click="addPiece"
                            :disabled="!selectedPiece"
                            class="px-4 py-2 rounded-lg transition flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 disabled:opacity-50"
                        >
                            <Plus class="w-4 h-4" />
                            Ajouter
                        </button>
                    </div>

                    <!-- Selected Pieces Table -->
                    <div v-if="form.pieces.length > 0" class="overflow-x-auto bg-gray-100 dark:bg-gray-800 rounded-lg m-5">
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
                                    :key="index"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-900 transition-colors"
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
                                            v-model.number="item.qte_f"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                allPieces.find(p => p.id_piece == item.id_piece)?.prix_piece * item.qte_f *
                                                (1 + (allPieces.find(p => p.id_piece == item.id_piece)?.tva / 100))
                                            ).toFixed(2)
                                        }} DA
                                    </TableCell>
                                    <TableCell>
                                        <button
                                            type="button"
                                            @click="removePiece(index)"
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


                    <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                        Aucune pièce sélectionnée
                    </div>

                    <!-- Droit Timbre Input -->
                    <div class="flex justify-end items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Droit Timbre (DA):</label>
                        <input
                            v-model.number="form.droit_timbre"
                            type="number"
                            min="0"
                            class="w-32 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <!-- Total Amount -->
                    <div class="flex justify-end">
                        <div class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            Montant Total: {{ totalAmount.toFixed(2) }} DA
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="destroyFacture"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>
                    <div class="flex gap-4">
                        <Link
                            :href="route('achat.dras.factures.index', { dra: props.dra.n_dra })"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || form.pieces.length === 0"
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
