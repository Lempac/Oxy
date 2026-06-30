<script lang="ts" setup>
import { usePerms, fetchJson } from '@/bootstrap';

import { server } from '@/routes/home';
import { create, deleteMethod, edit, index } from '@/routes/roles';
import {ref} from 'vue';
import {Link} from '@inertiajs/vue3';
import {PermType, Role, Server} from "@/types";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import {bigIntToPerms} from "@/bootstrap";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { BiDownArrowAlt, BiUpArrowAlt } from 'vue-icons-plus/bi';
import { BsCheckLg } from 'vue-icons-plus/bs';


const perms = usePerms();
const {selectedServer} = defineProps<{
    selectedServer: Server,
}>();

const roles = ref<Role[]>([]);
const newRole = ref({
    name: '',
    color: '#ffffff',
    importance: 0,
    perms: [],
} as unknown as Role);

const editingRole = ref<Role | null>(null);
const isModalOpen = ref(false);

const fetchRoles = async () => {
    try {
        const response = await fetchJson(index.url(selectedServer?.route_key));
        roles.value = response.data ?? []; // Default to an empty array if undefined
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
    newRole.value.perms = [];
};

const editRole = (role: Role) => {
    editingRole.value = role;
    newRole.value = role;
};

const updateRole = async () => {
    const currentEditingRole = editingRole.value;

    if (currentEditingRole) {
        const response = await fetchJson(edit.url(currentEditingRole.id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newRole.value)
        });

        const index = roles.value.findIndex(r => r.id === currentEditingRole.id);
        roles.value[index] = response.data.role;

        editingRole.value = null;
        closeModal(); // Reset modal state
        await fetchRoles();
    }
};

const addRole = async () => {
    try {
        newRole.value.importance = 1;

        if (roles.value.length > 0) {
            const maxImportance = Math.max(...roles.value.map(role => role.importance));
            newRole.value.importance = maxImportance + 1;
        }

        await fetchJson(create.url(selectedServer?.route_key), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: newRole.value.name,
                color: newRole.value.color,
                perms: newRole.value.perms,
                importance: newRole.value.importance,
            })
        });

        closeModal();
        await fetchRoles();
    } catch (error) {
        console.error('Error adding role:', error);
    }
};

const deleteRole = async (role: Role) => {
    try {
        await fetchJson(deleteMethod.url(role.id), { method: 'DELETE' });

        roles.value = roles.value.filter(r => r.id !== role.id);

        roles.value.forEach(r => {
            if (r.importance > role.importance) {
                r.importance -= 1;
                fetchJson(edit.url(r.id), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({importance: r.importance})
                });
            }
        });

        roles.value.sort((a, b) => a.importance - b.importance);

    } catch (error) {
        console.error('Error deleting role:', error);
    }
};


// Fetch roles when the component mounts
fetchRoles();

const togglePerm = (perm: string, state: boolean) => {
    if (editingRole.value?.perms === undefined) return;
    const currentPerm = bigIntToPerms(editingRole.value?.perms || [])

    if (state) currentPerm.add(perm)
    else currentPerm.remove(perm)

    editingRole.value.perms = currentPerm.perms
};

const roleArray = Object.entries(PermType);

const formatRolePerms = (role: Role) => {

    const filtered = roleArray.filter(roleObj => role.perms.includes(roleObj[1]));
    const length = filtered.length;
    if (length === 0) return 'None';
    const firstThree = filtered.slice(0, 3).map(roleObj => roleObj[0]).join(', ');
    if (length > 3) {
        return `${firstThree} +${length - 3} others`;
    }
    return firstThree;
};

const changeImportance = async (role: Role, direction: number) => {
    const currentImportance = role.importance;
    const newImportance = currentImportance + direction;

    if (newImportance < 1 || newImportance >= roles.value.length) return;

    let swapRole: Role | undefined;
    if (direction === 1) {
        swapRole = roles.value.find(r => r.importance === currentImportance + 1);
    } else if (direction === -1) {
        swapRole = roles.value.find(r => r.importance === currentImportance - 1);
    }

    if (swapRole) {
        const tempImportance = role.importance;
        role.importance = swapRole.importance;
        swapRole.importance = tempImportance;

        await fetchJson(edit.url(role.id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({importance: role.importance})
        });
        await fetchJson(edit.url(swapRole.id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({importance: swapRole.importance})
        });
    }

    roles.value.sort((a, b) => a.importance - b.importance);
};


</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- Navbar for Navigation -->
            <SettingsHeader :selected-server/>
            <div class="flex justify-end mb-6 space-x-4">
                <Link :href="server.url(selectedServer?.route_key)" class="btn btn-neutral">
                    Cancel
                </Link>
            </div>

            <!-- Role Settings Content Section -->
            <div class="bg-base-200 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-base-content mb-6">Role Settings</h1>

                <!-- Button to Open Modal -->
                <button :disabled="!perms.has([PermType.CAN_CREATE_ROLE])" class="btn mb-6" @click="isModalOpen = true">
                    Add Role
                </button>

                <!-- Roles Table -->
                <table class="min-w-full bg-base-300 rounded-lg">
                    <thead class="bg-base-100">
                    <tr>
                        <th class="py-2 px-4 text-left text-base-content">Role Name</th>
                        <th v-if="editingRole" class="py-2 px-4 text-left text-base-content">Color</th>
                        <th class="py-2 px-4 text-left text-base-content">Importance</th>
                        <th class="py-2 px-4 text-left text-base-content">Perms</th>
                        <th class="py-2 px-4 text-end text-base-content">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="role in roles" :key="role.id">
                        <td :style="{ color: editingRole === role ? newRole.color : role.color }" class="py-2 px-4">
                            <span v-if="editingRole !== role">{{ role.name }}</span>
                            <input
                                v-if="editingRole === role" v-model="newRole.name" class="p-1 text-base-content bg-base-100"
                                type="text"/>
                        </td>
                        <td v-if="editingRole === role" class="py-2 px-4">
                            <input v-model="newRole.color" class="ml-2 bg-transparent" type="color"/>
                        </td>
                        <td class="py-2 px-4 text-base-content">
                            <span v-if="editingRole !== role">{{ role.importance }}</span>
                            <div
                                v-if="editingRole === role && role.importance !== 0"
                                class="flex justify-center space-x-2">
                                <button class="btn" @click="changeImportance(role, -1)">
                                    <BiUpArrowAlt/>
                                </button>
                                <button class="btn" @click="changeImportance(role, 1)">
                                    <BiDownArrowAlt/>
                                </button>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-base-content">
                            <div class="dropdown">
                                <button
                                    class="btn m-1"
                                    tabindex="0">
                                    {{ formatRolePerms(role) }}
                                </button>
                                <ul
                                    class="dropdown-content menu bg-base-100 rounded-box z-[1] p-2 shadow gap-y-1"
                                    tabindex="0">
                                    <template v-for="(rolePerms, rolePermsIdx) in [bigIntToPerms(role.perms)]" :key="'perms-' + role.id + '-' + rolePermsIdx">
                                        <li v-for="(perm, permIndex) in roleArray" :key="permIndex">
                                            <button
                                                :class="(rolePerms.has(perm[1]) ? 'bg-base-300' : '')"
                                                :disabled="editingRole !== role"
                                                class="btn"
                                                @click="() => togglePerm(perm[1], !rolePerms.has(perm[1]))"
                                            >
                                                <BsCheckLg
                                                    v-if="rolePerms.has(perm[1])"/>
                                                {{ perm[0] }}
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-end">
                            <div class="flex justify-end space-x-2">
                                <button
                                    v-if="editingRole !== role" :disabled="!perms.has([PermType.CAN_EDIT_ROLE])"
                                    class="btn hover:btn-info px-4 py-2 "
                                    @click="editRole(role)">Edit
                                </button>
                                <button
                                    v-if="editingRole === role" class="btn hover:btn-success px-4 py-2"
                                    @click="updateRole">Save
                                </button>
                                <ConfirmDialog
                                    :class-name="`btn hover:btn-error px-4 py-2 ${!perms.has([PermType.CAN_DELETE_ROLE]) ? 'btn-disabled' : ''}`"
                                    :confirm="() => deleteRole(role)"
                                    description="Are you sure you want to delete this role?"
                                    text="Delete" title="Are you sure?"/>
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
                                v-model="newRole.name"
                                class="input input-bordered grow"
                                required
                                type="text"
                            />
                        </div>
                        <div class="form-control">
                            <label class="label justify-start" for="newRoleColor">
                                Color
                                <span
                                    :style="{ backgroundColor: newRole.color }"
                                    class="ml-3 size-12 rounded-full border border-gray-300 cursor-pointer"
                                />
                            </label>
                            <input
                                id="newRoleColor"
                                v-model="newRole.color"
                                class="hidden"
                                type="color"
                            />
                        </div>
                        <!-- Removed Importance Field from the Modal Form -->
                        <div class="modal-action">
                            <button class="btn btn-success" @click="addRole">Add Role</button>
                            <button class="btn" @click="closeModal">Cancel</button>
                        </div>
                    </div>
                </dialog>
            </div>
        </div>
    </div>
</template>
