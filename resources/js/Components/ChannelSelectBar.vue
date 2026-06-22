<script lang="ts" setup>
import { FaCog, FaRegCommentAlt, FaDoorOpen, FaChalkboard } from "vue-icons-plus/fa";
import { RiChatVoiceLine } from "vue-icons-plus/ri";
import { text, voice } from '@/routes/home';
import { whiteboard } from '@/routes/home';
import { leave } from '@/routes/server';
import { server } from '@/routes/settings';
import {Link, router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import axios from "axios";


const serverSettingsModal = ref<HTMLDialogElement>();
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));
const {selectedServer} = defineProps<{
    selectedServer?: Server,
}>();

if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

function leaveServer() {
    if (!selectedServer) {
        console.error('No server selected to leave.');
        return;
    }

    axios.delete(
        leave.url(selectedServer.id)).then(() => {
        router.reload();
    });
}

</script>

<template>
    <div v-if="selectedServer?.id">
        <div class="navbar bg-base-100 border-b border-base-300 justify-around">
            <!-- Leave server -->
            <ConfirmDialog
                id="leave-server"
                :confirm="leaveServer"
                class-name="left-2 mt-3 absolute btn btn-ghost hover:bg-red-500"
                description="Are you sure you want to leave this server?"
                title="Leave server"
            >
                <div class="tooltip tooltip-right" data-tip="Leave server">
                    <FaDoorOpen size="26"/>
                </div>
            </ConfirmDialog>

            <Link :href="text.url(selectedServer?.id)">
                <button
                    :class="{ 'border-b-2 border-base-content text-base-content': $page.url.includes('/text') }"
                    class="flex flex-col items-center justify-center gap-1 p-2 relative"
                >
                    <svg class="h-5 w-5">
                        <FaRegCommentAlt/>
                    </svg>
                    <span class="text-sm">Text Channels</span>
                </button>
            </Link>
            <!-- Server settings -->
            <Link
                v-if="perms.hasAny(PermType.CAN_MANAGE_SERVER | PermType.CAN_MANAGE_ROLE | PermType.CAN_MANAGE_MEMBERS)"
                :href="server.url(selectedServer?.id)"
                class="right-2 mt-3 absolute btn btn-ghost tooltip tooltip-left" data-tip="Server settings">
                <button
                    class="flex items-center justify-center h-10 w-auto my-auto"
                    @click="serverSettingsModal?.show()">
                    <FaCog size="26"/>
                </button>
            </Link>

            <Link :href="voice.url(selectedServer?.id)">
                <button
                    :class="{ 'border-b-2 border-base-content text-base-content': $page.url.includes('/voice') }"
                    class="flex flex-col items-center justify-center gap-1 p-2 relative"
                >
                    <svg class="h-5 w-5">
                        <RiChatVoiceLine/>
                    </svg>
                    <span class="text-sm">Voice Channels</span>
                </button>
            </Link>

            <Link :href="whiteboard.url(selectedServer?.id!)">
                <button
                    :class="{'border-b-2 border-base-content text-base-content': $page.url.includes('/whiteboard') }"
                    class="flex flex-col items-center justify-center gap-1 p-2 relative">
                    <svg class="h-5 w-5">
                        <FaChalkboard/>
                    </svg>
                    <span class="text-sm">Whiteboard</span>
                </button>
            </Link>
        </div>
    </div>
</template>

