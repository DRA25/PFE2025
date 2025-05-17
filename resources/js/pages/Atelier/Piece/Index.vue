<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    pieces: {
        id_piece: number;
        nom_piece: string;
        prix_piece: number;
        marque_piece: string;
        ref_piece: string;
    }[]
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const userContext = computed(() => {
    const roles = user.value?.roles?.map(r => r.name) || [];

    // Explicit check for admin first
    if (roles.includes('admin')) {
        return {
            type: 'admin' as const,
            base: 'atelier', // or 'magasin' depending on your admin default
            title: 'Administrateur'
        };
    }

    // Then check service roles
    if (roles.includes('service atelier')) {
        return {
            type: 'atelier' as const,
            base: 'atelier',
            title: 'Atelier'
        };
    }

    if (roles.includes('service magasin')) {
        return {
            type: 'magasin' as const,
            base: 'magasin',
            title: 'Magasin'
        };
    }

    // Fallback (shouldn't happen if auth is properly set up)
    return {
        type: 'unknown' as const,
        base: 'atelier',
        title: 'Système'
    };
});

// Usage in breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: userContext.value.title,
        href: route(`${userContext.value.base}.index`)
    },
    {
        title: 'Pièces',
        href: route(`${userContext.value.base}.pieces.index`)
    }
]);

// Usage in template
function deletePiece(id: number) {
    router.delete(route(`${userContext.value.base}.pieces.destroy`, id));
}



</script>

<template>
    <Head :title="isServiceAtelier ? 'Liste des Pièces (Atelier)' : 'Liste des Pièces (Magasin)'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="isServiceAtelier ? route('atelier.pieces.create') : route('magasin.pieces.create')"
                class="bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white px-4 py-2 rounded-lg hover:bg-blue-900 dark:hover:bg-yellow-200 transition"
            >
                Ajouter une Pièce
            </Link>
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des Pièces
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead>ID</TableHead>
                        <TableHead>Nom</TableHead>
                        <TableHead>Prix</TableHead>
                        <TableHead>Marque</TableHead>
                        <TableHead>Référence</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="piece in pieces" :key="piece.id_piece">
                        <TableCell>{{ piece.id_piece }}</TableCell>
                        <TableCell>{{ piece.nom_piece }}</TableCell>
                        <TableCell>{{ piece.prix_piece }} DA</TableCell>
                        <TableCell>{{ piece.marque_piece }}</TableCell>
                        <TableCell>{{ piece.ref_piece }}</TableCell>
                        <TableCell class="flex space-x-2">
                            <Link
                                :href="isServiceAtelier ? route('atelier.pieces.edit', piece.id_piece) : route('magasin.pieces.edit', piece.id_piece)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>
                            <button
                                @click.stop="deletePiece(piece.id_piece)"
                                class="bg-red-800 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition flex items-center gap-1"
                            >
                                <Trash2 class="w-4 h-4" />
                                <span>Supprimer</span>
                            </button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
