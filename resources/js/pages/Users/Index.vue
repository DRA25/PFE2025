<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Plus, Trash2, ArrowUpDown, Search } from 'lucide-vue-next';
import {
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead
} from '@/components/ui/table';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Espace admin', href: '/espace-admin' },
    { title: 'Gestion des Utilisateurs', href: '/users' },
];

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

const searchQuery = ref('');
const sortConfig = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);

const requestSort = (column: string) => {
    if (!sortConfig.value || sortConfig.value.column !== column) {
        sortConfig.value = { column, direction: 'asc' };
    } else {
        sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
    }
}

const sortedUsers = computed(() => {
    let data = [...props.users];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(user =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            (user.centre?.id_centre?.toLowerCase().includes(query) ?? false) ||
            user.roles.some(role => role.name.toLowerCase().includes(query))
        );
    }

    if (sortConfig.value) {
        const { column, direction } = sortConfig.value;
        data.sort((a, b) => {
            let valA, valB;
            if (column === 'roles') {
                valA = a.roles.map(role => role.name).join(', ');
                valB = b.roles.map(role => role.name).join(', ');
            } else if (column === 'centre') {
                valA = a.centre?.id_centre ?? '';
                valB = b.centre?.id_centre ?? '';
            }
            else {
                valA = a[column as keyof typeof a] ?? '';
                valB = b[column as keyof typeof b] ?? '';
            }
            return direction === 'asc'
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        });
    }

    return data;
});

const deleteUser = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        router.delete(route('users.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (errors) => {
                alert('Erreur lors de la suppression de l’utilisateur : ' + (errors.message || 'Une erreur s’est produite'));
            }
        });
    }
}
</script>

<template>
    <Head title="Liste des utilisateurs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center m-5 mb-0 gap-4 flex-wrap">
            <div class="flex items-center gap-2 w-full md:w-1/3">
                <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher par nom, email, centre ou rôle..."
                    class="w-full bg-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
            </div>

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
                        <TableHead class="cursor-pointer" @click="requestSort('name')">
                            Nom
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('email')">
                            E-mail
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('created_at')">
                            Date de création
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('centre')">
                            Centre
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead class="cursor-pointer" @click="requestSort('roles')">
                            Rôle
                            <ArrowUpDown class="ml-2 h-4 w-4 inline-block" />
                        </TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="user in sortedUsers"
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
