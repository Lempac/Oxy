<script setup lang="ts">
import { addIcons } from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill, BiDoorOpen } from "oh-vue-icons/icons";
import { Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { Perms, PermType, Role, Server } from "@/types";
import { bigIntToPerms } from "@/bootstrap";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import axios from "axios";


addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined, BiGearFill, BiDoorOpen);

const serverSettingsModal = ref<HTMLDialogElement>();
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const { selected_server } = defineProps<{
  selected_server?: Server,
}>();

if (selected_server && selected_server.roles !== null){
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

function leaveServer() {
    if (!selected_server) {
        console.error('No server selected to leave.');
        return;
    }

    const serverId = selected_server.id;

    axios.delete(
        route('server.leave', { id : serverId }
    )).then(()=>{   
        window.location.reload(); 
    });
}

</script>

<template>
    <div v-if="selected_server?.id">
        <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-around">
        <!-- Leave server -->
        <ConfirmDialog 
        id="leave-server"
        title="Delete server"
        description="Are you sure you want to delete this server?"
        :confirm="leaveServer"
        class-name="left-2 mt-3 absolute btn btn-ghost hover:bg-red-500"
                ><div class="tooltip tooltip-right" data-tip="Leave server"> <v-icon name="bi-door-open" scale="1.1" /> </div>
                </ConfirmDialog> 

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

            <Link :href="route('kanban.index')">
              <button class="flex flex-col items-center justify-center gap-1 p-2 relative"
                  :class="{'border-b-2 border-white text-white': $page.url.includes('/kanban') }">
                  <svg class="h-5 w-5">
                      <v-icon name="md-viewkanban-outlined"/>
                  </svg>
                  <span class="text-sm">Kanban Board</span>
              </button>
            </Link>
        </div>
    </div>
</template>

