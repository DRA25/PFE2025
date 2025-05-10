<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Pencil, Trash2 } from 'lucide-vue-next';

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

const removeSection = async (index: number, sectionId: number) => {
    if (sectionId > 0) {
        // Existing section - delete from server
        if (confirm('Are you sure you want to delete this section?')) {
            try {
                await router.delete(route('about.destroy'), {
                    data: { id: sectionId },
                    preserveScroll: true
                });
                form.sections.splice(index, 1);
            } catch (error) {
                console.error('Error deleting section:', error);
            }
        }
    } else {
        // New section - just remove from form
        form.sections.splice(index, 1);
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
                            @click="removeSection(index, section.id)"
                            class="text-red-600 hover:text-red-800"
                            :disabled="form.processing"
                        >
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                            <input
                                v-model="section.section_title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Content</label>
                            <textarea
                                v-model="section.section_content"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Image</label>
                            <div class="mt-1">
                                <img
                                    v-if="section.image_path"
                                    :src="`/storage/${section.image_path}`"
                                    class="h-20 w-auto object-contain rounded-md"
                                >
                                <span v-else class="text-gray-500">No image uploaded</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Display Order</label>
                            <input
                                v-model="section.order"
                                type="number"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
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
