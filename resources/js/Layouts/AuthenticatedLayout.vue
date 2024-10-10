<script setup lang="ts">
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import TextSelectBar from "@/Components/TextSelectBar.vue";

</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <ServerSelectBar></ServerSelectBar>
            <ChannelSelectBar></ChannelSelectBar>
            <TextSelectBar></TextSelectBar>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
