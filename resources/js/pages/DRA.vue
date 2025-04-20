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
    <AppLayout :breadcrumbs="breadcrumbs">
        <Table>
            <TableCaption>Liste Des DRA</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead> Numero DRA </TableHead>
                    <TableHead>Periode</TableHead>
                    <TableHead>Etat</TableHead>
                    <TableHead>compte generaux</TableHead>
                    <TableHead>compte analytique</TableHead>
                    <TableHead>Debit</TableHead>
                    <TableHead>Libelle</TableHead>
                    <TableHead>Fournisseur</TableHead>



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
