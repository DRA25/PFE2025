<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import breadcrumbs from '@/components/Breadcrumbs.vue';
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
    <h1 class="text-red-600 text-2xl font-bold">Access non-Autorise 403</h1>
    </AppLayout>
</template>
