<script lang="ts" setup>
import { Channel, Server, Whiteboard } from "@/types";
import WhiteboardBoard from "./WhiteboardBoard.vue";
import { ref } from "vue";

const isMaximized = ref(false);

const props = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
    channels?: Channel[];
    selectedChannel?: Channel & { whiteboard?: Whiteboard };
}>();
</script>

<template>
    <div class="h-full flex flex-col w-full relative">
        <WhiteboardBoard
            v-if="selectedChannel"
            :selected-channel="selectedChannel"
            :selected-server="selectedServer"
            :whiteboard="selectedChannel.whiteboard!"
            @toggle-maximize="isMaximized = !isMaximized"
        />
        <div v-else class="h-full flex items-center justify-center text-base-content/60">
            No whiteboard channel selected
        </div>
    </div>
</template>
