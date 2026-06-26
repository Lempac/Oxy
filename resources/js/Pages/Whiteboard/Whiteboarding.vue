<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import WhiteboardSelectBar from "@/Components/WhiteboardSelectBar.vue";
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
    <AuthenticatedLayout :invite-code :selected-server :servers>
        <WhiteboardSelectBar :channels :selected-channel :selected-server/>

        <div
            v-if="selectedChannel"
            class="flex-grow flex flex-col overflow-hidden h-[calc(100vh-64px-64px)]"
        >
            <WhiteboardBoard v-if="selectedChannel?.whiteboard" :whiteboard="selectedChannel.whiteboard" />
        </div>
        <div v-else class="flex-grow flex items-center justify-center text-base-content/50 h-[calc(100vh-64px-64px)]">
            <p>Select a whiteboard channel to start drawing!</p>
        </div>
    </AuthenticatedLayout>
</template>
