<script setup lang="ts">
import {ref} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';
import axios, {AxiosResponse} from 'axios';
import {Role} from "@/types";

const server = usePage().props.selected_server;

const roles = ref<Role[]>([]);
const newRole = ref({
    name: '',
    color: '#ffffff',
    importance: 0,
    perms: 0,
} as Role);
const editingRole = ref<Role | null>(null);
const isModalOpen = ref(false);

const fetchRoles = async () => {
    try {
        const response: AxiosResponse<Role[] | null> = await axios.get(route('roles.index', {server: server?.id}));
        roles.value = response.data ?? []; // Default to empty array if undefined
        // Sort roles by importance
        roles.value.sort((a, b) => a.importance - b.importance);
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    newRole.value.name = '';
    newRole.value.color = '#ffffff'; // Reset color
    newRole.value.importance = 0; // Reset importance
    newRole.value.perms = 0;
};

const editRole = (role: Role) => {
    editingRole.value = role;
    newRole.value = role;
};

const updateRole = async () => {
    const currentEditingRole = editingRole.value;

    if (currentEditingRole) {
        const response = await axios.patch(route('roles.edit', {role: currentEditingRole.id}), newRole.value);

        const index = roles.value.findIndex(r => r.id === currentEditingRole.id);
        roles.value[index] = response.data.role;

        editingRole.value = null;
        closeModal(); // Reset modal state
    }
};

const addRole = async () => {
    try {
        await axios.post(route('roles.create', {server: server?.id}), {
            name: newRole.value.name,
            color: newRole.value.color,
            perms: newRole.value.perms,
            importance: newRole.value.importance,
        });
        closeModal();
        await fetchRoles(); // Refresh the roles list after adding
    } catch (error) {
        console.error('Error adding role:', error);
    }
};

const deleteRole = async (role: Role) => {
    try {
        await axios.delete(route('roles.delete', {role: role.id}));
        // Remove the role from the local state after deletion
        roles.value = roles.value.filter(r => r.id !== role.id);
    } catch (error) {
        console.error('Error deleting role:', error);
    }
};

const updateColor = (event: Event) => {
    console.log(event.target)
    const target = event.target as HTMLInputElement;
    newRole.value.color = target.value;
};

// Fetch roles when component mounts
fetchRoles();

</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- Navbar for Navigation -->
            <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
                <div class="flex-1">
                    <h1 class="text-2xl truncate" :title="server?.name" style="max-width: 50%;">{{ server?.name }}</h1>
                </div>
                <div class="flex space-x-6">
                    <Link :href="route('settings.server', { serverId: server?.id })"
                          class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                        Server
                    </Link>
                    <Link :href="route('settings.role', { id: server?.id })"
                          class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                        Roles
                    </Link>
                </div>
            </div>

            <div class="flex justify-end mb-6 space-x-4">
                <Link :href="route('home.server', { server: server?.id })" class="btn btn-circle bg-red-500">X
                </Link>
            </div>

            <!-- Role Settings Content Section -->
            <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-white mb-6">Role Settings</h1>

                <!-- Button to Open Modal -->
                <button @click="isModalOpen = true" class="btn mb-6">Add Role</button>

                <!-- Roles Table -->
                <table class="min-w-full bg-gray-100 rounded-lg">
                    <thead class="bg-gray-500">
                    <tr>
                        <th class="py-2 px-4 text-left text-white">Role Name</th>
                        <th class="py-2 px-4 text-left text-white" v-if="editingRole">Color</th>
                        <th class="py-2 px-4 text-left text-white">Importance</th>
                        <th class="py-2 px-4 text-white text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="role in roles" :key="role.id">
                        <td class="py-2 px-4" :style="{ color: editingRole === role ? newRole.color : role.color }">
                            <span v-if="editingRole !== role">{{ role.name }}</span>
                            <input v-if="editingRole === role" v-model="newRole.name" type="text"
                                   class="p-1 text-black bg-white"/>
                        </td>
                        <td v-if="editingRole === role" class="py-2 px-4">
                            <input v-model="newRole.color" type="color" class="ml-2 bg-transparent"/>
                        </td>
                        <td class="py-2 px-4 text-black">
                            <span v-if="editingRole !== role">{{ role.importance }}</span>
                            <input v-if="editingRole === role" v-model="newRole.importance" type="number" min="0"
                                   max="100" class="p-1 text-black"/>
                        </td>
                        <td class="py-2 px-4 text-right">
                            <div class="flex justify-end space-x-2">
                                <button v-if="editingRole !== role" @click="editRole(role)"
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Edit
                                </button>
                                <button v-if="editingRole === role" @click="updateRole"
                                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">Save
                                </button>
                                <button @click="deleteRole(role)"
                                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Modal for Adding Role -->
                <dialog v-if="isModalOpen" class="modal modal-open">
                    <div class="modal-box">
                        <h2 class="text-lg font-bold">Add New Role</h2>
                        <div class="form-control">
                            <label class="label" for="newRoleName">Role Name</label>
                            <input
                                id="newRoleName"
                                type="text"
                                v-model="newRole.name"
                                required
                                class="input input-bordered grow"
                            />
                        </div>
                        <div class="form-control">
                            <label class="label justify-start" for="newRoleColor">
                                Color
                                <span
                                    class="ml-3 size-12 rounded-full border border-gray-300 cursor-pointer"
                                    :style="{ backgroundColor: newRole.color }"
                                />
                            </label>
                            <input
                                id="newRoleColor"
                                type="color"
                                v-model="newRole.color"
                                class="hidden"
                            />
                        </div>
                        <div class="form-control">
                            <label class="label" for="newRoleImportance">Importance</label>
                            <input
                                id="newRoleImportance"
                                type="number"
                                v-model="newRole.importance"
                                min="0"
                                max="100"
                                required
                                class="input input-bordered grow"
                            />
                        </div>
                        <div class="modal-action">
                            <button @click="addRole" class="btn btn-success">Add Role</button>
                            <button @click="closeModal" class="btn btn-error">Cancel</button>
                        </div>
                    </div>
                </dialog>
            </div>
        </div>
    </div>
</template>
