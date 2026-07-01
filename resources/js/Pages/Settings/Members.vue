<script lang="ts" setup>
import { usePerms } from '@/bootstrap';

import { server } from '@/routes/home';
import { addUser, removeUser as roles_removeUser } from '@/routes/roles';
import { removeUser as server_removeUser } from '@/routes/server';

import {Link, router, usePage} from "@inertiajs/vue3";
import {PermType, Role, Server, User} from "@/types";
import {baseUrl} from "@/bootstrap";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { GiBootKick } from 'vue-icons-plus/gi';
import { BsCheckLg } from 'vue-icons-plus/bs';

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

const toggleRole = (roleId: number, userId: number, state: boolean) => {
    if (state) {
        router.post(addUser.url({role: roleId, user: userId}), {}, {
            onSuccess: () => router.reload({only: ['selected_server']})
        });
    } else {
        router.delete(roles_removeUser.url({role: roleId, user: userId}), {
            onSuccess: () => router.reload({only: ['selected_server']})
        });
    }
};

const kickMember = (userId: number) =>
    router.delete(server_removeUser.url(selectedServer.route_key), {
        data: {'user_id': userId},
        onSuccess: () => router.reload({only: ['selected_server']})
    });
</script>

<template>
    <div class="flex h-screen bg-base-100 overflow-hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <div class="px-6 pt-6 md:px-10 md:pt-10 max-w-6xl mx-auto w-full pb-0">
                <SettingsHeader :selected-server="selectedServer"/>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 md:p-10 pt-0">
                <div class="max-w-4xl mx-auto space-y-8 pb-20">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-base-content">Members Settings</h1>
                        <div class="flex space-x-3">
                            <Link :href="server.url(selectedServer!.route_key)" class="btn btn-neutral px-6">
                                ← Back to Server
                            </Link>
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body p-0">
                            <!-- Roles Table -->
                            <div class="rounded-b-2xl">
                                <table class="table w-full m-0">
                                    <thead class="bg-base-300 text-base-content border-b border-base-300">
                                        <tr>
                                            <th class="py-4 px-6 text-left text-sm font-semibold uppercase tracking-wider">User Name</th>
                                            <th class="py-4 px-6 text-center text-sm font-semibold uppercase tracking-wider">Roles</th>
                                            <th class="py-4 px-6 text-end text-sm font-semibold uppercase tracking-wider">Kick</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-base-300 bg-base-200">
                                        <tr v-for="user in selectedServer.users" :key="user.id" class="hover:bg-base-300/50 transition-colors">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center space-x-3">
                                                    <div class="avatar" :class="{ placeholder: !user.icon }">
                                                        <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                                            <img v-if="user.icon" :src="baseUrl + user.icon" :alt="user.name" />
                                                            <span v-else class="text-xs uppercase">{{ user.name.substring(0, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="font-medium text-base-content">{{ user.name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-center">
                                                <div class="dropdown dropdown-bottom dropdown-end">
                                                    <button
                                                        :disabled="selectedServer.roles?.length === 0"
                                                        class="btn btn-sm bg-base-100 hover:bg-base-300 border-base-300"
                                                        :class="selectedServer.roles?.length === 0 ? 'tooltip' : ''"
                                                        data-tip="Your server doesnt have roles."
                                                        tabindex="0">
                                                        {{ user.rolesWithServer.map(role => role.name).join(', ') || 'None' }}
                                                    </button>
                                                    <ul
                                                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow-lg gap-y-1 mt-2 border border-base-300"
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
                                                    v-if="user.id !== usePage().props.user?.id"
                                                    :class-name="`btn btn-ghost btn-sm text-error hover:bg-error/20 ${!perms.has([PermType.CAN_KICK]) ? 'btn-disabled opacity-50' : ''}`"
                                                    :confirm="() => kickMember(user.id)"
                                                    :description="`Are you sure you want to kick ${user.name}? This action cannot be undone.`"
                                                    :title="`Kick ${user.name}`">
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
    </div>
</template>