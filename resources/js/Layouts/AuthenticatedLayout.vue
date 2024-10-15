<script setup lang="ts">
import {computed} from 'vue';
import {usePage} from '@inertiajs/vue3';
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import TextSelectBar from "@/Components/TextSelectBar.vue";

const page = usePage();
const isHome = computed(() => page.url.startsWith('/home'));

</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <ServerSelectBar/>

        <header v-if="isHome">
            <ChannelSelectBar/>
            <TextSelectBar/>
        </header>

        <main>
            <slot/>
        </main>
    </div>
</template>
