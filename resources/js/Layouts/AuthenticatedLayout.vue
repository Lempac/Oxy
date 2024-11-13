<script setup lang="ts">
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {usePage} from "@inertiajs/vue3";
import {addIcons} from "oh-vue-icons";
import {HiClipboardCopy} from "oh-vue-icons/icons";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";
import {Server} from "@/types";

addIcons(HiClipboardCopy);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
}

const toggle = ref(false);

defineProps<{
    servers?: Server[];
    invite_code?: string,
    selected_server?: Server,
}>();

</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <ServerSelectBar :servers/>
        <header v-if="$page.url.startsWith('/home')">
            <ChannelSelectBar :server-id="selected_server?.id"/>
        </header>

        <main>
            <slot/>
        </main>

        <footer v-if="$page.url.match(/\/home\/\d+/)">
            <div v-if="invite_code !== undefined" class="toast truncate mb-16">
                <div class="alert transition-all delay-300 ease-in-out items-center justify-center gap-0"
                     @mouseenter="toggle = true" @mouseleave="toggle = false">
                    <span :class="`font-bold p-2  ${toggle ? '' : 'hidden'}`">{{ invite_code }}</span>
                    <button class="btn tooltip" data-tip="Copy" @click="copyToClipboard(invite_code)">
                        <v-icon name="hi-clipboard-copy"/>
                    </button>
                </div>
            </div>

            <MembersList :selected_server/>
        </footer>
    </div>
</template>
