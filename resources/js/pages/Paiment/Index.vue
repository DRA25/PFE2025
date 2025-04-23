<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import breadcrumbs from '@/components/Breadcrumbs.vue';
</script>

<template >
    <app-layout :breadcrumbs="breadcrumbs">
        <div class="text-center mt-20  w-full h-full">
            <h1 class="text-4xl font-bold text-blue-600">paiment Page</h1>

        </div>
    </app-layout>
</template>
