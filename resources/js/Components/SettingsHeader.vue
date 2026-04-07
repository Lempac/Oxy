<script lang="ts" setup>
import {members, role, server} from '@/routes/settings';

import {Link, usePage} from "@inertiajs/vue3";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import {ref} from "vue";

const {selectedServer} = defineProps<{
    selectedServer: Server
}>();

const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleObj => roleObj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <div class="navbar bg-base-300 text-base-content rounded-lg mb-6 py-4 px-6">
        <div class="flex-1">
            <h1 :title="selectedServer?.name" class="text-2xl truncate" style="max-width: 50%;">
                {{ selectedServer?.name }}
            </h1>
        </div>
        <div class="flex space-x-6">
            <Link
                v-if="perms.has(PermType.CAN_MANAGE_SERVER)"
                :href="server.url(selectedServer?.id)"
                class="text-lg text-base-content transition-all duration-300 ease-in-out hover:bg-base-200 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Server
            </Link>
            <Link
                v-if="perms.has(PermType.CAN_MANAGE_ROLE)" :href="role.url(selectedServer?.id)"
                class="text-lg text-base-content transition-all duration-300 ease-in-out hover:bg-base-200 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Roles
            </Link>
            <Link
                v-if="perms.has(PermType.CAN_MANAGE_MEMBERS)"
                :href="members.url(selectedServer?.id)"
                class="text-lg text-base-content transition-all duration-300 ease-in-out hover:bg-base-200 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Members
            </Link>
        </div>
    </div>
</template>
