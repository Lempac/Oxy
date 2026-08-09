<script lang="ts" setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChannelSidebar from '@/Components/ChannelSidebar.vue';
import {Head} from '@inertiajs/vue3';
import {joinServer} from "@/bootstrap";
import {computed, ref} from "vue";
import {Server, Channel} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {usePaneDrag} from "@/composables/usePaneDrag";

const {
    draggedPaneId,
    dragHoverPaneId,
    getOrderedPanes,
    getPaneStyle,
    setDragHoverPane,
    dropOnPane,
    startGutterResize
} = usePaneDrag();

const joinCodeInput = ref('');
const serverInfo = ref<{ name: string; description: string; icon: string; members_count: number; online_count: number } | null>(null);
const checkLoading = ref(false);
const checkError = ref<string | null>(null);
const val = ref<[number, string?] | undefined>();

let checkDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const checkServerCode = () => {
    if (checkDebounceTimer) clearTimeout(checkDebounceTimer);
    checkDebounceTimer = setTimeout(async () => {
        const codeToTest = joinCodeInput.value.trim();
        if (!codeToTest) {
            serverInfo.value = null;
            checkError.value = null;
            return;
        }
        checkLoading.value = true;
        try {
            const res = await fetch(`/invites/${encodeURIComponent(codeToTest)}/check`);
            const data = await res.json();
            if (res.ok && data.valid) {
                serverInfo.value = data.server;
                checkError.value = null;
            } else {
                serverInfo.value = null;
                checkError.value = data.message || 'Invalid or expired server code.';
            }
        } catch {
            serverInfo.value = null;
            checkError.value = 'Failed to verify invite code.';
        } finally {
            checkLoading.value = false;
        }
    }, 300);
};

const props = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    inviteCode?: string
}>();

const availablePanes = computed(() => props.selectedServer ? ['sidebar', 'main'] : ['main']);
const activePanes = computed(() => getOrderedPanes(availablePanes.value));

const onPaneDragEnter = (paneId: string) => {
    if (draggedPaneId.value && draggedPaneId.value !== paneId) {
        setDragHoverPane(paneId);
    }
};

const onPaneDragOver = (paneId: string) => {
    if (draggedPaneId.value && draggedPaneId.value !== paneId) {
        setDragHoverPane(paneId);
    }
};

const onPaneDragLeave = (e: DragEvent, paneId: string) => {
    const currentTarget = e.currentTarget as HTMLElement | null;
    const relatedTarget = e.relatedTarget as Node | null;
    if (!currentTarget || !relatedTarget || !currentTarget.contains(relatedTarget)) {
        if (dragHoverPaneId.value === paneId) {
            setDragHoverPane(null);
        }
    }
};

</script>

<template>
    <Head title="Home"/>
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        <template v-for="(paneId, idx) in activePanes" :key="paneId">
            <!-- Sidebar Pane -->
            <div
                v-if="paneId === 'sidebar' && selectedServer"
                :style="getPaneStyle('sidebar', activePanes)"
                :class="[
                    'flex flex-col overflow-hidden relative transition-all duration-75',
                    dragHoverPaneId === 'sidebar' && draggedPaneId && draggedPaneId !== 'sidebar'
                        ? 'border-2 border-dashed border-primary bg-primary/10 rounded-xl'
                        : ''
                ]"
                @dragenter.prevent="onPaneDragEnter('sidebar')"
                @dragover.prevent="onPaneDragOver('sidebar')"
                @dragleave="onPaneDragLeave($event, 'sidebar')"
                @drop="dropOnPane('sidebar')"
            >
                <ChannelSidebar :channels="channels" :selected-server="selectedServer" />
            </div>

            <!-- Main Home Content Pane -->
            <div
                v-else-if="paneId === 'chat' || paneId === 'main'"
                :style="getPaneStyle('main', activePanes)"
                :class="[
                    'flex-1 flex flex-col overflow-y-auto min-w-0 transition-all duration-75',
                    dragHoverPaneId === 'chat' && draggedPaneId && draggedPaneId !== 'chat' && draggedPaneId !== 'main'
                        ? 'border-2 border-dashed border-primary bg-primary/10 rounded-xl'
                        : ''
                ]"
                @dragenter.prevent="onPaneDragEnter('main')"
                @dragover.prevent="onPaneDragOver('main')"
                @dragleave="onPaneDragLeave($event, 'main')"
                @drop="dropOnPane('main')"
            >
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                        <div
                            v-if="selectedServer?.description"
                            class="card bg-base-100 shadow-sm sm:rounded-lg">
                            <span class="card-body">{{ selectedServer?.description }}</span>
                        </div>
                        <div class="bg-base-100 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 text-base-content">
                                <span class="font-bold text-lg">Join a server!</span>
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                    <input
                                        v-model="joinCodeInput"
                                        autocomplete="off"
                                        class="input input-bordered w-full sm:w-64"
                                        data-bwignore="true"
                                        name="code"
                                        placeholder="Enter invite code"
                                        @input="checkServerCode"
                                        @blur="checkServerCode"
                                    />
                                    <button
                                        class="btn btn-primary"
                                        :disabled="!serverInfo || checkLoading"
                                        @click="async () => { val = await joinServer(joinCodeInput); if (val[0] === 200) { joinCodeInput = ''; serverInfo = null; } }">
                                        <span v-if="checkLoading" class="loading loading-spinner loading-xs"></span>
                                        Join
                                    </button>
                                </div>
                            </div>

                            <!-- Server Preview Card -->
                            <div v-if="serverInfo" class="mt-4 p-3 bg-success/10 border border-success/30 rounded-lg flex items-center gap-3">
                                <img v-if="serverInfo.icon" :src="serverInfo.icon" class="size-10 rounded-full object-cover border border-base-300" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-success truncate">Joining: {{ serverInfo.name }}</p>
                                    <p v-if="serverInfo.description" class="text-xs text-base-content/70 truncate">{{ serverInfo.description }}</p>
                                    <div class="flex items-center gap-4 mt-1 text-xs text-base-content/80 font-medium">
                                        <span class="flex items-center gap-1.5">
                                            <span class="size-2 rounded-full bg-success"></span>
                                            {{ serverInfo.online_count ?? 0 }} Online
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span class="size-2 rounded-full bg-base-content/40"></span>
                                            {{ serverInfo.members_count ?? 0 }} Members
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <ErrorAlert v-if="checkError" :message="checkError" class="mt-3" />
                        </div>
                        <div v-if="val && val[0] === 200" class="alert alert-success mt-3">
                            <span>{{ val[1] }}</span>
                        </div>
                        <ErrorAlert v-if="val && val[0] !== 200" :message="val[1]" class="mt-3"/>
                    </div>
                </div>
            </div>

            <!-- Gutter between adjacent panes -->
            <div
                v-if="idx < activePanes.length - 1"
                class="w-1.5 hover:w-2 hover:bg-primary active:bg-primary cursor-col-resize z-30 transition-all bg-base-300/80 flex-shrink-0 self-stretch select-none"
                :title="`Drag to resize`"
                @pointerdown.prevent="startGutterResize($event, activePanes[idx], activePanes[idx + 1], activePanes)"
            ></div>
        </template>
    </AuthenticatedLayout>
</template>
