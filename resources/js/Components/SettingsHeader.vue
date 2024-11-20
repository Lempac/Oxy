<script setup lang="ts">

import {Link, usePage} from "@inertiajs/vue3";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import {ref} from "vue";

const {selected_server} = defineProps<{
    selected_server: Server
}>();

const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

if (selected_server && selected_server.roles !== null){
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
        <div class="flex-1">
            <h1 class="text-2xl truncate" :title="selected_server?.name" style="max-width: 50%;">{{ selected_server?.name }}</h1>
        </div>
        <div class="flex space-x-6">
            <Link v-if="perms.has(PermType.CAN_MANAGE_SERVER)" :href="route('settings.server', { id: selected_server?.id })"
                  class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Server
            </Link>
            <Link v-if="perms.has(PermType.CAN_MANAGE_ROLE)" :href="route('settings.role', { id: selected_server?.id })"
                  class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Roles
            </Link>
            <Link v-if="perms.has(PermType.CAN_MANAGE_MEMBERS)" :href="route('settings.members', { id: selected_server?.id })"
                  class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Members
            </Link>
        </div>
    </div>
</template>
