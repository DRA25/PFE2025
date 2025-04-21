<script setup lang="ts">
import { defineComponent } from 'vue';
import { Head,Link } from '@inertiajs/vue3';
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
    <AppLayout :breadcrumbs="breadcrumbs">

        <Table class="m-5 mt-0">
            <TableCaption class="text-lg font-bold text-left mb-5  text-[#042B62FF] dark:text-[#BDBDBDFF] ">Liste Des DRA</TableCaption>
            <TableHeader>
                <TableRow >
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]"> Numero DRA </TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">Periode</TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">Etat</TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">compte generaux</TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">compte analytique</TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">Debit</TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">Libelle</TableHead>
                    <TableHead class=" text-[#042B62FF] dark:text-[#BDBDBDFF]">Fournisseur</TableHead>



                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow>
                    <TableCell>A20001</TableCell>
                    <TableCell>01/01/25</TableCell>
                    <TableCell>Active</TableCell>
                    <TableCell>602250</TableCell>
                    <TableCell>098081</TableCell>
                    <TableCell>650.00</TableCell>
                    <TableCell>cadna</TableCell>
                    <TableCell>Bellil Abdelkrim</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>A20001</TableCell>
                    <TableCell>01/01/25</TableCell>
                    <TableCell>Active</TableCell>
                    <TableCell>602250</TableCell>
                    <TableCell>098081</TableCell>
                    <TableCell>6500.00</TableCell>
                    <TableCell>support filtre a gas oil</TableCell>
                    <TableCell>Sarl Express Filtre</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>A20001</TableCell>
                    <TableCell>01/01/25</TableCell>
                    <TableCell>Active</TableCell>
                    <TableCell>602250</TableCell>
                    <TableCell>098081</TableCell>
                    <TableCell>6800.00</TableCell>
                    <TableCell>tresse volant</TableCell>
                    <TableCell>Sarl Cumi</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </AppLayout>
</template>
