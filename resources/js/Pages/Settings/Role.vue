<script lang="ts" setup>
import {baseUrl, bigIntToPerms, defaultIcon, fetchJson, usePerms} from '@/bootstrap';
import {server} from '@/routes/home';
import {create, deleteMethod, edit, index} from '@/routes/roles';
import {computed, ref} from 'vue';
import {Link} from '@inertiajs/vue3';
import {PermType, Role, Server} from "@/types";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {BiDownArrowAlt, BiUpArrowAlt} from 'vue-icons-plus/bi';
import {HiClipboardCopy} from 'vue-icons-plus/hi';

const perms = usePerms();
const {selectedServer, allPermissions} = defineProps<{
    selectedServer: Server,
    allPermissions: { name: string, title: string, description: string }[]
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

const searchRoles = ref('');
const searchPermissions = ref('');
const openCategories = ref<Record<string, boolean>>({});

const filteredRoles = computed(() => {
    if (!searchRoles.value) return roles.value;
    return roles.value.filter(role => role.name.toLowerCase().includes(searchRoles.value.toLowerCase()));
});

const filteredPermissions = computed(() => {
    if (!searchPermissions.value) return allPermissions;
    return allPermissions.filter(perm =>
        (perm.title || perm.name).toLowerCase().includes(searchPermissions.value.toLowerCase()) ||
        (perm.description || '').toLowerCase().includes(searchPermissions.value.toLowerCase())
    );
});

const groupedPermissions = computed(() => {
    const groups: Record<string, typeof allPermissions> = {};
    for (const perm of filteredPermissions.value) {
        const cat = perm.category || 'Other Permissions';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(perm);
    }
    return groups;
});

const fetchRoles = async () => {
    try {
        const response = await fetchJson(index.url(selectedServer?.route_key));
        roles.value = response.data ?? [];
        roles.value.sort((a, b) => a.importance - b.importance);

        // Update editingRole if it was already selected
        if (editingRole.value) {
            const updated = roles.value.find(r => r.id === editingRole.value?.id);
            if (updated) {
                editingRole.value = updated;
                // Avoid overwriting newRole if they have unsaved changes?
                // Actually it's fine, we should just update it if we fetched explicitly.
            } else {
                editingRole.value = null;
            }
        } else if (roles.value.length > 0) {
            selectRole(roles.value[0]);
        }
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const closeModal = () => {
    isModalOpen.value = false;
};

const selectRole = (role: Role) => {
    editingRole.value = role;
    newRole.value = JSON.parse(JSON.stringify(role));
};

const updateRole = async () => {
    if (editingRole.value) {
        const response = await fetchJson(edit.url(editingRole.value.id), {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(newRole.value)
        });

        const index = roles.value.findIndex(r => r.id === editingRole.value?.id);
        roles.value[index] = response.data.role;

        // Keep it selected after save
        selectRole(response.data.role);
        await fetchRoles();
    }
};

const newRoleForm = ref({name: '', color: '#ffffff'});

const addRole = async () => {
    try {
        let importance = 1;
        if (roles.value.length > 0) {
            const maxImportance = Math.max(...roles.value.map(role => role.importance));
            importance = maxImportance + 1;
        }

        await fetchJson(create.url(selectedServer?.route_key), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                name: newRoleForm.value.name,
                color: newRoleForm.value.color,
                perms: [],
                importance: importance,
            })
        });

        closeModal();
        newRoleForm.value.name = '';
        newRoleForm.value.color = '#ffffff';
        await fetchRoles();
    } catch (error) {
        console.error('Error adding role:', error);
    }
};

const duplicateRole = async (roleToDuplicate?: Role, event?: Event) => {
    if (event) {
        event.stopPropagation();
    }
    const targetRole = roleToDuplicate || editingRole.value;
    if (!targetRole || !perms.value.has([PermType.CAN_CREATE_ROLE])) return;
    try {
        let importance = 1;
        if (roles.value && roles.value.length > 0) {
            const validImportances = roles.value.map(role => Number(role.importance)).filter(i => !isNaN(i));
            if (validImportances.length > 0) {
                importance = Math.max(...validImportances) + 1;
            }
        }

        const rawName = targetRole.id === editingRole.value?.id ? (newRole.value.name || targetRole.name) : targetRole.name;
        const rawColor = targetRole.id === editingRole.value?.id ? (newRole.value.color || targetRole.color) : targetRole.color;
        const rawPerms = targetRole.id === editingRole.value?.id ? (newRole.value.perms || targetRole.perms || []) : (targetRole.perms || []);

        let name = (rawName || 'Role').trim();
        if (name.length > 245) {
            name = name.substring(0, 245);
        }
        name = `${name} Copy`;

        let color = (rawColor || '#ffffff').trim();
        if (!color.startsWith('#')) {
            color = '#' + color;
        }
        if (color.length === 4) {
            color = '#' + color[1] + color[1] + color[2] + color[2] + color[3] + color[3];
        }
        if (color.length !== 7) {
            color = '#ffffff';
        }

        let permsArray: string[] = [];
        if (Array.isArray(rawPerms)) {
            permsArray = rawPerms.map((p: any) => typeof p === 'string' ? p : (p?.name || String(p))).filter(Boolean);
        }

        const response = await fetchJson(create.url(selectedServer?.route_key), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                name: name,
                color: color,
                perms: permsArray,
                importance: importance,
            })
        });

        await fetchRoles();
        if (response.data?.role) {
            const duplicatedRole = roles.value.find(r => r.id === response.data.role.id) || response.data.role;
            selectRole(duplicatedRole);
        }
    } catch (error: any) {
        console.error('Error duplicating role:', error?.response?.data || error);
    }
};

const deleteRole = async () => {
    if (!editingRole.value) return;
    const role = editingRole.value;
    try {
        await fetchJson(deleteMethod.url(role.id), {method: 'DELETE'});

        roles.value = roles.value.filter(r => r.id !== role.id);

        roles.value.forEach(r => {
            if (r.importance > role.importance) {
                r.importance -= 1;
                fetchJson(edit.url(r.id), {
                    method: 'PATCH',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({importance: r.importance})
                });
            }
        });

        roles.value.sort((a, b) => a.importance - b.importance);
        editingRole.value = null;
        if (roles.value.length > 0) {
            selectRole(roles.value[0]);
        }

    } catch (error) {
        console.error('Error deleting role:', error);
    }
};

fetchRoles();

const expandAllPermissions = () => {
    for (const cat of Object.keys(groupedPermissions.value)) {
        openCategories.value[cat] = true;
    }
};

const collapseAllPermissions = () => {
    for (const cat of Object.keys(openCategories.value)) {
        openCategories.value[cat] = false;
    }
};

const allExpanded = computed(() => {
    const cats = Object.keys(groupedPermissions.value);
    return cats.length > 0 && cats.every(cat => openCategories.value[cat]);
});

const togglePerm = (perm: string, state: boolean) => {
    if (!newRole.value) return;

    // We should not modify the reactive `editingRole` directly, but `newRole`.
    const currentPerm = bigIntToPerms(newRole.value.perms || []);

    if (state) currentPerm.add(perm);
    else currentPerm.remove(perm);

    newRole.value.perms = currentPerm.perms;
};

const changeImportance = async (role: Role, direction: number, event: Event) => {
    event.stopPropagation();
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
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({importance: role.importance})
        });
        await fetchJson(edit.url(swapRole.id), {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({importance: swapRole.importance})
        });
    }

    roles.value.sort((a, b) => a.importance - b.importance);
};
</script>

<template>
    <div class="flex h-screen bg-base-100 overflow-hidden">
        <!-- Sidebar for Navigation -->
        <div class="w-80 bg-base-200 border-r border-base-300 flex flex-col h-full shrink-0">
            <div class="p-4 border-b border-base-300">
                <Link :href="server.url(selectedServer?.route_key)" class="btn btn-neutral w-full mb-4">
                    ← Back to Server
                </Link>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-base-content">Roles</h2>
                    <button
                        :disabled="!perms.has([PermType.CAN_CREATE_ROLE])" class="btn btn-sm btn-primary"
                        @click="isModalOpen = true">
                        Add Role
                    </button>
                </div>
                <input
                    v-model="searchRoles" autocomplete="off" class="input input-bordered input-sm w-full bg-base-100"
                    data-bwignore="true"
                    placeholder="Search Roles" type="text"/>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-1">
                <div
                    v-for="role in filteredRoles" :key="role.id"
                    :class="editingRole?.id === role.id ? 'bg-primary text-primary-content' : 'hover:bg-base-300 text-base-content'"
                    class="p-3 rounded-lg cursor-pointer flex justify-between items-center transition-colors group"
                    @click="selectRole(role)">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div
                            :style="{ backgroundColor: role.color }"
                            class="w-4 h-4 rounded-full shrink-0 shadow-sm border border-black/10"></div>
                        <span
                            :style="editingRole?.id === role.id ? {} : { color: role.color }"
                            class="font-medium truncate">{{ role.name }}</span>
                    </div>
                    <div
                        v-if="role.importance !== 0"
                        class="flex items-center space-x-1 shrink-0 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                        <button
                            :class="editingRole?.id === role.id ? 'text-primary-content hover:bg-black/20' : ''"
                            :disabled="!perms.has([PermType.CAN_CREATE_ROLE])"
                            class="btn btn-xs btn-circle btn-ghost"
                            title="Duplicate Role"
                            @click="(e) => duplicateRole(role, e)">
                            <HiClipboardCopy class="w-3.5 h-3.5"/>
                        </button>
                        <button
                            :class="editingRole?.id === role.id ? 'text-primary-content hover:bg-black/20' : ''"
                            :disabled="role.importance <= 1"
                            class="btn btn-xs btn-circle btn-ghost"
                            @click="(e) => changeImportance(role, -1, e)">
                            <BiUpArrowAlt class="w-4 h-4"/>
                        </button>
                        <button
                            :class="editingRole?.id === role.id ? 'text-primary-content hover:bg-black/20' : ''"
                            :disabled="role.importance >= roles.length - 1"
                            class="btn btn-xs btn-circle btn-ghost"
                            @click="(e) => changeImportance(role, 1, e)">
                            <BiDownArrowAlt class="w-4 h-4"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Settings Content Section -->
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <SettingsHeader :selected-server="selectedServer"/>

            <div v-if="editingRole" class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-4xl mx-auto space-y-8 pb-20">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-base-content">Edit Role</h1>
                        <div class="flex space-x-3">
                            <ConfirmDialog
                                :class-name="`btn btn-error px-6 ${(!perms.has([PermType.CAN_DELETE_ROLE]) || editingRole.importance === 0) ? 'btn-disabled' : ''}`"
                                :confirm="deleteRole"
                                description="Are you sure you want to delete this role? This action cannot be undone."
                                text="Delete Role" title="Delete Role"/>
                            <button
                                :disabled="!perms.has([PermType.CAN_CREATE_ROLE])"
                                class="btn btn-neutral px-6"
                                @click="duplicateRole">
                                Duplicate Role
                            </button>
                            <button
                                :disabled="!perms.has([PermType.CAN_EDIT_ROLE])"
                                class="btn btn-success px-8"
                                @click="updateRole">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- General Settings -->
                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">General
                                Settings</h2>
                            <div class="flex flex-col md:flex-row gap-8">
                                <!-- Left: Settings -->
                                <div class="flex-1 space-y-5">
                                    <div class="form-control w-full">
                                        <label class="label"><span
                                            class="label-text font-medium">Role Name</span></label>
                                        <input
                                            v-model="newRole.name" :disabled="editingRole.importance === 0"
                                            autocomplete="off" class="input input-bordered w-full bg-base-100"
                                            data-bwignore="true"
                                            type="text"/>
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label">
                                            <span class="label-text font-medium">Role Color (Member Name Color)</span>
                                        </label>
                                        <div class="flex items-center space-x-3">
                                            <input
                                                v-model="newRole.color" autocomplete="off"
                                                class="h-12 w-20 rounded cursor-pointer bg-base-100 border border-base-300 p-1"
                                                data-bwignore="true"
                                                type="color"/>
                                            <span class="text-base-content opacity-70 uppercase font-mono">{{
                                                    newRole.color
                                                }}</span>
                                        </div>
                                        <p class="text-xs text-base-content/60 mt-1">This role's color will be used as
                                            the
                                            name color for members with this as their highest role.</p>
                                    </div>
                                </div>
                                <!-- Right: Preview -->
                                <div class="w-full md:w-64 shrink-0">
                                    <span class="label-text font-medium">Preview</span>
                                    <div
                                        class="mt-1 p-4 bg-base-100 rounded-lg border border-base-300 flex items-center gap-3">
                                        <div :class="{ placeholder: !$page.props.user?.icon }" class="avatar">
                                            <div class="w-8 h-8 rounded-full bg-base-300">
                                                <img
                                                    v-if="$page.props.user?.icon"
                                                    :alt="$page.props.user?.nickname || 'User'"
                                                    :src="`${baseUrl}${$page.props.user.icon}`"/>
                                                <img v-else :src="defaultIcon" alt="User"/>
                                            </div>
                                        </div>
                                        <span :style="{ color: newRole.color }" class="text-sm font-bold">
                                            {{ $page.props.user?.nickname || 'SampleUser' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <div
                                class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-base-300 pb-4 mb-6 gap-4">
                                <h2 class="card-title text-xl text-base-content m-0">Permissions</h2>
                                <div class="flex items-center gap-2">
                                    <button
                                        class="btn btn-sm btn-ghost"
                                        @click="allExpanded ? collapseAllPermissions() : expandAllPermissions()">
                                        {{ allExpanded ? 'Collapse All' : 'Expand All' }}
                                    </button>
                                    <input
                                        v-model="searchPermissions" autocomplete="off"
                                        class="input input-bordered input-sm w-full md:w-64 bg-base-100"
                                        data-bwignore="true" placeholder="Search Permissions"
                                        type="text"/>
                                </div>
                            </div>

                            <div
                                v-for="(permsList, category) in groupedPermissions" :key="category"
                                class="collapse collapse-arrow bg-base-100 border border-base-300 mb-4 last:mb-0">
                                <input
                                    :checked="searchPermissions.length > 0 || openCategories[category]"
                                    type="checkbox"
                                    @change="(e) => openCategories[category] = (e.target as HTMLInputElement).checked"/>
                                <div
                                    class="collapse-title text-base font-bold text-base-content/80 uppercase tracking-wider">
                                    {{ category }}
                                </div>
                                <div class="collapse-content">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                        <label
                                            v-for="perm in permsList" :key="perm.name"
                                            :class="!perms.has([PermType.CAN_EDIT_ROLE]) ? 'opacity-70 cursor-not-allowed' : ''"
                                            class="flex items-start gap-4 p-4 bg-base-200 rounded-xl border border-base-300 shadow-sm transition-colors hover:border-primary/30 cursor-pointer">
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-base-content select-none">
                                                    {{ perm.title || perm.name }}</h4>
                                                <p class="text-sm text-base-content/70 mt-1 leading-snug select-none">
                                                    {{ perm.description }}</p>
                                            </div>
                                            <input
                                                :checked="bigIntToPerms(newRole.perms).has(perm.name)"
                                                :disabled="!perms.has([PermType.CAN_EDIT_ROLE])"
                                                class="toggle toggle-primary mt-1"
                                                type="checkbox"
                                                @change="(e) => togglePerm(perm.name, (e.target as HTMLInputElement).checked)"/>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="Object.keys(groupedPermissions).length === 0"
                                class="text-center py-8 text-base-content/50">
                                No permissions found matching "{{ searchPermissions }}"
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="flex-1 flex flex-col items-center justify-center text-base-content/50">
                <div class="w-24 h-24 rounded-full bg-base-200 flex items-center justify-center mb-4">
                    <svg
                        class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium">No Role Selected</h3>
                <p>Select a role from the sidebar to view or edit its settings.</p>
            </div>
        </div>

        <!-- Modal for Adding Role -->
        <dialog :class="{'modal-open': isModalOpen}" class="modal">
            <div class="modal-box bg-base-200">
                <h3 class="font-bold text-lg mb-4 text-base-content">Create New Role</h3>
                <div class="form-control mb-4">
                    <label class="label" for="newRoleName">
                        <span class="label-text">Role Name</span>
                    </label>
                    <input
                        id="newRoleName"
                        v-model="newRoleForm.name"
                        autocomplete="off"
                        class="input input-bordered w-full bg-base-100"
                        data-bwignore="true"
                        placeholder="e.g. Moderator"
                        required
                        type="text"
                    />
                </div>
                <div class="form-control mb-6">
                    <label class="label">
                        <span class="label-text">Role Color (Member Name Color)</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <input
                            id="newRoleColor"
                            v-model="newRoleForm.color"
                            autocomplete="off"
                            class="h-10 w-20 cursor-pointer bg-base-100 rounded border border-base-300 p-1"
                            data-bwignore="true"
                            type="color"
                        />
                        <span class="uppercase font-mono text-sm opacity-70">{{ newRoleForm.color }}</span>
                    </div>
                    <div class="mt-3">
                        <span class="label-text font-medium">Preview</span>
                        <div class="mt-1 p-4 bg-base-100 rounded-lg border border-base-300 flex items-center gap-3">
                            <div :class="{ placeholder: !$page.props.user?.icon }" class="avatar">
                                <div class="w-8 h-8 rounded-full bg-base-300">
                                    <img
                                        v-if="$page.props.user?.icon" :alt="$page.props.user?.nickname || 'User'"
                                        :src="`${baseUrl}${$page.props.user.icon}`"/>
                                    <img v-else :src="defaultIcon" alt="User"/>
                                </div>
                            </div>
                            <span :style="{ color: newRoleForm.color }" class="text-sm font-bold">
                                {{ $page.props.user?.nickname || 'SampleUser' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-action">
                    <button class="btn btn-ghost" @click="closeModal">Cancel</button>
                    <button :disabled="!newRoleForm.name" class="btn btn-primary" @click="addRole">Create Role</button>
                </div>
            </div>
            <form class="modal-backdrop" method="dialog">
                <button @click="closeModal">close</button>
            </form>
        </dialog>
    </div>
</template>
