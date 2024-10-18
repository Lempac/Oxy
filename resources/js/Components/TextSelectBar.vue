<script setup lang="ts">
import {Link, usePage} from "@inertiajs/vue3";

const { selected_server } = usePage().props;
const serverId = selected_server?.id;
</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-around" v-if="$page.props.channels && $page.props.channels.length > 0">
        <Link v-for="channel in $page.props.channels" :key="channel.id" :href="route('home.channel', {server : serverId, channel : channel.id} )">
            <button class="btn btn-outline btn-sm">
                {{ channel.name }}
            </button>
        </Link>
    </div>
</template>
