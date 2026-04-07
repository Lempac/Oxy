<script lang="ts" setup>
import {baseUrl, defaultIcon} from "@/bootstrap";
import {Server} from "@/types";

defineProps<{
    selectedServer?: Server
}>();

</script>

<template>
    <div class="h-18 overflow-x-auto whitespace-nowrap">
        <div v-for="(user, index) in selectedServer?.users" :key="user.id" class="flex flex-row">
            <div class="grid grow place-items-center">
                <div class="avatar flex flex-col items-center mx-2">
                    <div class="w-10 rounded-full">
                        <img :src="user.icon ? `${baseUrl}${user.icon}` : defaultIcon" alt="user"/>
                    </div>
                    <div class="justify-center aspect-[unset]!">{{ user.name }}</div>
                </div>
            </div>

            <!--                v-if="index < ((selectedServer?.users?.length || 0) - 1)"-->
            <div
                v-if="index < (selectedServer?.users?.length ?? 1)"
                class="divider divider-horizontal px-0 mx-0"
            />
        </div>
    </div>
</template>
