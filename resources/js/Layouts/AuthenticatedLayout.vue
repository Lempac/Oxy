<script setup lang="ts">
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <ServerSelectBar/>
        <header v-if="$page.url.startsWith('/home')">
            <ChannelSelectBar/>
        </header>

        <main>
            <slot/>
        </main>
    </div>
</template>
