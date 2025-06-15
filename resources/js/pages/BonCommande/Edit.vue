<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Plus, Trash2, Pencil } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps<{
    boncommande: {
        n_bc: string,
        date_bc: string,
        pieces: Array<{
            id_piece: number,
            nom_piece: string,
            qte_commandep: number,
            prix_piece: number,
            tva: number,
        }>,
        prestations: Array<{
            id_prest: number,
            nom_prest: string,
            qte_commandepr: number,
            prix_prest: number,
            tva: number,
        }>,
        charges: Array<{
            id_charge: number,
            nom_charge: string,
            qte_commandec: number,
            prix_charge: number,
            tva: number,
        }>,
    },
    pieces: Array<{
        id_piece: number,
        nom_piece: string,
        prix_piece: number,
        tva: number,
    }>,
    prestations: Array<{
        id_prest: number,
        nom_prest: string,
        prix_prest: number,
        tva: number,
    }>,
    charges: Array<{
        id_charge: number,
        nom_charge: string,
        prix_charge: number,
        tva: number,
    }>,
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Centre', href: '/scentre' },
    { title: 'Gestion des Bons de Commande', href: route('scentre.boncommandes.index') },
    { title: `Modifier Bon de Commande ${props.boncommande.n_bc}`, href: route('scentre.boncommandes.edit', { n_bc: props.boncommande.n_bc }) },
]

const form = useForm({
    n_bc: props.boncommande.n_bc,
    date_bc: props.boncommande.date_bc,
    pieces: props.boncommande.pieces.map(p => ({
        id_piece: p.id_piece,
        qte_commandep: p.qte_commandep,
    })),
    prestations: props.boncommande.prestations.map(pr => ({
        id_prest: pr.id_prest,
        qte_commandepr: pr.qte_commandepr,
    })),
    charges: props.boncommande.charges.map(c => ({
        id_charge: c.id_charge,
        qte_commandec: c.qte_commandec,
    }))
})

const selectedPiece = ref<string | number>('')
const quantityPiece = ref(1)
const selectedPrestation = ref<string | number>('')
const quantityPrestation = ref(1)
const selectedCharge = ref<string | number>('')
const quantityCharge = ref(1)

const totalAmount = computed(() => {
    let total = 0;

    // Calculate total for pieces
    total += form.pieces.reduce((subTotal, item) => {
        const piece = props.pieces.find(p => p.id_piece == item.id_piece)
        if (!piece) return subTotal

        const itemSubtotal = piece.prix_piece * item.qte_commandep
        const itemTotalWithTva = itemSubtotal * (1 + (piece.tva / 100))
        return subTotal + itemTotalWithTva
    }, 0);

    // Calculate total for prestations
    total += form.prestations.reduce((subTotal, item) => {
        const prestation = props.prestations.find(p => p.id_prest == item.id_prest)
        if (!prestation) return subTotal

        const itemSubtotal = prestation.prix_prest * item.qte_commandepr
        const itemTotalWithTva = itemSubtotal * (1 + (prestation.tva / 100))
        return subTotal + itemTotalWithTva
    }, 0);

    // Calculate total for charges
    total += form.charges.reduce((subTotal, item) => {
        const charge = props.charges.find(c => c.id_charge == item.id_charge)
        if (!charge) return subTotal

        const itemSubtotal = charge.prix_charge * item.qte_commandec
        const itemTotalWithTva = itemSubtotal * (1 + (charge.tva / 100))
        return subTotal + itemTotalWithTva
    }, 0);

    return total;
})

function addPiece() {
    if (!selectedPiece.value || quantityPiece.value < 1) return

    const existingIndex = form.pieces.findIndex(p => p.id_piece === Number(selectedPiece.value))

    if (existingIndex >= 0) {
        form.pieces[existingIndex].qte_commandep += quantityPiece.value
    } else {
        form.pieces.push({
            id_piece: Number(selectedPiece.value),
            qte_commandep: quantityPiece.value,
        })
    }

    selectedPiece.value = ''
    quantityPiece.value = 1
}

function removePiece(index: number) {
    form.pieces.splice(index, 1)
}

function addPrestation() {
    if (!selectedPrestation.value || quantityPrestation.value < 1) return

    const existingIndex = form.prestations.findIndex(p => p.id_prest === Number(selectedPrestation.value))

    if (existingIndex >= 0) {
        form.prestations[existingIndex].qte_commandepr += quantityPrestation.value
    } else {
        form.prestations.push({
            id_prest: Number(selectedPrestation.value),
            qte_commandepr: quantityPrestation.value,
        })
    }

    selectedPrestation.value = ''
    quantityPrestation.value = 1
}

function removePrestation(index: number) {
    form.prestations.splice(index, 1)
}

function addCharge() {
    if (!selectedCharge.value || quantityCharge.value < 1) return

    const existingIndex = form.charges.findIndex(c => c.id_charge === Number(selectedCharge.value))

    if (existingIndex >= 0) {
        form.charges[existingIndex].qte_commandec += quantityCharge.value
    } else {
        form.charges.push({
            id_charge: Number(selectedCharge.value),
            qte_commandec: quantityCharge.value,
        })
    }

    selectedCharge.value = ''
    quantityCharge.value = 1
}

function removeCharge(index: number) {
    form.charges.splice(index, 1)
}

function submit() {
    form.put(route('scentre.boncommandes.update', { n_bc: props.boncommande.n_bc }), {
        onSuccess: () => {
            window.location.href = route('scentre.boncommandes.index')
        },
        onError: (errors) => {
            console.error('Validation errors:', errors)
        }
    })
}

function destroyBonCommande() {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le Bon de Commande numéro ${props.boncommande.n_bc}?`)) {
        form.delete(route('scentre.boncommandes.destroy', { n_bc: props.boncommande.n_bc }), {
            onSuccess: () => {
                window.location.href = route('scentre.boncommandes.index');
            },
            onError: (errors) => {
                console.error("Erreur lors de la suppression:", errors);
                alert("Une erreur est survenue lors de la suppression.");
            }
        });
    }
}
</script>

<template>
    <Head :title="`Modifier Bon de Commande ${boncommande.n_bc}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Modifier le Bon de Commande {{ form.n_bc }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow" novalidate>
                <div v-if="Object.keys(form.errors).length" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="n_bc" class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Bon de Commande</label>
                        <input
                            id="n_bc"
                            v-model="form.n_bc"
                            type="text"
                            aria-label="N° Bon de Commande"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            disabled
                        />
                        <div v-if="form.errors.n_bc" class="text-red-500 text-sm">{{ form.errors.n_bc }}</div>
                    </div>

                    <div class="space-y-2">
                        <label for="date_bc" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de Commande</label>
                        <input
                            id="date_bc"
                            v-model="form.date_bc"
                            type="date"
                            aria-label="Date de Commande"
                            class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            required
                        />
                        <div v-if="form.errors.date_bc" class="text-red-500 text-sm">{{ form.errors.date_bc }}</div>
                    </div>
                </div>

                <!-- Pieces Section -->
                <div class="space-y-4">
                    <h3 class="text-md font-medium text-gray-700 dark:text-gray-300">Pièces Commandées</h3>

                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1 space-y-2 w-full">
                            <label for="selectPiece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pièce</label>
                            <select
                                id="selectPiece"
                                v-model="selectedPiece"
                                aria-label="Sélectionner une pièce"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une pièce --</option>
                                <option
                                    v-for="piece in props.pieces"
                                    :key="piece.id_piece"
                                    :value="piece.id_piece"
                                >
                                    {{ piece.nom_piece }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2 w-full md:w-auto">
                            <label for="quantityPiece" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                            <input
                                id="quantityPiece"
                                v-model.number="quantityPiece"
                                type="number"
                                min="1"
                                aria-label="Quantité pièce"
                                class="w-full md:w-24 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <button
                            type="button"
                            @click="addPiece"
                            :disabled="!selectedPiece || quantityPiece < 1"
                            class="w-full md:w-auto px-4 py-2 rounded-lg transition flex items-center justify-center md:justify-start gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 disabled:opacity-50"
                        >
                            <Plus class="w-4 h-4" />
                            Ajouter Pièce
                        </button>
                    </div>

                    <div v-if="form.pieces.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pièce</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                            <tr v-for="(item, index) in form.pieces" :key="index">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    {{ props.pieces.find(p => p.id_piece == item.id_piece)?.nom_piece }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    <input
                                        v-model.number="item.qte_commandep"
                                        type="number"
                                        min="1"
                                        aria-label="Modifier quantité pièce"
                                        class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    <button
                                        type="button"
                                        @click="removePiece(index)"
                                        aria-label="Supprimer pièce"
                                        class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                        Aucune pièce sélectionnée.
                    </div>
                </div>

                <!-- Prestations Section -->
                <div class="space-y-4">
                    <h3 class="text-md font-medium text-gray-700 dark:text-gray-300">Prestations Commandées</h3>

                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1 space-y-2 w-full">
                            <label for="selectPrestation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prestation</label>
                            <select
                                id="selectPrestation"
                                v-model="selectedPrestation"
                                aria-label="Sélectionner une prestation"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une prestation --</option>
                                <option
                                    v-for="prestation in props.prestations"
                                    :key="prestation.id_prest"
                                    :value="prestation.id_prest"
                                >
                                    {{ prestation.nom_prest }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2 w-full md:w-auto">
                            <label for="quantityPrestation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                            <input
                                id="quantityPrestation"
                                v-model.number="quantityPrestation"
                                type="number"
                                min="1"
                                aria-label="Quantité prestation"
                                class="w-full md:w-24 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <button
                            type="button"
                            @click="addPrestation"
                            :disabled="!selectedPrestation || quantityPrestation < 1"
                            class="w-full md:w-auto px-4 py-2 rounded-lg transition flex items-center justify-center md:justify-start gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 disabled:opacity-50"
                        >
                            <Plus class="w-4 h-4" />
                            Ajouter Prestation
                        </button>
                    </div>

                    <div v-if="form.prestations.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prestation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                            <tr v-for="(item, index) in form.prestations" :key="index">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    {{ props.prestations.find(p => p.id_prest == item.id_prest)?.nom_prest }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    <input
                                        v-model.number="item.qte_commandepr"
                                        type="number"
                                        min="1"
                                        aria-label="Modifier quantité prestation"
                                        class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    <button
                                        type="button"
                                        @click="removePrestation(index)"
                                        aria-label="Supprimer prestation"
                                        class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                        Aucune prestation sélectionnée.
                    </div>
                </div>

                <!-- Charges Section -->
                <div class="space-y-4">
                    <h3 class="text-md font-medium text-gray-700 dark:text-gray-300">Charges Commandées</h3>

                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1 space-y-2 w-full">
                            <label for="selectCharge" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Charge</label>
                            <select
                                id="selectCharge"
                                v-model="selectedCharge"
                                aria-label="Sélectionner une charge"
                                class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">-- Sélectionnez une charge --</option>
                                <option
                                    v-for="charge in props.charges"
                                    :key="charge.id_charge"
                                    :value="charge.id_charge"
                                >
                                    {{ charge.nom_charge }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2 w-full md:w-auto">
                            <label for="quantityCharge" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité</label>
                            <input
                                id="quantityCharge"
                                v-model.number="quantityCharge"
                                type="number"
                                min="1"
                                aria-label="Quantité charge"
                                class="w-full md:w-24 border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <button
                            type="button"
                            @click="addCharge"
                            :disabled="!selectedCharge || quantityCharge < 1"
                            class="w-full md:w-auto px-4 py-2 rounded-lg transition flex items-center justify-center md:justify-start gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200 disabled:opacity-50"
                        >
                            <Plus class="w-4 h-4" />
                            Ajouter Charge
                        </button>
                    </div>

                    <div v-if="form.charges.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Charge</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                            <tr v-for="(item, index) in form.charges" :key="index">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    {{ props.charges.find(c => c.id_charge == item.id_charge)?.nom_charge }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    <input
                                        v-model.number="item.qte_commandec"
                                        type="number"
                                        min="1"
                                        aria-label="Modifier quantité charge"
                                        class="w-20 border border-gray-300 dark:border-gray-600 p-1 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    <button
                                        type="button"
                                        @click="removeCharge(index)"
                                        aria-label="Supprimer charge"
                                        class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                        Aucune charge sélectionnée.
                    </div>
                </div>



                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="destroyBonCommande"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-2 transition"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>
                    <div class="flex gap-4">
                        <Link
                            :href="route('scentre.boncommandes.index')"
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
