<script setup lang="ts">
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {usePage} from "@inertiajs/vue3";
import {addIcons} from "oh-vue-icons";
import {HiClipboardCopy} from "oh-vue-icons/icons";

addIcons(HiClipboardCopy);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
}

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

        <footer v-if="$page.url.match(/\/home\/\d+/) && $page.props.invite_code !== null || $page.props.invite_code !== undefined">
            <div class="toast">
                <div class="alert">
                    <span class="text-md font-bold p-2">{{ $page.props.invite_code }}</span>
                    <button class="btn tooltip" data-tip="Copy" @click="copyToClipboard($page.props.invite_code!)">
                        <v-icon name="hi-clipboard-copy"/>
                    </button>
                </div>
            </div>
        </footer>
    </div>
</template>
