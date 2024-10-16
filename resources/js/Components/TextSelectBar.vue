<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import {BiChatText, RiChatVoiceLine, MdViewkanbanOutlined} from "oh-vue-icons/icons";
import {Link, usePage} from "@inertiajs/vue3";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

const { selected_server } = usePage().props;
const serverId = selected_server?.id;
</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-around" v-if="$page.props.channels && $page.props.channels.length > 0">
        <Link v-for="channel in $page.props.channels" :key="channel.id" :href="route('home.channel', {server : serverId, channel : channel.id} )">
            <button class="btn btn-wide">
                {{ channel.name }}
            </button>
        </Link>
    </div>
</template>
