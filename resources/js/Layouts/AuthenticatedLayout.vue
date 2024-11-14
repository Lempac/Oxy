<script setup lang="ts">
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {usePage} from "@inertiajs/vue3";
import {addIcons} from "oh-vue-icons";
import {HiClipboardCopy} from "oh-vue-icons/icons";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";

addIcons(HiClipboardCopy);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
}

const toggle = ref(false);

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
            <div class="toast truncate mb-16">
                <div class="alert transition-all delay-300 ease-in-out items-center justify-center gap-0 bg-white dark:bg-gray-800" @mouseenter="toggle = true" @mouseleave="toggle = false">
                    <span :class="`font-bold p-2  ${toggle ? '' : 'hidden'}`">{{ $page.props.invite_code }}</span>
                    <button class="btn tooltip" data-tip="Copy" @click="copyToClipboard($page.props.invite_code!)">
                        <v-icon name="hi-clipboard-copy"/>
                    </button>
                </div>
            </div>

            <MembersList/>
        </footer>
    </div>
</template>
