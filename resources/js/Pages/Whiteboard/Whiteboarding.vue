<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import {Channel, Server, Whiteboard} from "@/types";
import WhiteboardBoard from "./WhiteboardBoard.vue";

const {selectedChannel, selectedServer} = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    selectedChannel?: Channel & { whiteboard: Whiteboard },
    inviteCode?: string,
}>();

</script>

<template>
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        

        <div
            v-if="selectedChannel"
            class="flex-grow flex flex-col overflow-hidden w-full h-full"
        >
            <WhiteboardBoard :selected-channel :selected-server :whiteboard="selectedChannel.whiteboard"/>
        </div>
        <div v-else class="flex-grow flex items-center justify-center text-base-content/50 w-full h-full">
            <p>Select a whiteboard channel to start drawing!</p>
        </div>
    </AuthenticatedLayout>
</template>
