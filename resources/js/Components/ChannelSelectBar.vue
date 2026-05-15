<script lang="ts" setup>
import { text, voice } from '@/routes/home';
import { index } from '@/routes/kanban';
import { leave } from '@/routes/server';
import { server } from '@/routes/settings';
import {addIcons} from "oh-vue-icons";
import {BiChatText, BiDoorOpen, BiGearFill, MdViewkanbanOutlined, RiChatVoiceLine} from "oh-vue-icons/icons";
import {Link, router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill, BiDoorOpen);

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

    router.delete(
        leave.url(selectedServer.slug), {
        onSuccess: () => {
            router.reload();
        }
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
                    <v-icon name="bi-door-open" scale="1.1"/>
                </div>
            </ConfirmDialog>

            <Link :href="text.url(selectedServer?.slug)">
                <button
                    :class="{ 'border-b-2 border-base-content text-base-content': $page.url.includes('/text') }"
                    class="flex flex-col items-center justify-center gap-1 p-2 relative"
                >
                    <svg class="h-5 w-5">
                        <v-icon name="bi-chat-text"/>
                    </svg>
                    <span class="text-sm">Text Channels</span>
                </button>
            </Link>
            <!-- Server settings -->
            <Link
                v-if="perms.hasAny(PermType.CAN_MANAGE_SERVER | PermType.CAN_MANAGE_ROLE | PermType.CAN_MANAGE_MEMBERS)"
                :href="server.url(selectedServer?.slug)"
                class="right-2 mt-3 absolute btn btn-ghost tooltip tooltip-left" data-tip="Server settings">
                <button
                    class="flex items-center justify-center h-10 w-auto my-auto"
                    @click="serverSettingsModal?.show()">
                    <v-icon animation="ring" hover name="bi-gear-fill" scale="1.1"/>
                </button>
            </Link>

            <Link :href="voice.url(selectedServer?.slug)">
                <button
                    :class="{ 'border-b-2 border-base-content text-base-content': $page.url.includes('/voice') }"
                    class="flex flex-col items-center justify-center gap-1 p-2 relative"
                >
                    <svg class="h-5 w-5">
                        <v-icon name="ri-chat-voice-line"/>
                    </svg>
                    <span class="text-sm">Voice Channels</span>
                </button>
            </Link>

            <Link :href="index.url()">
                <button
                    :class="{'border-b-2 border-base-content text-base-content': $page.url.includes('/kanban') }"
                    class="flex flex-col items-center justify-center gap-1 p-2 relative">
                    <svg class="h-5 w-5">
                        <v-icon name="md-viewkanban-outlined"/>
                    </svg>
                    <span class="text-sm">Kanban Board</span>
                </button>
            </Link>
        </div>
    </div>
</template>

