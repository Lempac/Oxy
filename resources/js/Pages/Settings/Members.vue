<script lang="ts" setup>
import { server } from '@/routes/home';
import { addUser, removeUser as roles_removeUser } from '@/routes/roles';
import { removeUser as server_removeUser } from '@/routes/server';

import {Link, router, usePage} from "@inertiajs/vue3";
import {Perms, PermType, Role, Server, User} from "@/types";
import {BiCheckLg, GiBootKick} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {ref} from "vue";
import {bigIntToPerms} from "@/bootstrap";

addIcons(BiCheckLg, GiBootKick);

interface customUser extends User {
    rolesWithServer: Role[]
}

interface customServer extends Server {
    users: customUser[]
}

const {selectedServer} = defineProps<{
    selectedServer: customServer,
}>();

const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const toggleRole = (roleId: number, userId: number, state: boolean) => {
    if (state) {
        router.post(addUser.url({role: roleId, user: userId}), {}, {
            onSuccess: () => router.reload({only: ['selected_server']})
        })
    } else {
        router.delete(roles_removeUser.url({role: roleId, user: userId}), {
            onSuccess: () => router.reload({only: ['selected_server']})
        })
    }
};

const kickMember = (userId: number) =>
    router.delete(server_removeUser.url(selectedServer.slug), {
        data: {'user_id': userId},
        onSuccess: () => router.reload({only: ['selected_server']})
    })


if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles
        .filter(role => usePage().props.user?.roles?.some(roleObj => roleObj.id === role.id))
        .reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- Navbar for Navigation -->
            <SettingsHeader :selected-server/>

            <div class="flex justify-end mb-6 space-x-4">
                <Link :href="server.url(selectedServer?.slug)" class="btn btn-neutral">
                    Cancel
                </Link>
            </div>

            <!-- Role Settings Content Section -->
            <div class="bg-base-200 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-base-content mb-6">Members Settings</h1>

                <!-- Roles Table -->
                <table class="min-w-full bg-base-300 rounded-lg">
                    <thead class="bg-base-100">
                    <tr class="text-base-content">
                        <th class="py-4 px-4 text-left">User Name</th>
                        <th class="py-4 px-4">Roles</th>
                        <th class="py-4 px-4 pr-7 text-end">Kick</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="user in selectedServer.users" :key="user.id">
                        <td class="py-2 px-4">
                            <span>{{ user.name }}</span>
                        </td>
                        <td class="py-2 px-4 text-center">
                            <div class="dropdown">
                                <button
                                    :disabled="selectedServer.roles?.length === 0"
                                    class="btn m-1 {{server.roles.length === 0 ? 'tooltip' : ''}}"
                                    data-tip="Your server doesnt have roles."
                                    tabindex="0">
                                    {{ user.rolesWithServer.map(role => role.name).join(', ') || 'None' }}
                                </button>
                                <ul
                                    class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow gap-y-1"
                                    tabindex="0">
                                    <li v-for="role in selectedServer.roles" :key="role.id">
                                        <button
                                            :class="user.rolesWithServer.find(objRole => objRole.id === role.id) ? 'bg-base-300' : ''"
                                            :disabled="!perms.has(PermType.CAN_EDIT_MEMBER_ROLES)"
                                            class="btn"
                                            @click="() => toggleRole(role.id, user.id, !user.rolesWithServer.find(objRole => objRole.id === role.id))">
                                            <v-icon
                                                v-if="user.rolesWithServer.find(objRole => objRole.id === role.id)"
                                                name="bi-check-lg"/>
                                            {{ role.name }}
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td v-if="user.id !== usePage().props.user?.id" class="py-2 px-4 text-end">
                            <ConfirmDialog
                                :class-name="`btn btn-error ${!perms.has(PermType.CAN_KICK) ? 'btn-disabled' : ''}`"
                                :confirm="() => kickMember(user.id)"
                                description="Are you sure you want to kick this member?"
                                title="Are you sure?">
                                <v-icon name="gi-boot-kick"/>
                            </ConfirmDialog>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
