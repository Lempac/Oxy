<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined } from "oh-vue-icons/icons";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-evenly">
            <button class="flex flex-col items-center justify-center gap-1 p-2">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <v-icon name="bi-chat-text" />
                </svg>
                <span class="text-sm">Text Channels</span>
            </button>

            <button class="flex flex-col items-center justify-center gap-1 p-2">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <v-icon name="ri-chat-voice-line" />
                </svg>
                <span class="text-sm">Voice Channels</span>
            </button>

            <button class="flex flex-col items-center justify-center gap-1 p-2">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <v-icon name="md-viewkanban-outlined" />
                </svg>
                <span class="text-sm">Kanban Board</span>
            </button>
    </div>
</template>
