
<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2 } from 'lucide-vue-next'
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
            tva: number,
            pivot: {
                qte_ba: number,
                prix_piece: number
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
        qte_ba: p.pivot.qte_ba,
        prix_piece: p.pivot.prix_piece
    }))
})

const selectedItemId = ref<string | number>('')
const quantity = ref(1)
const unitPrice = ref('')

const totalAmount = computed(() => {
    return form.pieces.reduce((total, item) => {
        const piece = props.allPieces.find(p => p.id_piece == item.id_piece)
        if (!piece) return total
        const subtotal = Number(item.prix_piece) * item.qte_ba
        const totalWithTva = subtotal * (1 + (piece.tva / 100))
        return total + totalWithTva
    }, 0)
})

function addItem() {
    if (!selectedItemId.value || quantity.value < 1) return

    const existingIndex = form.pieces.findIndex(p => p.id_piece === selectedItemId.value)
    if (existingIndex >= 0) {
        form.pieces[existingIndex].qte_ba += quantity.value
    } else {
        form.pieces.push({
            id_piece: selectedItemId.value,
            qte_ba: quantity.value,
            prix_piece: unitPrice.value ? Number(unitPrice.value) : 0
        })
    }

    selectedItemId.value = ''
    quantity.value = 1
    unitPrice.value = ''
}

function removeItem(type: 'piece', index: number) {
    if (type === 'piece') {
        form.pieces.splice(index, 1)
    }
}

function submit() {
    form.put(route('scentre.dras.bon-achats.update', {
        dra: props.dra.n_dra,
        bonAchat: props.bonAchat.n_ba
    }), {
        onSuccess: () => {
            window.location.href = route('scentre.dras.bon-achats.index', { dra: props.dra.n_dra })
        }
    })
}

function destroyBonAchat() {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce bon d'achat?")) {
        form.delete(route('scentre.dras.bon-achats.destroy', {
            dra: props.dra.n_dra,
            bonAchat: props.bonAchat.n_ba
        }), {
            onSuccess: () => {
                window.location.href = route('scentre.dras.bon-achats.index', { dra: props.dra.n_dra })
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

                <!-- Input Section for Pieces -->
                <div class="space-y-4">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1 space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pièce</label>
                            <select
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
                        </div>

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
                                            v-model.number="item.qte_ba"
                                            type="number"
                                            min="1"
                                            class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            (
                                                item.prix_piece *
                                                item.qte_ba *
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

                    <div v-if="form.pieces.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                        Aucune pièce sélectionnée. Veuillez ajouter au moins une pièce.
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
                            :href="route('scentre.dras.bon-achats.index', { dra: dra.n_dra })"
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
