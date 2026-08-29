<script lang="ts" setup>
import { Channel, Server, Whiteboard as WhiteboardType } from "@/types";
import WhiteboardBoard from "./WhiteboardBoard.vue";
import { onMounted, ref, watch } from "vue";
import pb from '@/pocketbase';

const isMaximized = ref(false);

const props = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
    channels?: Channel[];
    selectedChannel?: Channel & { whiteboard?: WhiteboardType };
}>();

const activeWhiteboard = ref<WhiteboardType | undefined>(props.selectedChannel?.whiteboard);
const loading = ref(false);

const loadWhiteboardData = async () => {
    if (!props.selectedChannel?.id) {
        activeWhiteboard.value = undefined;
        return;
    }
    loading.value = true;
    try {
        let wbRec;
        try {
            wbRec = await pb.collection('whiteboards').getFirstListItem(`channel = "${props.selectedChannel.id}"`, { requestKey: null });
        } catch {
            // Create initial whiteboard record for this channel if not existing
            wbRec = await pb.collection('whiteboards').create({
                channel: props.selectedChannel.id,
                data: JSON.stringify({ shapes: [] }),
                sync_status: 'synced'
            }, { requestKey: null });
        }
        activeWhiteboard.value = {
            id: wbRec.id,
            channel_id: wbRec.channel,
            data: wbRec.data,
            sync_status: wbRec.sync_status || 'synced'
        } as unknown as WhiteboardType;
    } catch (err) {
        console.error('Error loading whiteboard data:', err);
    } finally {
        loading.value = false;
    }
};

watch(() => props.selectedChannel?.id, () => {
    loadWhiteboardData();
}, { immediate: true });
</script>

<template>
    <div class="h-full flex flex-col w-full relative">
        <WhiteboardBoard
            v-if="selectedChannel && activeWhiteboard"
            :selected-channel="selectedChannel"
            :selected-server="selectedServer"
            :whiteboard="activeWhiteboard"
            @toggle-maximize="isMaximized = !isMaximized"
        />
        <div v-else-if="loading" class="h-full flex items-center justify-center text-base-content/60 gap-2">
            <span class="loading loading-spinner loading-md text-primary"></span>
            <span>Loading Whiteboard...</span>
        </div>
        <div v-else class="h-full flex items-center justify-center text-base-content/60">
            No whiteboard channel selected
        </div>
    </div>
</template>
