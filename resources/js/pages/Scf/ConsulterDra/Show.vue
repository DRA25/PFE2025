<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { FileText } from 'lucide-vue-next';

const props = defineProps<{
    dra: {
        n_dra: string;
        date_creation: string;
        etat: string;
        total_dra: number;
        created_at: string;
    };
    errors?: {
        etat?: string;
    };
}>();

const form = useForm({
    etat: props.dra.etat.toLowerCase() // Ensure lowercase to match validation
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Service Coordination Finnanciere', href: route('scf.index') },
    { title: 'Consulter DRAs', href: route('scf.dras.index') },
    { title: `DRA ${props.dra.n_dra}`, href: route('scf.dras.show', { dra: props.dra.n_dra }) }
];

// Ensure these values exactly match your Laravel validation
const etatOptions = [
    { value: 'cloture', label: 'Clôturé' },
    { value: 'refuse', label: 'Refusé' },
    { value: 'accepte', label: 'Accepté' }
];

const submit = () => {
    form.put(route('scf.dras.update', { dra: props.dra.n_dra }), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('scf.dras.index'), {
                preserveScroll: true,
                preserveState: true
            });
        },
        onError: () => {
            // Errors will be automatically available in props.errors
        }
    });
};
</script>

<template>
    <Head :title="`Détails DRA ${dra.n_dra}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column - DRA Details -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-primary-600 dark:text-yellow-400">
                        Détails de la DRA
                    </h2>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">N° DRA</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ dra.n_dra }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date de création</p>
                        <p class="text-gray-900 dark:text-gray-100">{{ dra.date_creation }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total DRA</p>
                        <p class="text-gray-900 dark:text-gray-100">
                            {{ dra.total_dra.toLocaleString('fr-FR') }} DA
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">État actuel</p>
                        <p class="text-gray-900 dark:text-gray-100 font-bold"
                           :class="{
                               'text-green-600': dra.etat.toLowerCase() === 'accepte',
                               'text-blue-600': dra.etat.toLowerCase() === 'cloture',
                               'text-red-600': dra.etat.toLowerCase() === 'refuse'
                           }">
                            {{
                                dra.etat.toLowerCase() === 'accepte' ? 'Accepté' :
                                    dra.etat.toLowerCase() === 'cloture' ? 'Clôturé' :
                                        'Refusé'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Right Column - State Update Form -->
                <div>
                    <h2 class="text-xl font-semibold text-primary-600 dark:text-yellow-400 mb-4">
                        Mettre à jour l'état
                    </h2>

                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                État
                            </label>
                            <select
                                v-model="form.etat"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': errors?.etat }"
                                :disabled="form.processing"
                            >
                                <option
                                    v-for="option in etatOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <p v-if="errors?.etat" class="mt-2 text-sm text-red-600">
                                {{ errors.etat }}
                            </p>


                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Mettre à jour</span>
                            </button>
                            <Link
                                :href="route('scf.dras.index')"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                            >
                                Retour
                            </Link>

                        </div>
                    </form>
                    <h2 class="text-xl font-semibold text-primary-600 dark:text-yellow-400 mb-4">
                        Voir les facture et les bon d'achat
                    </h2>
                    <div class="flex justify-end gap-2 mt-10">

                    <Link

                        :href="route('scf.dras.factures.show', { dra: dra.n_dra })"
                        class="bg-[#042B62] dark:bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-200 transition flex items-center gap-1"
                    >
                        <FileText class="w-4 h-4" />
                        <span>Factures</span>
                    </Link>

                    <Link

                        :href="route('scf.dras.bon-achats.show', { dra: dra.n_dra })"
                        class="bg-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-200 transition flex items-center gap-1"
                    >
                        <FileText class="w-4 h-4" />
                        <span>Bons d'Achat</span>
                    </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
