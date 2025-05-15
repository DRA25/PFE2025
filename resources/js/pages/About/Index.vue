<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Pencil, Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const { props } = usePage();

defineProps<{
    sections: Array<{
        id: number;
        section_title: string;
        section_content: string;
        image_path: string | null;
        order: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'À propos', href: '/about' },
];

const canEdit = computed(() => !!props.auth?.user);
</script>

<template>
    <Head title="À propos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-6 relative">

            <div class="absolute top-6 right-6 flex gap-3">
                <Link
                    v-if="canEdit"
                    :href="route('about.edit')"
                    class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition flex items-center gap-2"
                >
                    <Pencil class="w-5 h-5" />
                    <span>Modifier la page</span>
                </Link>

                <Link
                    v-if="canEdit"
                    :href="route('about.create')"
                    class="bg-green-600 dark:bg-green-400 text-white dark:text-[#042B62] px-4 py-2 rounded-lg hover:bg-green-700 dark:hover:bg-green-300 transition flex items-center gap-2"
                >
                    <Plus class="w-5 h-5" />
                    <span>Créer une section</span>
                </Link>
            </div>

            <div class="flex justify-between mb-6">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Contenu de la page À propos
                </h1>
            </div>

            <div class="space-y-8">
                <template v-for="section in sections" :key="section.id">
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                        <h1 class="text-2xl font-bold text-[#042B62FF] dark:text-[#F3B21B] mb-4">
                            {{ section.section_title }}
                        </h1>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            {{ section.section_content }}
                        </p>
                        <img
                            v-if="section.image_path"
                            :src="`/storage/${section.image_path}`"
                            alt="Image de la section"
                            class="w-full md:w-1/2 rounded-lg border-2 border-[#042B62] dark:border-[#F3B21B] object-cover"
                        />
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
