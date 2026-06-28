<script lang="ts" setup>
import {baseUrl, defaultIcon} from "@/bootstrap";
import {Server} from "@/types";

defineProps<{
    selectedServer?: Server
}>();

</script>

<template>
    <div class="flex flex-row overflow-x-auto whitespace-nowrap h-[72px] items-center px-4 w-full">
        <div v-for="(user, index) in selectedServer?.users" :key="user.id" class="flex flex-row items-center flex-shrink-0">
            <div class="flex flex-col items-center mx-2">
                <div class="avatar">
                    <div class="w-10 rounded-full">
                        <img :src="user.icon ? `${baseUrl}${user.icon}` : defaultIcon" alt="user"/>
                    </div>
                </div>
                <div class="text-xs mt-1">{{ user.name }}</div>
            </div>

            <div
                v-if="index < ((selectedServer?.users?.length || 0) - 1)"
                class="divider divider-horizontal px-0 mx-2"
            />
        </div>
    </div>
</template>
