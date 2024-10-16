<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined } from "oh-vue-icons/icons";
import { Link, usePage } from "@inertiajs/vue3";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

const { selected_server } = usePage().props;
const serverId = selected_server?.id;

</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-evenly">
        <Link :href="route('home.text', {server : serverId} )">
            <button class="flex flex-col items-center justify-center gap-1 p-2" >
                <svg class="h-5 w-5">
                    <v-icon name="bi-chat-text" />
                </svg>
                <span class="text-sm">Text Channels</span>
            </button>
        </Link>

<!--        :href="route('home.voice', {server : serverId} )"-->
        <Link>
            <button class="flex flex-col items-center justify-center gap-1 p-2">
                <svg class="h-5 w-5">
                    <v-icon name="ri-chat-voice-line" />
                </svg>
                <span class="text-stextm">Voice Channels</span>
            </button>
        </Link>

<!--        :href="route('home.board', {server : serverId} )"-->
        <Link>
            <button class="flex flex-col items-center justify-center gap-1 p-2">
                <svg class="h-5 w-5">
                    <v-icon name="md-viewkanban-outlined" />
                </svg>
                <span class="text-sm">Kanban Board</span>
            </button>
        </Link>
    </div>
</template>
