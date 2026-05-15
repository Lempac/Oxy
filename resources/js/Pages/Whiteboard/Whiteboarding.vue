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
            v-if="$page.url.match(/\/whiteboard\/\d+/)"
            class="flex-grow h-[calc(100vh-64px-64px)] m-5 rounded-lg overflow-hidden border border-base-300"
        >
            <WhiteboardBoard v-if="selectedChannel?.whiteboard" :whiteboard="selectedChannel.whiteboard" />
        </div>
        <div v-else class="flex items-center justify-center h-[calc(100vh-64px-64px)] text-base-content/50">
            <p>Select a whiteboard channel to start drawing!</p>
        </div>
    </AuthenticatedLayout>
</template>
