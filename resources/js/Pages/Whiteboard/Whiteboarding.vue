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
            class="w-2/3 h-[calc(100vh-64px-80px-64px-80px-16px)] bg-base-100 m-5 rounded-lg mx-auto mt-3 flex flex-col overflow-hidden border border-base-300"
        >
            <WhiteboardBoard v-if="selectedChannel?.whiteboard" :whiteboard="selectedChannel.whiteboard" />
        </div>
        <div v-else class="w-2/3 h-[calc(100vh-64px-80px-64px-80px-16px)] bg-base-100 m-5 rounded-lg mx-auto mt-3 flex items-center justify-center text-base-content/50">
            <p>Select a whiteboard channel to start drawing!</p>
        </div>
    </AuthenticatedLayout>
</template>
