<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined } from "oh-vue-icons/icons";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

</script>

<template>
    <div class="btm-nav">
    <!--            class="active"-->
        <button>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <v-icon name="bi-chat-text" />
                </svg>
                <span class="btm-nav-label">Text Channels</span>
        </button>
        <button>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <v-icon name="ri-chat-voice-line" />
            </svg>
            <span class="btm-nav-label">Voice Channels</span>
        </button>
        <button disabled>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <v-icon name="md-viewkanban-outlined" />
            </svg>
            <span class="btm-nav-label">Kanban Board</span>
        </button>
    </div>
</template>
