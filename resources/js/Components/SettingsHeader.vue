<script setup lang="ts">

import {Link} from "@inertiajs/vue3";
import {Server} from "@/types";

defineProps<{
    selected_server: Server
}>();

</script>

<template>
    <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
        <div class="flex-1">
            <h1 class="text-2xl truncate" :title="selected_server?.name" style="max-width: 50%;">{{ selected_server?.name }}</h1>
        </div>
        <div class="flex space-x-6">
            <Link :href="route('settings.server', { id: selected_server?.id })"
                  class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Server
            </Link>
            <Link :href="route('settings.role', { id: selected_server?.id })"
                  class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Roles
            </Link>
            <Link :href="route('settings.members', { id: selected_server?.id })"
                  class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                Members
            </Link>
        </div>
    </div>
</template>
