<script setup lang="ts">
import { addIcons } from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill } from "oh-vue-icons/icons";
import { Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";

addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill);

const serverSettingsModal = ref<HTMLDialogElement>();
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const {selected_server} = defineProps<{
    selected_server?: Server,
}>();

if (selected_server && selected_server.roles !== null){
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <div v-if="selected_server?.id">
        <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-around">
            <Link :href="route('home.text', { server: selected_server?.id })">
                <button class="flex flex-col items-center justify-center gap-1 p-2 relative"
                    :class="{ 'border-b-2 border-black text-black dark:border-white dark:text-white': $page.url.includes('/text') }"
                >
                    <svg class="h-5 w-5">
                        <v-icon name="bi-chat-text" />
                    </svg>
                    <span class="text-sm">Text Channels</span>
                </button>
            </Link>

            <!-- Server settings -->
            <Link v-if="perms.hasAny(PermType.CAN_MANAGE_SERVER | PermType.CAN_MANAGE_ROLE | PermType.CAN_MANAGE_MEMBERS)" :href="route('settings.server', { id: selected_server?.id })" class="right-2 mt-3 absolute btn btn-ghost tooltip tooltip-left" data-tip="Server settings">
                <button @click="serverSettingsModal?.show()" class="flex items-center justify-center h-10 w-auto my-auto">
                    <v-icon name="bi-gear-fill" scale="1.1" animation="ring" hover />
                </button>
            </Link>

            <Link :href="route('home.voice', { server: selected_server?.id })">
                <button class="flex flex-col items-center justify-center gap-1 p-2 relative"
                        :class="{ 'border-b-2 border-black text-black dark:border-white dark:text-white': $page.url.includes('/voice') }"
                >
                    <svg class="h-5 w-5">
                        <v-icon name="ri-chat-voice-line" />
                    </svg>
                    <span class="text-sm">Voice Channels</span>
                </button>
            </Link>

            <button class="flex flex-col items-center justify-center gap-1 p-2 relative text-gray-600"
                :class="{ 'border-b-2 border-white text-white': $page.url.includes('/board') }">
                <svg class="h-5 w-5">
                    <v-icon name="md-viewkanban-outlined" />
                </svg>
                <span class="text-sm">Kanban Board</span>
            </button>
        </div>
    </div>
</template>

