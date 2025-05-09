<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Pencil, Plus, Trash2, Upload } from 'lucide-vue-next';

const props = defineProps<{
    sections: Array<{
        id: number;
        section_title: string;
        section_content: string;
        image_path: string|null;
        order: number;
    }>;
}>();

const form = useForm({
    sections: props.sections.map(section => ({
        id: section.id,
        section_title: section.section_title,
        section_content: section.section_content,
        image: null as File|null,
        image_path: section.image_path,
        order: section.order,
        _method: 'put' // For Laravel to recognize as PUT request
    }))
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'About', href: '/about' },
    { title: 'Edit About', href: '/about/edit' },
];

const addSection = () => {
    form.sections.push({
        id: 0, // New sections will have ID 0
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
    <Head title="Edit About" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <form @submit.prevent="form.put(route('about.update'))">
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
                        <!-- Section Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                            <input
                                v-model="section.section_title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <!-- Section Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Content</label>
                            <textarea
                                v-model="section.section_content"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            ></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Image</label>
                            <div class="mt-1 flex items-center">
                                <img
                                    v-if="section.image_path"
                                    :src="`/storage/${section.image_path}`"
                                    class="h-20 w-20 object-cover rounded-md mr-4"
                                >
                                <input
                                    type="file"
                                    @change="handleImageChange($event, index)"
                                    class="hidden"
                                    :id="`image-upload-${index}`"
                                    accept="image/*"
                                >
                                <label
                                    :for="`image-upload-${index}`"
                                    class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                                >
                                    <Upload class="w-4 h-4 mr-2" />
                                    {{ section.image_path ? 'Change Image' : 'Upload Image' }}
                                </label>
                                <span v-if="section.image" class="ml-2 text-sm text-gray-500">
                                    {{ section.image.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 dark:text-gray-200">Display Order</label>
                            <input
                                v-model="section.order"
                                type="number"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                        Add Section
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        <Pencil class="w-4 h-4 mr-2" />
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
