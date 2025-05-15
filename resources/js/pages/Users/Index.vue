<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table';

const props = defineProps<{
    users: Array<{
        id: number,
        name: string,
        email: string,
        created_at: string,
        roles: Array<{
            id: number,
            name: string
        }>,
        centre: { // Add the centre property to the type definition
            id_centre: string | null
        } | null
    }>,
    success?: string
}>();

const deleteUser = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        router.delete(route('users.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (errors) => {
                alert('Erreur lors de la suppression de l’utilisateur : ' + (errors.message || 'Une erreur s’est produite'));
            }
        });
    }
}
</script>

<template>
    <Head title="Liste des utilisateurs" />
    <AppLayout>
        <div class="flex justify-end m-5 mb-0">
            <Link
                :href="route('users.create')"
                as="button"
                class="px-4 py-2 rounded-lg transition cursor-pointer flex items-center gap-1 bg-[#042B62] dark:bg-[#F3B21B] dark:text-[#042B62] text-white hover:bg-blue-900 dark:hover:bg-yellow-200"
            >
                <Plus class="w-4 h-4" />
                <span>Créer un utilisateur</span>
            </Link>
        </div>

        <div v-if="success" class="mx-5 mt-2 p-3 bg-green-100 text-green-800 rounded-lg">
            {{ success }}
        </div>

        <div class="m-5 mr-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="flex justify-between m-5">
                <h1 class="text-lg font-bold text-left text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Liste des utilisateurs
                </h1>
            </div>

            <Table class="m-3 w-39/40">
                <TableHeader>
                    <TableRow>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Nom</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">E-mail</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Date de création</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Centre</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Rôle</TableHead>
                        <TableHead class="text-[#042B62FF] dark:text-[#BDBDBDFF]">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="user in users"
                        :key="user.id"
                        class="hover:bg-gray-300 dark:hover:bg-gray-900"
                    >
                        <TableCell>{{ user.name }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>{{ new Date(user.created_at).toLocaleDateString('fr-FR') }}</TableCell>
                        <TableCell>{{ user.centre ? user.centre.id_centre : 'N/A' }}</TableCell>
                        <TableCell>
                            <div v-if="user.roles && user.roles.length" class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    class="inline-block bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 text-xs px-2 py-1 rounded"
                                >
                                    {{ role.name }}
                                </span>
                            </div>
                            <span v-else class="text-gray-500 text-sm">Aucun rôle attribué</span>
                        </TableCell>
                        <TableCell class="flex flex-wrap gap-2">
                            <Link
                                :href="route('users.edit', user.id)"
                                class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-400 transition flex items-center gap-1"
                            >
                                <Pencil class="w-4 h-4" />
                                <span>Modifier</span>
                            </Link>

                            <button
                                @click="deleteUser(user.id)"
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
