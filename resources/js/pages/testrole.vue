<script setup lang="ts">
import { defineComponent } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { TableCaption, TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';




const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'DRA',
        href: '/dra',
    },

];
</script>

<template>
    <Head title="DRA" />

       <app-layout>
           <h1 class="ml-5">only me the admin can see this page</h1>
       </app-layout>

</template>
