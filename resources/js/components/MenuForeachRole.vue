<script setup lang="ts">
import { Button } from '@/components/ui/button';

import { computed } from 'vue';

import { Link, usePage } from '@inertiajs/vue3';

const currentRoute = usePage().url;

const page = usePage();
const userHasRole = computed(() => page.props.userHasRole as string);

defineProps<{

    userHasRole?: string;
}>();
</script>

<template>
    <div v-if="userHasRole === 'service atelier'|| userHasRole === 'admin'">
        <ul class="flex justify-center gap-5">
            <Link
                href="/atelier"
                :class="[
          'p-1.5 rounded cursor-pointer transition',
          currentRoute === '/atelier' ? 'bg-gray-300 dark:bg-gray-950' : 'hover:bg-gray-100 dark:hover:bg-gray-800'
        ]"
            >
                <li>Atelier</li>
            </Link>

            <Link
                href="/demandepiece"
                :class="[
          'p-1.5 rounded cursor-pointer transition',
          currentRoute === '/demandepiece' ? 'bg-gray-300 dark:bg-gray-950' : 'hover:bg-gray-100 dark:hover:bg-gray-800'
        ]"
            >
                <li>demande une piece</li>
            </Link>

            <li class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer rounded">demande une charge</li>
            <li class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer rounded">demande une prestation</li>
        </ul>
    </div>

</template>
