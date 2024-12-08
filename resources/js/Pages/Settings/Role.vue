<script setup lang="ts">
import {defineProps, ref} from 'vue';
import {Link, router, usePage} from '@inertiajs/vue3';
import axios, {AxiosResponse} from 'axios';
import {Perms, PermType, Role, Server} from "@/types";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import {bigIntToPerms} from "@/bootstrap";
import {addIcons} from "oh-vue-icons";
import {BiCheckLg, CoArrowBottom, CoArrowTop} from "oh-vue-icons/icons";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

addIcons(BiCheckLg, CoArrowBottom, CoArrowTop);

const {selected_server} = defineProps<{
    selected_server: Server,
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
        const response: AxiosResponse<Role[] | null> = await axios.get(route('roles.index', {server: selected_server?.id}));
        roles.value = response.data ?? []; // Default to empty array if undefined
        // Sort roles by importance
        roles.value.sort((a, b) => a.importance - b.importance);

        if (selected_server && selected_server.roles !== null){
            perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
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
        const response = await axios.patch(route('roles.edit', {role: currentEditingRole.id}), newRole.value);

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

        await axios.post(route('roles.create', { server: selected_server?.id }), {
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
        await axios.delete(route('roles.delete', { role: role.id }));

        roles.value = roles.value.filter(r => r.id !== role.id);

        roles.value.forEach(r => {
            if (r.importance > role.importance) {
                r.importance -= 1;
                axios.patch(route('roles.edit', { role: r.id }), { importance: r.importance });
            }
        });

        roles.value.sort((a, b) => a.importance - b.importance);

    } catch (error) {
        console.error('Error deleting role:', error);
    }
};


// Fetch roles when component mounts
fetchRoles();

const togglePerm = (perm: PermType, state: boolean) => {
    if (editingRole.value?.perms === undefined) return;
    let currentPerm = bigIntToPerms(BigInt(editingRole.value?.perms))
    state ? currentPerm.add(perm) : currentPerm.remove(perm)
    editingRole.value.perms = currentPerm.perm.toString()
};

let roleArray = Object.entries(PermType).filter(role => typeof role[1] === 'number') as [string, number][];

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

        await axios.patch(route('roles.edit', { role: role.id }), { importance: role.importance });
        await axios.patch(route('roles.edit', { role: swapRole.id }), { importance: swapRole.importance });
    }

    roles.value.sort((a, b) => a.importance - b.importance);
};


</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- Navbar for Navigation -->
            <SettingsHeader :selected_server/>
            <div class="flex justify-end mb-6 space-x-4">
                <Link :href="route('home.server', { server: selected_server?.id })" class="btn btn-neutral">
                    Cancel
                </Link>
            </div>

            <!-- Role Settings Content Section -->
            <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-white mb-6">Role Settings</h1>

                <!-- Button to Open Modal -->
                <button :disabled="!perms.has(PermType.CAN_CREATE_ROLE)" @click="isModalOpen = true" class="btn mb-6">Add Role</button>

                <!-- Roles Table -->
                <table class="min-w-full bg-base-300 rounded-lg">
                    <thead class="bg-gray-500">
                    <tr>
                        <th class="py-2 px-4 text-left text-white">Role Name</th>
                        <th class="py-2 px-4 text-left text-white" v-if="editingRole">Color</th>
                        <th class="py-2 px-4 text-left text-white">Importance</th>
                        <th class="py-2 px-4 text-left text-white">Perms</th>
                        <th class="py-2 px-4 text-end text-white">Actions</th>
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
                        <td class="py-2 px-4 text-white">
                            <span v-if="editingRole !== role">{{ role.importance }}</span>
                            <div  v-if="editingRole === role && role.importance !== 0" class="flex justify-center space-x-2">
                                <button @click="changeImportance(role, -1)" class="btn"><v-icon name="co-arrow-top"></v-icon></button>
                                <button @click="changeImportance(role, 1)" class="btn"><v-icon name="co-arrow-bottom"></v-icon></button>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-black">
                            <div class="dropdown">
                                <button tabindex="0"
                                        class="btn m-1">
                                    {{
                                        roleArray.filter(roleobj => BigInt(roleobj[1]) & BigInt(role.perms)).map(roleobj => roleobj[0]).slice(0, 3).join(', ') + (roleArray.filter(roleobj => BigInt(roleobj[1]) & BigInt(role.perms)).length > 3 ? ' +' + (roleArray.filter(roleobj => BigInt(roleobj[1]) & BigInt(role.perms)).length - 3).toString() + ' others' : '') + (roleArray.filter(roleobj => BigInt(roleobj[1]) & BigInt(role.perms)).length === 0 ? 'None' : '')
                                    }}
                                </button>
                                <ul tabindex="0"
                                    class="dropdown-content menu bg-base-100 rounded-box z-[1] p-2 shadow gap-y-1">
                                    <li v-for="perm in roleArray">
                                        <button
                                            @click="() => togglePerm(perm[1], !bigIntToPerms(BigInt(role.perms)).has(BigInt(perm[1])))"
                                            :class="(bigIntToPerms(BigInt(role.perms)).has(BigInt(perm[1])) ? 'bg-gray-700' : '')"
                                            class="btn"
                                            :disabled="editingRole !== role"
                                        >
                                            <v-icon name="bi-check-lg"
                                                    v-if="bigIntToPerms(BigInt(role.perms)).has(BigInt(perm[1]))"/>
                                            {{ perm[0] }}
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-end">
                            <div class="flex justify-end space-x-2">
                                <button v-if="editingRole !== role" :disabled="!perms.has(PermType.CAN_EDIT_ROLE)" @click="editRole(role)"
                                        class="btn hover:btn-info px-4 py-2 ">Edit
                                </button>
                                <button v-if="editingRole === role" @click="updateRole"
                                        class="btn hover:btn-success px-4 py-2">Save
                                </button>
                                <ConfirmDialog title="Are you sure?"
                                               description="Are you sure you want to delete this role?"
                                               :class-name="`btn hover:btn-error px-4 py-2 ${!perms.has(PermType.CAN_DELETE_ROLE) ? 'btn-disabled' : ''}`"
                                               :confirm="() => deleteRole(role)" text="Delete"/>
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
                        <!-- Removed Importance Field from the Modal Form -->
                        <div class="modal-action">
                            <button @click="addRole" class="btn btn-success">Add Role</button>
                            <button @click="closeModal" class="btn">Cancel</button>
                        </div>
                    </div>
                </dialog>
            </div>
        </div>
    </div>
</template>
