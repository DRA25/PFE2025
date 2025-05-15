<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button/index.js';
import AppLayout from '@/layouts/AppLayout.vue';
import breadcrumbs from '@/components/Breadcrumbs.vue';
import { TableCaption, TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { watchEffect } from 'vue';

const props = defineProps({ users: Array, roles: Array, success: String });

// Initialize user.role for each user on mounted
watchEffect(() => {
    props.users.forEach(user => {
        if (Array.isArray(user.roles) && user.roles.length > 0) {
            user.role = typeof user.roles[0] === 'string' ? user.roles[0] : user.roles[0].name;
        } else {
            user.role = null;
        }
    });
});

function updateRoles(user, roles) {
    router.post(route('roles.assign', user.id), {
        roles: roles,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Rôles mis à jour');
        },
        onError: (errors) => {
            console.log(errors);
        }
    });
}

function removeUserRole(user, role) {
    router.post(route('roles.remove', {
        user: user.id,
        role: typeof role === 'string' ? role : role.name
    }))
        .then(() => {
            user.roles = user.roles.filter(r => r !== (typeof role === 'string' ? role : role.name));
        })
        .catch(error => console.error(error));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Gestion des rôles" />

        <div v-if="success" class="alert alert-success text-green-600 mb-2">
            {{ success }}
        </div>

        <div class="m-5 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <Table class="m-3 w-39/40">
                <TableCaption class="text-lg font-bold text-left mb-5 ml-3 text-[#042B62FF] dark:text-[#BDBDBDFF]">
                    Gestion des rôles des utilisateurs
                </TableCaption>
                <TableHeader>
                    <TableRow class="border-b">
                        <TableHead class="p-2">Utilisateur</TableHead>
                        <TableHead class="p-2">Rôles disponibles</TableHead>
                        <TableHead class="p-2">Rôles attribués</TableHead>
                        <TableHead class="p-2">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users" :key="user.id" class="border-b">
                        <TableCell class="p-2">{{ user.name }}</TableCell>
                        <TableCell class="p-2">
                            <div class="space-y-2">
                                <div class="space-y-1">
                                    <label
                                        v-for="role in props.roles"
                                        :key="role.id"
                                        class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                                    >
                                        <input
                                            type="radio"
                                            :value="role.name"
                                            v-model="user.role"
                                            :name="'user-role-' + user.id"
                                            @change="updateRoles(user, [user.role])"
                                            class="accent-blue-800 dark:accent-yellow-600"
                                        />
                                        <span class="text-sm text-gray-800 dark:text-gray-100 capitalize">
                                            {{ role.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell class="p-2">
                            <span
                                v-for="role in user.roles"
                                :key="role.id || role"
                                class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1 mb-1"
                            >
                                {{ typeof role === 'string' ? role : role.name }}
                            </span>
                        </TableCell>
                        <TableCell class="p-2">
                            <div class="flex flex-wrap gap-1">
                                <Button
                                    v-for="role in user.roles"
                                    :key="role + '-delete'"
                                    @click="removeUserRole(user, role)"
                                    class="text-white hover:underline text-xs bg-red-500 hover:bg-red-400"
                                >
                                    Supprimer
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
