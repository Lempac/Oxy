<script setup lang="ts">
import {ref} from "vue";
import Server from "@/Pages/Settings/Server.vue";
import Role from "@/Pages/Settings/Role.vue";
import {Link} from "@inertiajs/vue3";

const serverSettingsModal = ref<HTMLDialogElement>();
const activeTab = ref<"server" | "role">("server");
</script>

<template>
    <dialog class="modal" ref="serverSettingsModal">
        <div class="card bg-base-100 modal-box">
            <div class="flex flow-row bg-gray-800 p-2 gap-2">
                <h1 class="font-bold leading-[2] text-white ml-4 text-2xl text-center">{{ $page.props.selected_server!.name }}</h1>
                <div class="flex space-x-6 ml-6">
                    <button @click="activeTab = 'server'"
                            :class="`text-lg text-white transition-all duration-300 ease-in-out ${ activeTab == 'server' ? 'bg-gray-700 px-6' : 'hover:bg-gray-700 hover:pl-6 hover:pr-6' } rounded-lg btn btn-neutral`">
                        Server
                    </button>
                    <button @click="activeTab = 'role'"
                            :class="`text-lg text-white transition-all duration-300 ease-in-out ${ activeTab == 'role' ? 'bg-gray-700 px-6' : 'hover:bg-gray-700 hover:pl-6 hover:pr-6' } rounded-lg btn btn-neutral`">
                        Roles
                    </button>
                </div>
            </div>

            <div class="modal-action mb-4 bg-gray-800 card p-2 gap-1 flex-row max-w-fit">
                <button class="btn">Save Changes</button>
                <button class="btn" @click="serverSettingsModal?.close()">Close</button>
            </div>
            <Server v-if="activeTab === 'server'"/>
            <Role v-if="activeTab === 'role'"/>
        </div>
    </dialog>
</template>

<style scoped>

</style>
