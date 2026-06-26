<script lang="ts" setup>
import { usePerms } from '@/bootstrap';

import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import echo from "@/echo";
import { HiClipboardCopy } from 'vue-icons-plus/hi';


const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
}

const toggle = ref(false);

const perms = usePerms();
const {selectedServer} = defineProps<{
    servers?: Server[];
    inviteCode?: string,
    selectedServer?: Server,
}>();


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
        <ServerSelectBar :servers/>
        <header v-if="$page.url.startsWith('/home')">
            <ChannelSelectBar :selected-server/>
        </header>

        <main>
            <slot/>
        </main>

        <footer v-if="$page.url.match(/\/home\/\d+/)">
            <div v-if="inviteCode !== undefined && perms.has([PermType.CAN_INVITE])" class="toast truncate mb-16">
                <div
                    class="alert transition-all delay-300 ease-in-out items-center justify-center gap-0 bg-base-100"
                    @mouseenter="toggle = true" @mouseleave="toggle = false">
                    <span :class="`font-bold p-2  ${toggle ? '' : 'hidden'}`">{{ inviteCode }}</span>
                    <button class="btn tooltip" data-tip="Copy" @click="copyToClipboard(inviteCode)">
                        <HiClipboardCopy/>
                    </button>
                </div>
            </div>

            <MembersList :selected-server/>
        </footer>
    </div>
</template>
