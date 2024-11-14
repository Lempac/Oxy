<script setup lang="ts">
import { addIcons } from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill } from "oh-vue-icons/icons";
import { Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill);

const { selected_server } = usePage().props;
const serverId = selected_server?.id;

const serverSettingsModal = ref<HTMLDialogElement>();
</script>

<template>
    <div v-if="serverId">
        <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-around">
            <Link :href="route('home.text', { server: serverId })">
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
            <Link :href="route('settings.server', { serverId: serverId })" class="right-2 absolute btn btn-ghost tooltip tooltip-left" data-tip="Server settings">
                <button @click="serverSettingsModal?.show()" class="flex items-center justify-center h-10 w-auto my-auto">
                    <v-icon name="bi-gear-fill" scale="1.1" animation="ring" hover />
                </button>
            </Link>

            <Link :href="route('home.voice', { server: serverId })">
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

