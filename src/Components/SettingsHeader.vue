<script lang="ts" setup>
import {usePerms} from '@/bootstrap';
import {members, role, server} from '@/routes/settings';
import {PermType, Server} from "@/types";

const perms = usePerms();
defineProps<{
    selectedServer: Server
}>();
</script>

<template>
    <div class="navbar bg-base-300 text-base-content rounded-lg mb-6 py-4 px-6">
        <div class="flex-1">
            <h1 :title="selectedServer?.name" class="text-2xl truncate" style="max-width: 50%;">
                {{ selectedServer?.name }}
            </h1>
        </div>
        <div class="flex space-x-6">
            <router-link
                v-if="perms.has([PermType.CAN_MANAGE_SERVER])"
                :to="server.url(selectedServer?.route_key)"
                class="text-lg text-base-content transition-all duration-300 ease-in-out hover:bg-base-200 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Server
            </router-link>
            <router-link
                v-if="perms.has([PermType.CAN_MANAGE_ROLE])" :to="role.url(selectedServer?.route_key)"
                class="text-lg text-base-content transition-all duration-300 ease-in-out hover:bg-base-200 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Roles
            </router-link>
            <router-link
                v-if="perms.has([PermType.CAN_MANAGE_MEMBERS])"
                :to="members.url(selectedServer?.route_key)"
                class="text-lg text-base-content transition-all duration-300 ease-in-out hover:bg-base-200 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Members
            </router-link>
        </div>
    </div>
</template>
