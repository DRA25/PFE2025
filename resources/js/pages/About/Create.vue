<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Upload, Pencil, Trash2 } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'À propos', href: '/about' },
    { title: 'Créer une section À propos', href: '/about/create' },
];

const form = useForm({
    sections: [
        {
            id: 0,
            section_title: '',
            section_content: '',
            image: null as File | null,
            image_path: null,
            order: 0,
            _method: 'post'
        }
    ]
});

const addSection = () => {
    form.sections.push({
        id: 0,
        section_title: '',
        section_content: '',
        image: null,
        image_path: null,
        order: form.sections.length,
        _method: 'post'
    });
};

const removeSection = (index: number) => {
    form.sections.splice(index, 1);
};

const handleImageChange = (event: Event, index: number) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.sections[index].image = input.files[0];
    }
};
</script>

<template>
    <Head title="Créer une section À propos" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <form @submit.prevent="form.post(route('about.store'))">
                <div v-for="(section, index) in form.sections" :key="index" class="mb-8 p-4 border rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Section {{ index + 1 }}</h3>
                        <button
                            type="button"
                            @click="removeSection(index)"
                            class="text-red-600 hover:text-red-800"
                        >
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Titre</label>
                            <input
                                v-model="section.section_title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Contenu</label>
                            <textarea
                                v-model="section.section_content"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Image</label>
                            <div class="mt-1 flex items-center">
                                <input
                                    type="file"
                                    @change="handleImageChange($event, index)"
                                    class="hidden"
                                    :id="`image-upload-${index}`"
                                    accept="image/*"
                                >
                                <label
                                    :for="`image-upload-${index}`"
                                    class="cursor-pointer bg-white py-2 px-3 border border-gray-300 dark:border-gray-100 rounded-md shadow-sm dark:text-gray-700  text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                                >
                                    <Upload class="w-4 h-4 mr-2 dark:text-gray-700" />
                                    Télécharger une image
                                </label>
                                <span v-if="section.image" class="ml-2 text-sm text-gray-500 dark:text-gray-200">
                                    {{ section.image.name }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Ordre d'affichage</label>
                            <input
                                v-model="section.order"
                                type="number"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300  shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button
                        type="button"
                        @click="addSection"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        Ajouter une section
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        <Pencil class="w-4 h-4 mr-2" />
                        Enregistrer les sections
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
