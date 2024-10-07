<script setup lang="ts">
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";

</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <ServerSelectBar></ServerSelectBar>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <ChannelSelectBar></ChannelSelectBar>
    </div>
</template>
