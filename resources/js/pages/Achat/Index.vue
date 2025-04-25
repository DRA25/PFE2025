<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';

import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Service Achat',
        href: '/achat',
    },
   

];
</script>

<template >
    <app-layout :breadcrumbs="breadcrumbs">
        <div class="text-center mt-20  w-full h-full">
            <h1 class="text-4xl font-bold text-blue-600">achat Page</h1>


        </div>
    </app-layout>
</template>
