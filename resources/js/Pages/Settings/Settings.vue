<script setup lang="ts">
    import {ref} from "vue";
    import Server from "@/Pages/Settings/Server.vue";
    import Role from "@/Pages/Settings/Role.vue";

    const serverSettingsModal = ref<HTMLDialogElement>();
    const activeTab = ref<"server" | "role">("server");

    defineExpose({serverSettingsModal})
</script>

<template>
    <dialog class="modal" ref="serverSettingsModal">
        <div class="modal-action">
            <button>

            </button>
        </div>
<!--        <Server v-if="activeTab === 'server'"/>-->
<!--        <Role v-if="activeTab === 'role'"/>-->
    </dialog>
</template>

<style scoped>

</style>
