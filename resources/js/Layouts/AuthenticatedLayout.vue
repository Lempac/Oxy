<script lang="ts" setup>
import ServerSelectBar from "@/Components/ServerSelectBar.vue";
import ChannelSelectBar from "@/Components/ChannelSelectBar.vue";
import {router, usePage} from "@inertiajs/vue3";
import {addIcons} from "oh-vue-icons";
import {HiClipboardCopy} from "oh-vue-icons/icons";
import {ref} from "vue";
import MembersList from "@/Components/MembersList.vue";
import {Perms, PermType, Role, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import echo from "@/echo";

addIcons(HiClipboardCopy);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
}

const toggle = ref(false);
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const {selectedServer} = defineProps<{
    servers?: Server[];
    inviteCode?: string,
    selectedServer?: Server,
}>();

if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleObj => roleObj.id === role.id))
        .reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

if (selectedServer) {
    echo.private(`servers.${selectedServer.id}`)
        .listen('.ServerJoined', () => {
            router.reload({only: ['selected_server']});
        })
        .listen('.ServerLeft', () => {
            router.reload({only: ['selected_server']});
        })
        .listen('.ServerEdited', () => {
            router.reload({only: ['servers', 'selected_server']});
        });

    echo.private(`roles.${selectedServer.id}`)
        .listen('.RoleDeleted', () => {
            router.reload({only: ['selected_server']});
        })
        .listen('.RoleEdited', () => {
            router.reload({only: ['selected_server']});
        });
}

</script>

<template>
    <div class="min-h-screen bg-base-200">
        <ServerSelectBar :servers/>
        <header v-if="$page.url.startsWith('/home')">
            <ChannelSelectBar :selected-server/>
        </header>

        <main>
            <slot/>
        </main>

        <footer v-if="$page.url.match(/\/home\/\d+/)">
            <div v-if="inviteCode !== undefined && perms.has(PermType.CAN_INVITE)" class="toast truncate mb-16">
                <div
                    class="alert transition-all delay-300 ease-in-out items-center justify-center gap-0 bg-base-100"
                    @mouseenter="toggle = true" @mouseleave="toggle = false">
                    <span :class="`font-bold p-2  ${toggle ? '' : 'hidden'}`">{{ inviteCode }}</span>
                    <button class="btn tooltip" data-tip="Copy" @click="copyToClipboard(inviteCode)">
                        <v-icon name="hi-clipboard-copy"/>
                    </button>
                </div>
            </div>

            <MembersList :selected-server/>
        </footer>
    </div>
</template>
