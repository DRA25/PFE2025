<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import breadcrumbs from '@/components/Breadcrumbs.vue';
</script>

<template >
    <app-layout :breadcrumbs="breadcrumbs">
    <div class="text-center mt-20 dark:bg-[#121212] w-full h-full">
        <h1 class="text-4xl font-bold text-red-600">403 - Access Denied</h1>
        <p class="mt-4 text-lg text-red-600">You do not have permission to access this page.</p>
    </div>
    </app-layout>
</template>
