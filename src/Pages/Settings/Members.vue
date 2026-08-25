<script lang="ts" setup>
import {defaultIcon, fetchJson, getMemberRoleColor, resolveUrl, usePerms} from '@/bootstrap';
import SettingsHeader from "@/Components/SettingsHeader.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {GiBootKick} from 'vue-icons-plus/gi';
import {BsCheckLg} from 'vue-icons-plus/bs';
import {computed, ref} from 'vue';
import {PermType, Role, Server, User} from '@/types';
import pb from '@/pocketbase';
import {server} from '@/routes/home';

interface customUser extends User {
    rolesWithServer: Role[]
}

interface customServer extends Server {
    users: customUser[]
}

const perms = usePerms();
const {selectedServer} = defineProps<{
    selectedServer: customServer,
}>();

const createdInviteCode = ref<string | null>(null);
const searchMembers = ref('');
const filterRoleId = ref<string | null>(null);

const filteredMembers = computed(() => {
    let members = selectedServer.users;
    if (searchMembers.value) {
        const query = searchMembers.value.toLowerCase();
        members = members.filter(user => user.nickname.toLowerCase().includes(query));
    }
    if (filterRoleId.value) {
        members = members.filter(user =>
            user.rolesWithServer.some(role => role.id === filterRoleId.value)
        );
    }
    return members;
});

const toggleRole = async (roleId: string, userId: string, state: boolean) => {
    try {
        const members = await pb.collection('members').getList(1, 1, {
            filter: `server = "${selectedServer.id}" && user = "${userId}"`
        });
        if (members.items.length > 0) {
            await pb.collection('members').update(members.items[0].id, {
                role: state ? roleId : null
            });
        }
    } catch (err: unknown) {
        console.error('Error updating member role:', err);
    }
};

const kickMember = async (userId: string) => {
    try {
        const members = await pb.collection('members').getList(1, 1, {
            filter: `server = "${selectedServer.id}" && user = "${userId}"`
        });
        if (members.items.length > 0) {
            await pb.collection('members').delete(members.items[0].id);
        }
    } catch (err: unknown) {
        console.error('Error kicking member:', err);
    }
};

const generateInvite = async () => {
    try {
        const {data} = await fetchJson(`/server/${selectedServer.id}/invites`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        if (data && data.invite) {
            createdInviteCode.value = data.invite.code;
        }
    } catch {
        // Handle error
    }
};
</script>

<template>
    <div class="flex h-screen bg-base-100 overflow-hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <div class="px-6 pt-6 md:px-10 md:pt-10 max-w-6xl mx-auto w-full pb-0">
                <SettingsHeader :selected-server="selectedServer">
                    <template #title>
                        Members
                    </template>
                </SettingsHeader>
            </div>

            <div class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-4xl mx-auto space-y-6 pb-20">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-base-content">Server Members</h2>
                            <p class="text-sm text-base-content/70 mt-1">Manage members and roles for this server.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <router-link :to="server.url(selectedServer.route_key)" class="btn btn-neutral px-6">
                                ← Back to Server
                            </router-link>
                            <button
                                v-if="perms.has([PermType.CAN_INVITE, PermType.CAN_MANAGE_SERVER])"
                                class="btn btn-primary" @click="generateInvite">
                                Generate Invite Code
                            </button>
                        </div>
                    </div>

                    <div v-if="createdInviteCode" class="alert alert-info">
                        <span>Invite Code: <strong class="text-lg font-mono">{{ createdInviteCode }}</strong></span>
                    </div>

                    <!-- Search & Filter -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="searchMembers"
                            autocomplete="off"
                            class="input input-bordered input-sm flex-1 bg-base-100"
                            data-bwignore="true"
                            placeholder="Search members..."
                            type="text"/>
                        <select
                            v-model="filterRoleId"
                            class="select select-bordered select-sm w-full sm:w-48 bg-base-100">
                            <option :value="null">All Roles</option>
                            <option v-for="role in selectedServer.roles" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <div class="card bg-base-200 border border-base-300 shadow-sm">
                        <div class="overflow-x-visible">
                            <table class="table w-full">
                                <thead>
                                <tr class="border-b border-base-300 bg-base-300/50">
                                    <th class="py-4 px-6 text-start text-xs font-semibold text-base-content/70 uppercase">
                                        User
                                    </th>
                                    <th class="py-4 px-6 text-center text-xs font-semibold text-base-content/70 uppercase">
                                        Roles
                                    </th>
                                    <th class="py-4 px-6 text-end text-xs font-semibold text-base-content/70 uppercase">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="user in filteredMembers" :key="user.id"
                                    class="border-b border-base-300 hover:bg-base-300/30 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div :class="{ placeholder: !user.icon }" class="avatar">
                                                <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                                    <img
                                                        v-if="user.icon" :alt="user.nickname"
                                                        :src="resolveUrl(user.icon)"
                                                        @error="(e) => (e.target as HTMLImageElement).src = defaultIcon"
                                                    />
                                                    <span v-else class="text-xs uppercase">{{
                                                            user.nickname.substring(0, 2)
                                                        }}</span>
                                                </div>
                                            </div>
                                            <span
                                                :style="{ color: getMemberRoleColor(user, selectedServer.roles) }"
                                                class="font-medium">{{
                                                    user.nickname
                                                }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="dropdown dropdown-top dropdown-end">
                                            <button
                                                :class="selectedServer.roles?.length === 0 ? 'tooltip' : ''"
                                                :disabled="selectedServer.roles?.length === 0"
                                                class="btn btn-sm bg-base-100 hover:bg-base-300 border-base-300"
                                                data-tip="Your server doesnt have roles."
                                                tabindex="0">
                                                {{ user.rolesWithServer.map(role => role.name).join(', ') || 'None' }}
                                            </button>
                                            <ul
                                                class="dropdown-content menu bg-base-100 rounded-box z-50 w-52 p-2 shadow-lg gap-y-1 mb-2 border border-base-300"
                                                tabindex="0">
                                                <li v-for="role in selectedServer.roles" :key="role.id">
                                                    <button
                                                        :class="user.rolesWithServer.find(objRole => objRole.id === role.id) ? 'bg-primary/10 text-primary hover:bg-primary/20' : 'hover:bg-base-200'"
                                                        :disabled="!perms.has([PermType.CAN_EDIT_MEMBER_ROLES])"
                                                        class="btn btn-ghost btn-sm justify-start w-full"
                                                        @click="() => toggleRole(role.id, user.id, !user.rolesWithServer.find(objRole => objRole.id === role.id))">
                                                        <BsCheckLg
                                                            v-if="user.rolesWithServer.find(objRole => objRole.id === role.id)"
                                                            class="w-4 h-4 mr-2"
                                                        />
                                                        <span v-else class="w-4 h-4 mr-2"></span>
                                                        {{ role.name }}
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-end">
                                        <ConfirmDialog
                                            v-if="user.id !== pb.authStore.model?.id"
                                            :class-name="`btn btn-ghost btn-sm text-error hover:bg-error/20 ${!perms.has([PermType.CAN_KICK]) ? 'btn-disabled opacity-50' : ''}`"
                                            :confirm="() => kickMember(user.id)"
                                            :description="`Are you sure you want to kick ${user.nickname}? This action cannot be undone.`"
                                            :title="`Kick ${user.nickname}`">
                                            <GiBootKick class="w-5 h-5"/>
                                        </ConfirmDialog>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
