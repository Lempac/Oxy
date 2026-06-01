<script lang="ts" setup>
import { server } from '@/routes/home';
import { create, deleteMethod, edit, index } from '@/routes/roles';
import {ref} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';
import axios, {AxiosResponse} from 'axios';
import {Perms, PermType, Role, Server} from "@/types";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import {bigIntToPerms} from "@/bootstrap";
import {addIcons} from "oh-vue-icons";
import {BiCheckLg, CoArrowBottom, CoArrowTop} from "oh-vue-icons/icons";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

addIcons(BiCheckLg, CoArrowBottom, CoArrowTop);

const {selectedServer} = defineProps<{
    selectedServer: Server,
}>();

const roles = ref<Role[]>([]);
const newRole = ref({
    name: '',
    color: '#ffffff',
    importance: 0,
    perms: "0",
} as Role);

const editingRole = ref<Role | null>(null);
const isModalOpen = ref(false);
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const fetchRoles = async () => {
    try {
        const response: AxiosResponse<Role[] | null> = await axios.get(index.url(selectedServer?.id));
        roles.value = response.data ?? []; // Default to an empty array if undefined
        // Sort roles by importance
        roles.value.sort((a, b) => a.importance - b.importance);

        if (selectedServer && selectedServer.roles !== null) {
            perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleObj => roleObj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
        }
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    newRole.value.name = '';
    newRole.value.color = '#ffffff'; // Reset color
    newRole.value.importance = 0; // Reset importance
    newRole.value.perms = "0";
};

const editRole = (role: Role) => {
    editingRole.value = role;
    newRole.value = role;
};

const updateRole = async () => {
    const currentEditingRole = editingRole.value;

    if (currentEditingRole) {
        const response = await axios.patch(edit.url(currentEditingRole.id), newRole.value);

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

        await axios.post(create.url(selectedServer?.id), {
            name: newRole.value.name,
            color: newRole.value.color,
            perms: newRole.value.perms,
            importance: newRole.value.importance,
        });

        closeModal();
        await fetchRoles();
    } catch (error) {
        console.error('Error adding role:', error);
    }
};

const deleteRole = async (role: Role) => {
    try {
        await axios.delete(deleteMethod.url(role.id));

        roles.value = roles.value.filter(r => r.id !== role.id);

        roles.value.forEach(r => {
            if (r.importance > role.importance) {
                r.importance -= 1;
                axios.patch(edit.url(r.id), {importance: r.importance});
            }
        });

        roles.value.sort((a, b) => a.importance - b.importance);

    } catch (error) {
        console.error('Error deleting role:', error);
    }
};


// Fetch roles when the component mounts
fetchRoles();

const togglePerm = (perm: typeof PermType | number, state: boolean) => {
    if (editingRole.value?.perms === undefined) return;
    const currentPerm = bigIntToPerms(BigInt(editingRole.value?.perms))

    if (state) currentPerm.add(perm)
    else currentPerm.remove(perm)

    editingRole.value.perms = currentPerm.perm.toString()
};

const roleArray = Object.entries(PermType);

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

        await axios.patch(edit.url(role.id), {importance: role.importance});
        await axios.patch(edit.url(swapRole.id), {importance: swapRole.importance});
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
                <Link :href="server.url(selectedServer?.id)" class="btn btn-neutral">
                    Cancel
                </Link>
            </div>

            <!-- Role Settings Content Section -->
            <div class="bg-base-200 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-base-content mb-6">Role Settings</h1>

                <!-- Button to Open Modal -->
                <button :disabled="!perms.has(PermType.CAN_CREATE_ROLE)" class="btn mb-6" @click="isModalOpen = true">
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
                                    <v-icon name="co-arrow-top"/>
                                </button>
                                <button class="btn" @click="changeImportance(role, 1)">
                                    <v-icon name="co-arrow-bottom"/>
                                </button>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-base-content">
                            <div class="dropdown">
                                <button
                                    class="btn m-1"
                                    tabindex="0">
                                    {{
                                        roleArray
                                            .filter(roleObj => BigInt(roleObj[1]) & BigInt(role.perms))
                                            .map(roleObj => roleObj[0])
                                            .slice(0, 3)
                                            .join(', ') + (roleArray.filter(roleObj => BigInt(roleObj[1]) & BigInt(role.perms)).length > 3 ?
                                            ' +' + (roleArray.filter(roleObj => BigInt(roleObj[1]) & BigInt(role.perms)).length - 3).toString() + ' others' : '') + (roleArray.filter(roleObj => BigInt(roleObj[1]) & BigInt(role.perms)).length === 0 ? 'None'
                                            : '')
                                    }}
                                </button>
                                <ul
                                    class="dropdown-content menu bg-base-100 rounded-box z-[1] p-2 shadow gap-y-1"
                                    tabindex="0">
                                    <template v-for="rolePerms in [bigIntToPerms(BigInt(role.perms))]" :key="'perms-' + role.id">
                                        <li v-for="(perm, index) in roleArray" :key="index">
                                            <button
                                                :class="(rolePerms.has(BigInt(perm[1])) ? 'bg-base-300' : '')"
                                                :disabled="editingRole !== role"
                                                class="btn"
                                                @click="() => togglePerm(perm[1], !rolePerms.has(BigInt(perm[1])))"
                                            >
                                                <v-icon
                                                    v-if="rolePerms.has(BigInt(perm[1]))"
                                                    name="bi-check-lg"/>
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
                                    v-if="editingRole !== role" :disabled="!perms.has(PermType.CAN_EDIT_ROLE)"
                                    class="btn hover:btn-info px-4 py-2 "
                                    @click="editRole(role)">Edit
                                </button>
                                <button
                                    v-if="editingRole === role" class="btn hover:btn-success px-4 py-2"
                                    @click="updateRole">Save
                                </button>
                                <ConfirmDialog
                                    :class-name="`btn hover:btn-error px-4 py-2 ${!perms.has(PermType.CAN_DELETE_ROLE) ? 'btn-disabled' : ''}`"
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
