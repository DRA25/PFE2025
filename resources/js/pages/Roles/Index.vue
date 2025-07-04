<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button/index.js';
import AppLayout from '@/layouts/AppLayout.vue';

import { TableCaption, TableHeader, TableBody, TableRow, TableCell, TableHead, Table } from '@/components/ui/table'; // Import Table component
import { watchEffect } from 'vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Espace admin', href: '/espace-admin' },
    { title: 'Role', href: '/roles' },


];

const props = defineProps({ users: Array, roles: Array, success: String });


// Initialize user.role for each user on mounted
watchEffect(() => {
    props.users.forEach(user => {
        if (Array.isArray(user.roles) && user.roles.length > 0) {
            // Ensure user.role is just the name of the first role
            user.role = typeof user.roles[0] === 'string' ? user.roles[0] : user.roles[0].name;
        } else {
            user.role = null; // No role assigned
        }
    });
});

function updateRoles(user, roles) {
    // Ensure roles is always an array, even for single select
    const rolesToAssign = Array.isArray(roles) ? roles : [roles];

    router.post(route('roles.assign', user.id), {
        roles: rolesToAssign,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Rôles mis à jour');
            // Optimistically update the user's roles array after successful assignment
            // This assumes a single role assignment per user through the select.
            user.roles = rolesToAssign.map(roleName => props.roles.find(r => r.name === roleName));
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}

function removeUserRole(user, role) {
    const roleName = typeof role === 'string' ? role : role.name;
    router.post(route('roles.remove', {
        user: user.id,
        role: roleName
    }))
        .then(() => {
            // Filter out the removed role from the user's local roles array
            user.roles = user.roles.filter(r => (typeof r === 'string' ? r : r.name) !== roleName);
            // If the removed role was the selected one, reset the select
            if (user.role === roleName) {
                user.role = null;
            }
        })
        .catch(error => console.error(error));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Gestion des rôles" />

        <div class="min-h-screen bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-gray-900 font-inter text-gray-800 dark:text-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden transition-all duration-500">
                <div class="p-6 sm:p-8 lg:p-10">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-8 text-[#042B62] dark:text-[#F3B21B] text-center tracking-tight leading-tight">
                        Gestion des Rôles Utilisateurs
                    </h1>

                    <div v-if="success" class="bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 p-4 rounded-lg mb-6 shadow-md border border-green-200 dark:border-green-700">
                        {{ success }}
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                        <Table class="w-full">

                            <TableHeader class="bg-gray-100 dark:bg-gray-700">
                                <TableRow class="border-b border-gray-200 dark:border-gray-600">
                                    <TableHead class="p-4 text-gray-700 dark:text-gray-200 text-left font-semibold">Utilisateur</TableHead>
                                    <TableHead class="p-4 text-gray-700 dark:text-gray-200 text-left font-semibold">Sélectionner un rôle</TableHead>
                                    <TableHead class="p-4 text-gray-700 dark:text-gray-200 text-left font-semibold">Rôles attribués</TableHead>
                                    <TableHead class="p-4 text-gray-700 dark:text-gray-200 text-left font-semibold">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="user in users" :key="user.id" class="border-b border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-750 transition-colors duration-200">
                                    <TableCell class="p-4 text-gray-900 dark:text-gray-100 font-medium">{{ user.name }}</TableCell>
                                    <TableCell class="p-4">
                                        <select
                                            v-model="user.role"
                                            @change="updateRoles(user, user.role)"
                                            class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 shadow-sm"
                                        >
                                            <option :value="null">-- Choisir un rôle --</option>
                                            <option
                                                v-for="role in props.roles"
                                                :key="role.id"
                                                :value="role.name"
                                            >
                                                {{ role.name }}
                                            </option>
                                        </select>
                                    </TableCell>
                                    <TableCell class="p-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role.id || role"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                       bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100
                                                       shadow-sm border border-blue-200 dark:border-blue-800"
                                            >
                                                {{ typeof role === 'string' ? role : role.name }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="p-4">
                                        <div class="flex flex-wrap gap-2">
                                            <Button
                                                v-for="role in user.roles"
                                                :key="role + '-delete'"
                                                @click="removeUserRole(user, role)"
                                                class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white
                                                       px-4 py-2 rounded-lg text-sm font-semibold shadow-md transition-colors duration-200
                                                       focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                            >
                                                Supprimer Rôle
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
