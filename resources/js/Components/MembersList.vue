<script setup lang="ts">
import {baseUrl, defaultIcon} from "@/bootstrap";

</script>

<template>
    <div class="btm-nav h-[72px] overflow-x-auto whitespace-nowrap">
        <div v-for="(user, index) in $page.props.selected_server?.users" :key="user.id" class="flex flex-row">
            <div class="grid flex-grow place-items-center">
                <div class="avatar flex flex-col items-center mx-2">
                    <div class="w-10 rounded-full">
                        <img :src="user.icon ? `${baseUrl}${user.icon}` : defaultIcon" />
                    </div>
                    <div class="justify-center !aspect-[unset]">{{ user.name }}</div>
                </div>
            </div>

            <div v-if="index < (($page.props.selected_server?.users?.length || 0) - 1)" class="divider divider-horizontal px-0 mx-0"></div>
        </div>
    </div>
</template>
