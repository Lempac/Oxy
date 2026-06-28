<script lang="ts" setup>
import { usePerms } from '@/bootstrap';

import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";
import {Perms, PermType, Role, Server, Channel} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import echo from "@/echo";
import { HiOutlineChevronDown, HiOutlineChevronUp } from 'vue-icons-plus/hi';

const perms = usePerms();
const {selectedServer} = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
    channels?: Channel[];
}>();

const isTopPinned = ref(false);
const isBottomPinned = ref(false);
const isTopHovered = ref(false);
const isBottomHovered = ref(false);

if (selectedServer) {
    echo.private(`servers.${selectedServer.id}`)
        .listen('.ServerJoined', () => {
            router.reload({only: ['selected_server']});
        })
        .listen('.ServerLeft', () => {
            router.reload({only: ['selected_server']});
        })
        .listen('.ServerEdited', () => {
            router.reload({only: ['servers', 'selected_server']});
        });

    echo.private(`roles.${selectedServer.id}`)
        .listen('.RoleDeleted', () => {
            router.reload({only: ['selected_server']});
        })
        .listen('.RoleEdited', () => {
            router.reload({only: ['selected_server']});
        });
}

</script>

<template>
    <div class="min-h-screen bg-base-200">
        <!-- Top Bar Container -->
        <div 
            class="fixed top-0 inset-x-0 z-50 transition-transform duration-300 ease-in-out"
            :class="(isTopPinned || isTopHovered) ? 'translate-y-0' : '-translate-y-[calc(100%-1.5rem)]'"
            @mouseenter="isTopHovered = true"
            @mouseleave="isTopHovered = false"
        >
            <div class="bg-base-100 shadow-md border-b border-base-300">
                <ServerSelectBar :servers="servers" :selected-server="selectedServer" />
                <ChannelSelectBar v-if="$page.url.startsWith('/home') && selectedServer" :selected-server="selectedServer" :channels="channels" />
            </div>
            <!-- Pin Button -->
            <button 
                class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 bg-base-300 hover:bg-base-200 rounded-b-lg px-6 py-1 shadow-md cursor-pointer border border-t-0 border-base-300 text-base-content"
                @click="isTopPinned = !isTopPinned"
            >
                <HiOutlineChevronUp v-if="isTopPinned" />
                <HiOutlineChevronDown v-else />
            </button>
        </div>

        <main 
            class="h-screen flex flex-col overflow-y-auto transition-all duration-300 ease-in-out"
            :class="[
                isTopPinned ? (($page.url.startsWith('/home') && selectedServer) ? 'pt-[8rem]' : 'pt-[4.5rem]') : 'pt-[2rem]',
                isBottomPinned ? 'pb-[6rem]' : 'pb-[2rem]'
            ]"
        >
            <slot/>
        </main>

        <!-- Bottom Bar Container -->
        <footer 
            v-if="selectedServer"
            class="fixed bottom-0 inset-x-0 z-50 transition-transform duration-300 ease-in-out"
            :class="(isBottomPinned || isBottomHovered) ? 'translate-y-0' : 'translate-y-[calc(100%-1.5rem)]'"
            @mouseenter="isBottomHovered = true"
            @mouseleave="isBottomHovered = false"
        >
            <!-- Pin Button -->
            <button 
                class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-base-300 hover:bg-base-200 rounded-t-lg px-6 py-1 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] cursor-pointer border border-b-0 border-base-300 text-base-content"
                @click="isBottomPinned = !isBottomPinned"
            >
                <HiOutlineChevronDown v-if="isBottomPinned" />
                <HiOutlineChevronUp v-else />
            </button>
            <div class="bg-base-100 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] border-t border-base-300">
                <MembersList :selected-server="selectedServer"/>
            </div>
        </footer>
    </div>
</template>
