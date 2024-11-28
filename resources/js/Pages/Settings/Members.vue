<script setup lang="ts">

import {Link, router, usePage} from "@inertiajs/vue3";
import {Perms, PermType, Role, Server, User} from "@/types";
import {BiCheckLg} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import axios from "axios";
import { GiBootKick } from "oh-vue-icons/icons";
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

const {selected_server} = defineProps<{
    selected_server: customServer,
}>();

const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const toggleRole = (roleId: number, userId: number, state: boolean) => {
    if(state){
        axios.post(route( 'roles.add-user', {role: roleId, user: userId})).then(() => {
            router.reload({only: ['selected_server']});
        })
    }else{
        axios.delete(route(  'roles.remove-user', {role: roleId, user: userId})).then(() => {
            router.reload({only: ['selected_server']});
        })
    }
};

const kickMember = (userId: number) => {
    axios.delete(route('server.removeUser', {server: selected_server.id}), {data: {'user_id': userId}}).then(() => {
        router.reload({only: ['selected_server']});
    })
}

if (selected_server && selected_server.roles !== null){
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

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
                <h1 class="text-3xl text-white mb-6">Members Settings</h1>

                <!-- Roles Table -->
                <table class="min-w-full bg-base-300 rounded-lg">
                    <thead class="bg-gray-500">
                    <tr class="text-white">
                        <th class="py-4 px-4 text-left">User Name</th>
                        <th class="py-4 px-4">Roles</th>
                        <th class="py-4 px-4 pr-7 text-end">Kick</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="user in selected_server.users" :key="user.id">
                        <td class="py-2 px-4">
                            <span>{{ user.name }}</span>
                        </td>
                        <td class="py-2 px-4 text-center">
                            <div class="dropdown">
                                <button :disabled="selected_server.roles?.length === 0" tabindex="0"
                                        data-tip="Your server doesnt have roles."
                                        class="btn m-1 {{server.roles.length === 0 ? 'tooltip' : ''}}">
                                    {{ user.rolesWithServer.map(role => role.name).join(', ') || 'None' }}
                                </button>
                                <ul tabindex="0"
                                    class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow gap-y-1">
                                    <li v-for="role in selected_server.roles"><button :disabled="!perms.has(PermType.CAN_EDIT_MEMBER_ROLES)" @click="() => toggleRole(role.id, user.id, !user.rolesWithServer.find(objRole => objRole.id === role.id))"
                                        :class="user.rolesWithServer.find(objRole => objRole.id === role.id) ? 'bg-gray-700' : ''" class="btn">
                                        <v-icon name="bi-check-lg"
                                                v-if="user.rolesWithServer.find(objRole => objRole.id === role.id)"/>
                                        {{ role.name }}</button></li>
                                </ul>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-end" v-if="user.id !== usePage().props.user?.id">
                            <ConfirmDialog title="Are you sure?" description="Are you sure you want to kick this member?" :class-name="`btn btn-error ${!perms.has(PermType.CAN_KICK) ? 'btn-disabled' : ''}`" :confirm="() => kickMember(user.id)">
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
