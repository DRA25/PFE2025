<script setup>
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
        // Set the initial role for each user if roles are available
        if (Array.isArray(user.roles) && user.roles.length > 0) {
            user.role = typeof user.roles[0] === 'string' ? user.roles[0] : user.roles[0].name;
        } else {
            user.role = null; // No role assigned
        }
    });
});



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
                    <div>
                        <!-- Single-role radio group -->
                        <label v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2 my-1">
                            <input
                                type="radio"
                                :value="role.name"
                                v-model="user.role"
                                :name="'user-role-' + user.id"
                            @change="updateRoles(user, [user.role])"
                            class="accent-blue-600"
                            />
                            <span>{{ role.name }}</span>
                        </label>
                    </div>
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
                            Remove Role
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
            </TableBody>
        </Table>
</div>



    </AppLayout>
</template>

