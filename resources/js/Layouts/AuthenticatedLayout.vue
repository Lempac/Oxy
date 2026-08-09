<script lang="ts" setup>
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSidebar from "@/Components/ChannelSidebar.vue";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";
import {Channel, Server} from "@/types";
import {HiOutlineChevronDown, HiOutlineChevronUp} from 'vue-icons-plus/hi';
import {useServerEvents} from "@/composables/useServerEvents";
import {useUserPresence} from "@/composables/useUserPresence";
import {usePaneDrag} from "@/composables/usePaneDrag";

const { startSidebarResize } = usePaneDrag();

const {selectedServer} = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
    channels?: Channel[];
}>();

const isTopPinned = ref(false);
const isBottomPinned = ref(false);
const isTopHovered = ref(false);
const isBottomHovered = ref(false);

useServerEvents(selectedServer?.id);
useUserPresence();

</script>

<template>
    <div class="min-h-screen bg-base-200 flex flex-col h-screen overflow-hidden">
        <!-- Top Bar Container -->
        <div
            :class="(isTopPinned || isTopHovered) ? 'translate-y-0' : '-translate-y-[calc(100%-1rem)]'"
            class="fixed top-0 inset-x-0 z-50 transition-transform duration-300 ease-in-out"
            @mouseenter="isTopHovered = true"
            @mouseleave="isTopHovered = false"
        >
            <div class="bg-base-100 shadow-md border-b border-base-300">
                <ServerSelectBar :selected-server="selectedServer" :servers="servers"/>
            </div>
            <!-- Pin Button -->
            <button
                class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 bg-base-300 hover:bg-base-200 rounded-b-lg px-6 py-1 shadow-md cursor-pointer border border-t-0 border-base-300 text-base-content text-xs"
                @click="isTopPinned = !isTopPinned"
            >
                <HiOutlineChevronUp v-if="isTopPinned"/>
                <HiOutlineChevronDown v-else/>
            </button>
        </div>

        <!-- Main Middle Area (Sidebar + Viewport Content) -->
        <div
            :class="[
                isTopPinned ? 'pt-[4rem]' : 'pt-[1rem]',
                isBottomPinned ? 'pb-[5rem]' : 'pb-[1rem]'
            ]"
            class="flex-1 flex overflow-hidden transition-[padding] duration-300 ease-in-out"
        >
            <!-- Channel Navigation Sidebar -->
            <ChannelSidebar
                v-if="selectedServer"
                :channels="channels"
                :selected-server="selectedServer"
            />

            <!-- Gutter Resizer between Sidebar & Main Content -->
            <div
                v-if="selectedServer"
                class="w-1 hover:w-1.5 hover:bg-primary/50 active:bg-primary cursor-col-resize z-20 transition-all bg-base-300/80 flex-shrink-0 self-stretch select-none"
                title="Drag to resize sidebar width"
                @pointerdown.prevent="startSidebarResize"
            ></div>

            <!-- Main Content Area (Text Chat / Whiteboard / Pages) -->
            <main class="flex-1 flex flex-col overflow-y-auto min-w-0">
                <slot/>
            </main>
        </div>

        <!-- Bottom Bar Container -->
        <footer
            v-if="selectedServer"
            :class="(isBottomPinned || isBottomHovered) ? 'translate-y-0' : 'translate-y-[calc(100%-1rem)]'"
            class="fixed bottom-0 inset-x-0 z-50 transition-transform duration-300 ease-in-out"
            @mouseenter="isBottomHovered = true"
            @mouseleave="isBottomHovered = false"
        >
            <!-- Pin Button -->
            <button
                class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-base-300 hover:bg-base-200 rounded-t-lg px-6 py-1 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] cursor-pointer border border-b-0 border-base-300 text-base-content text-xs"
                @click="isBottomPinned = !isBottomPinned"
            >
                <HiOutlineChevronDown v-if="isBottomPinned"/>
                <HiOutlineChevronUp v-else/>
            </button>
            <div class="bg-base-100 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] border-t border-base-300">
                <MembersList :selected-server="selectedServer"/>
            </div>
        </footer>
    </div>
</template>
