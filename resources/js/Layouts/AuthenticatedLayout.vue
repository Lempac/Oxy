<script lang="ts" setup>
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";
import {Channel, Server} from "@/types";
import {HiOutlineChevronDown, HiOutlineChevronUp} from 'vue-icons-plus/hi';
import {useServerEvents} from "@/composables/useServerEvents";

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

</script>

<template>
    <div class="min-h-screen bg-base-200">
        <!-- Top Bar Container -->
        <div
            :class="(isTopPinned || isTopHovered) ? 'translate-y-0' : '-translate-y-[calc(100%-1.5rem)]'"
            class="fixed top-0 inset-x-0 z-50 transition-transform duration-300 ease-in-out"
            @mouseenter="isTopHovered = true"
            @mouseleave="isTopHovered = false"
        >
            <div class="bg-base-100 shadow-md border-b border-base-300">
                <ServerSelectBar :selected-server="selectedServer" :servers="servers"/>
                <ChannelSelectBar
v-if="$page.url.startsWith('/home') && selectedServer"
                                  :channels="channels" :selected-server="selectedServer"/>
            </div>
            <!-- Pin Button -->
            <button
                class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 bg-base-300 hover:bg-base-200 rounded-b-lg px-6 py-1 shadow-md cursor-pointer border border-t-0 border-base-300 text-base-content"
                @click="isTopPinned = !isTopPinned"
            >
                <HiOutlineChevronUp v-if="isTopPinned"/>
                <HiOutlineChevronDown v-else/>
            </button>
        </div>

        <main
            :class="[
                isTopPinned ? (($page.url.startsWith('/home') && selectedServer) ? 'pt-[8rem]' : 'pt-[4.5rem]') : 'pt-[2rem]',
                isBottomPinned ? 'pb-[6rem]' : 'pb-[2rem]'
            ]"
            class="h-screen flex flex-col overflow-y-auto transition-all duration-300 ease-in-out"
        >
            <slot/>
        </main>

        <!-- Bottom Bar Container -->
        <footer
            v-if="selectedServer"
            :class="(isBottomPinned || isBottomHovered) ? 'translate-y-0' : 'translate-y-[calc(100%-1.5rem)]'"
            class="fixed bottom-0 inset-x-0 z-50 transition-transform duration-300 ease-in-out"
            @mouseenter="isBottomHovered = true"
            @mouseleave="isBottomHovered = false"
        >
            <!-- Pin Button -->
            <button
                class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-base-300 hover:bg-base-200 rounded-t-lg px-6 py-1 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] cursor-pointer border border-b-0 border-base-300 text-base-content"
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
