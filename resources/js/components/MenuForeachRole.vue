<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';


const page = usePage();
const userHasRole = computed(() => page.props.userHasRole as string);

defineProps<{

    userHasRole?: string;
}>();
</script>

<template>
    <div v-if="userHasRole === 'service atelier'|| userHasRole === 'admin'">
    <ul class="flex justify-center gap-5 ">
        <Link href="/demandepiece"><li class="p-1.5 hover:bg-gray-300 dark:hover:bg-[#042B62] cursor-pointer rounded ">demande une piece</li></Link>
        <li class="p-1.5 hover:bg-gray-300 dark:hover:bg-[#042B62] cursor-pointer rounded ">demande une charge</li>
        <li class="p-1.5 hover:bg-gray-300 dark:hover:bg-[#042B62] cursor-pointer rounded ">demande une prestation</li>

    </ul>
    </div>

</template>
