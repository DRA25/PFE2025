<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    prestation: {
        id_prest: number;
        nom_prest: string;
        desc_prest: string;
        date_prest: string;
        tva: number;
        compte_general_code: string;
        compte_analytique_code: string;
    };
    comptesGeneraux: { code: string; libelle: string }[];
    comptesAnalytiques: { code: string; libelle: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Atelier', href: route('atelier.index') },
    { title: 'Prestations', href: route('scentre.prestations.index') },
    { title: 'Modifier', href: route('scentre.prestations.edit', props.prestation.id_prest) },
];

const form = useForm({
    id_prest: props.prestation.id_prest,
    nom_prest: props.prestation.nom_prest,
    desc_prest: props.prestation.desc_prest,
    date_prest: props.prestation.date_prest,
    tva: props.prestation.tva,
    compte_general_code: props.prestation.compte_general_code || '',
    compte_analytique_code: props.prestation.compte_analytique_code || '',
});

function submit() {
    form.put(route('scentre.prestations.update', props.prestation.id_prest));
}

function destroyPrestation() {
    if (confirm("Êtes-vous sûr de vouloir supprimer cette prestation ?")) {
        form.delete(route('scentre.prestations.destroy', form.id_prest), {
            onSuccess: () => {
                window.location.href = route('scentre.prestations.index');
            },
            onError: () => {
                console.log("Erreur lors de la suppression.");
            }
        });
    }
}
</script>

<template>
    <Head title="Modifier Prestation" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <h1 class="text-lg font-bold mb-6 text-[#042B62FF] dark:text-[#BDBDBDFF]">
                Modifier la prestation #{{ form.id_prest }}
            </h1>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                <div class="space-y-2">
                    <label for="id_prest" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Prestation</label>
                    <input
                        id="id_prest"
                        v-model="form.id_prest"
                        type="number"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        readonly
                    />
                </div>

                <div v-for="(label, field) in {
                    nom_prest: 'Nom',
                    desc_prest: 'Description',
                    date_prest: 'Date',
                    tva: 'TVA (%)'
                }" :key="field" class="space-y-2">
                    <label :for="field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ label }}
                    </label>
                    <textarea
                        v-if="field === 'desc_prest'"
                        v-model="form[field]"
                        :id="field"
                        rows="4"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        :placeholder="field === 'nom_prest' ? 'Nom de la prestation' : field === 'desc_prest' ? 'Description de la prestation' : ''"
                    ></textarea>
                    <input
                        v-else
                        v-model="form[field]"
                        :id="field"
                        :type="field === 'tva' ? 'number' : (field === 'date_prest' ? 'date' : 'text')"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        :step="field === 'tva' ? 0.01 : undefined"
                        :min="field === 'tva' ? 0 : undefined"
                        :max="field === 'tva' ? 100 : undefined"
                        :placeholder="field === 'nom_prest' ? 'Nom de la prestation' : field === 'tva' ? 'TVA en %' : ''"
                    />
                    <div v-if="form.errors[field]" class="text-red-500 text-sm">{{ form.errors[field] }}</div>
                </div>

                <div class="space-y-2">
                    <label for="compte_general_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Compte Général
                    </label>
                    <select
                        v-model="form.compte_general_code"
                        id="compte_general_code"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un compte général --</option>
                        <option
                            v-for="compte in props.comptesGeneraux"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <div v-if="form.errors.compte_general_code" class="text-red-500 text-sm">{{ form.errors.compte_general_code }}</div>
                </div>

                <div class="space-y-2">
                    <label for="compte_analytique_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Compte Analytique
                    </label>
                    <select
                        v-model="form.compte_analytique_code"
                        id="compte_analytique_code"
                        class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded focus:ring-2 focus:ring-[#042B62] dark:focus:ring-[#F3B21B] focus:border-transparent dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Sélectionnez un compte analytique --</option>
                        <option
                            v-for="compte in props.comptesAnalytiques"
                            :key="compte.code"
                            :value="compte.code"
                        >
                            {{ compte.code }} - {{ compte.libelle }}
                        </option>
                    </select>
                    <div v-if="form.errors.compte_analytique_code" class="text-red-500 text-sm">{{ form.errors.compte_analytique_code }}</div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button
                        type="button"
                        @click="destroyPrestation"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-2"
                    >
                        <Trash2 class="w-4 h-4" />
                        Supprimer
                    </button>
                    <div class="flex gap-4">
                        <Link
                            :href="route('scentre.prestations.index')"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
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
