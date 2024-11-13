<script setup lang="ts">

import {Link, router} from "@inertiajs/vue3";
import {Role, Server, User} from "@/types";
import {BiCheckLg} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import axios from "axios";
import { GiBootKick } from "oh-vue-icons/icons";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

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

</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- Navbar for Navigation -->
            <SettingsHeader :selected_server/>

            <div class="flex justify-end mb-6 space-x-4">
                <Link :href="route('home.server', { server: selected_server?.id })" class="btn btn-circle bg-red-500">
                    X
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
                                    <li v-for="role in selected_server.roles"><button @click="() => toggleRole(role.id, user.id, !user.rolesWithServer.find(objRole => objRole.id === role.id))"
                                        :class="user.rolesWithServer.find(objRole => objRole.id === role.id) ? 'bg-gray-700' : ''">
                                        <v-icon name="bi-check-lg"
                                                v-if="user.rolesWithServer.find(objRole => objRole.id === role.id)"/>
                                        {{ role.name }}</button></li>
                                </ul>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-end">
                            <ConfirmDialog title="Are you sure?" description="Are you sure you want to kick this member?" class-name="btn btn-error" :confirm="() => kickMember(user.id)">
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
