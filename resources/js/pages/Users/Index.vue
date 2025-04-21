<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button/index.js';
import AppLayout from '@/layouts/AppLayout.vue';
import breadcrumbs from '@/components/Breadcrumbs.vue';
import { TableCaption, TableHeader, TableBody, TableRow, TableCell, TableHead } from '@/components/ui/table';
import { isRowSelected } from '@tanstack/vue-table';

const props = defineProps({ users: Array, roles: Array, success: String });

function updateRoles(user, roles) {
    router.post(route('users.assignRoles', user.id), {
        roles: roles, // Send the selected roles as an array of role names
    }, {
        preserveScroll: true, // Keep the page scroll position
        onSuccess: () => {
            // Optionally handle success actions here (e.g., update UI or show message)
            console.log('Roles updated');
        },
        onError: (errors) => {
            console.log(errors); // Log any errors if they occur
        }
    });
}

const hasRole = (roleName) => props.auth?.user?.roles?.some(r => r.name === roleName);

function removeUserRole(user, role) {
    // Send request to backend to remove role
    router.post(route('users.removeRole', { user: user.id, role: role.name }))
        .then(() => {
            // After the response, update the local user roles list
            user.roles = user.roles.filter(r => r !== role.name);
        })
        .catch(error => console.error(error));
}
</script>


<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Roles" />

        <!-- Success message (if any) -->
        <div v-if="success" class="alert alert-success text-red-600 mb-2">
            {{ success }}
        </div>
<div class=" m-5  bg-[#dedede] dark:bg-[#042B62] rounded-lg" >
        <Table class=" m-3  w-39/40 ">

            <TableCaption class="text-lg font-bold text-left mb-5 ml-3 text-[#042B62FF] dark:text-[#BDBDBDFF] ">User Role Manager</TableCaption>
            <TableHeader>
            <TableRow class="border-b ">
                <TableHead class="p-2">User</TableHead>
                <TableHead class="p-2">Roles</TableHead>
                <TableHead class="p-2">Assigned Roles</TableHead>
                <TableHead class="p-2">Actions</TableHead>
            </TableRow>
            </TableHeader>
            <TableBody>
            <!-- Loop through each user -->
            <TableRow v-for="user in users" :key="user.id" class="border-b">
                <TableCell class="p-2">{{ user.name }}</TableCell>
                <TableCell class="p-2">
                    <!-- select role -->
                    <select multiple
                            v-model="user.roles"
                            @change="updateRoles(user, user.roles)"
                            class="bg-white rounded-lg p-2 dark:bg-[#070738] ">

                        <option v-for="role in roles"
                                :key="role.id"
                                :value="role.name"
                                class="hover:bg-gray-200 hover:dark:bg-[#042B62] rounded border-b" >
                            {{ role.name }}
                        </option>
                    </select>
                </TableCell>
                <TableCell class="p-2">
  <span
      v-for="role in user.roles"
      :key="role.id || role.name"
      class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1"
  >
    {{ typeof role === 'string' ? role : role.name }}
  </span>

                </TableCell>
                <!-- Actions (Delete Button) -->
                <TableCell class="p-2">
                    <div class="space-y-1">
                        <!-- Delete button for each assigned role -->
                        <Button
                            v-for="role in user.roles"
                            :key="role + '-delete'"
                            @click="removeUserRole(user, role)"
                            class="text-white hover:underline text-xs bg-red-500 hover:bg-red-400"
                        >
                            Remove {{ role.name }}
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
            </TableBody>
        </Table>
</div>



    </AppLayout>
</template>

