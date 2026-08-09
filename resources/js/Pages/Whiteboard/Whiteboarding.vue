<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Channel, Message, MessageType, PermType, Server, Whiteboard} from "@/types";
import WhiteboardBoard from "./WhiteboardBoard.vue";
import {ref} from "vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {baseUrl, defaultIcon, getMemberRoleColor, usePerms} from "@/bootstrap";
import {Filter} from 'bad-words';
import {FaRegFile, FaRegPaperPlane} from 'vue-icons-plus/fa';
import {MdOutlineDeleteForever, MdOutlineFileUpload, MdOutlineModeEdit, MdDragIndicator} from 'vue-icons-plus/md';
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {create, deleteMethod, edit} from "@/routes/message";
import {usePaneDrag} from "@/composables/usePaneDrag";

const filter = new Filter({placeHolder: '#'});
filter.addWords();

const perms = usePerms();
const { isDragModeActive, paneOrder, chatPaneWidth, startPaneSwapDrag, dropOnPane, startSplitResize } = usePaneDrag();
const isMaximized = ref(false);
const splitContainer = ref<HTMLElement | null>(null);

const onSplitResizeStart = (e: PointerEvent) => {
    if (splitContainer.value) {
        startSplitResize(e, splitContainer.value.offsetWidth);
    }
};

const props = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    selectedChannel?: Channel & { whiteboard: Whiteboard },
    messages?: Message[],
    inviteCode?: string,
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const messageIdToEdit = ref<number | null>(null);
const inputFile = ref<File | null>(null);

const clearFile = () => {
    if (fileInput.value) fileInput.value.value = '';
    inputFile.value = null;
    form.mdata = null;
    form.type = MessageType.Text;
};

const uploadFile = (val: File) => {
    if (!val) return;
    inputFile.value = val;
    form.mdata = inputFile.value;
    if (form.mdata.type.startsWith('image/')) {
        form.type = MessageType.Image;
    } else {
        form.type = MessageType.File;
    }
};

const form = useForm<{ type: typeof MessageType[keyof typeof MessageType], mdata: File | string | null }>({
    type: MessageType.Text,
    mdata: null
});

const createMessage = async () => {
    if (!props.selectedChannel || !props.selectedServer) return;
    if (typeof form.mdata === 'string') {
        form.type = MessageType.Text;
    }
    form.post(create.url({server: props.selectedServer.route_key, channel: props.selectedChannel.route_key}), {
        preserveScroll: true,
        onSuccess: () => {
            clearFile();
        }
    });
};

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = String(date.getFullYear());
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}
</script>

<template>
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        <div
            v-if="selectedChannel"
            ref="splitContainer"
            class="flex-grow flex w-full h-full overflow-hidden p-0"
        >
            <!-- Chat Pane (Split View - visible when not maximized) -->
            <div
                v-if="!isMaximized"
                :style="{ order: paneOrder.indexOf('chat'), flexBasis: chatPaneWidth + '%', flexGrow: 0, flexShrink: 0 }"
                class="bg-base-100 flex flex-col overflow-hidden border-r border-base-300 min-w-[250px]"
                @dragover.prevent
                @drop="dropOnPane('chat')"
            >
                <div class="px-4 py-2 bg-base-200/50 border-b border-base-300 flex items-center justify-between">
                    <div class="font-bold text-sm text-base-content flex items-center gap-1.5">
                        <span>#</span>
                        <span>{{ selectedChannel.name }}</span>
                    </div>
                    <div
                        v-if="isDragModeActive"
                        draggable="true"
                        class="cursor-grab active:cursor-grabbing text-primary p-1 rounded hover:bg-base-300 transition-colors"
                        title="Drag handle: Hold Alt to swap pane positions"
                        @dragstart="startPaneSwapDrag('chat')"
                    >
                        <MdDragIndicator class="size-4" />
                    </div>
                </div>

                <!-- Messages Stream -->
                <div class="overflow-y-auto grow p-3 space-y-2">
                    <div v-if="messages && messages.length > 0">
                        <div
                            v-for="message in messages" :key="message.id"
                            :class="{'chat chat-start': message.user_id !== $page.props.user?.id, 'chat chat-end': message.user_id === $page.props.user?.id}"
                        >
                            <div class="chat-image avatar">
                                <div class="w-8 rounded-full">
                                    <img :src="message.sender?.icon ? baseUrl + message.sender.icon : defaultIcon" alt="User Avatar"/>
                                </div>
                            </div>
                            <div class="chat-header text-xs">
                                <span class="font-semibold" :style="{ color: getMemberRoleColor(message.sender, selectedServer?.roles) }">{{ message.sender?.nickname || message.sender?.name }}</span>
                                <time class="opacity-50 ml-1">{{ formatDate(message.created_at) }}</time>
                            </div>
                            <div class="chat-bubble bg-base-200 text-base-content text-xs">
                                <div v-if="MessageType.Text === message.type" class="text-wrap break-all">
                                    {{ message.mdata ? filter.clean(message.mdata) : '' }}
                                </div>
                                <img
                                    v-if="MessageType.Image === message.type" :src="message.mdata" alt="img"
                                    class="max-w-[200px] h-auto rounded"/>
                                <div v-if="MessageType.File === message.type" class="flex items-center gap-1">
                                    <FaRegFile/>
                                    <a :href="baseUrl + message.mdata.split('|*|')[1]" class="underline truncate max-w-[150px]" download>
                                        {{ message.mdata.split('|*|')[0] }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-base-content/50 p-2">
                        <span>No messages yet in this channel.</span>
                    </div>
                </div>

                <!-- Chat Input Form -->
                <form class="flex items-center gap-2 p-2 border-t border-base-300 bg-base-100" @submit.prevent="createMessage">
                    <label
                        :class="{'btn-disabled opacity-50': !perms.has([PermType.CAM_CREATE_ATTACHMENTS])}"
                        class="btn btn-sm btn-square btn-ghost shrink-0"
                        for="whiteboard-file-upload"
                    >
                        <MdOutlineFileUpload class="size-4" />
                    </label>
                    <input
                        id="whiteboard-file-upload"
                        ref="fileInput"
                        :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                        autocomplete="off"
                        class="hidden"
                        data-bwignore="true"
                        type="file"
                        @input="uploadFile((<HTMLInputElement>$event.target).files![0])"
                    />

                    <div v-if="inputFile" class="badge badge-sm badge-primary gap-1 max-w-[120px] truncate shrink-0">
                        <span class="truncate">{{ inputFile.name }}</span>
                        <button class="btn btn-ghost btn-xs p-0 min-h-0 h-auto" @click.prevent="clearFile">✕</button>
                    </div>

                    <input
                        v-model="form.mdata"
                        class="input input-sm input-bordered flex-1 focus:outline-none"
                        :placeholder="inputFile ? 'File ready to upload...' : 'Type message...'"
                        type="text"
                        @keydown.enter="createMessage"
                    />
                    <button
                        :disabled="!form.mdata && !inputFile"
                        class="btn btn-sm btn-square btn-primary shrink-0"
                        type="submit"
                    >
                        <FaRegPaperPlane class="size-3.5" />
                    </button>
                </form>
            </div>

            <!-- Split Gutter Resizer (between chat and whiteboard) -->
            <div
                v-if="!isMaximized"
                class="w-1 hover:w-1.5 cursor-col-resize bg-base-300/80 hover:bg-primary/50 active:bg-primary transition-all flex-shrink-0 self-stretch z-10 select-none"
                title="Drag to resize chat/whiteboard split"
                @pointerdown="onSplitResizeStart"
            ></div>

            <!-- Whiteboard Canvas Pane -->
            <div
                :style="{ order: paneOrder.indexOf('whiteboard') }"
                class="flex-1 bg-base-100 flex flex-col overflow-hidden min-w-[300px]"
                @dragover.prevent
                @drop="dropOnPane('whiteboard')"
            >
                <WhiteboardBoard
                    :selected-channel="selectedChannel"
                    :selected-server="selectedServer"
                    :whiteboard="selectedChannel.whiteboard"
                    @toggle-maximize="isMaximized = !isMaximized"
                />
            </div>
        </div>
        <div v-else class="flex-grow flex items-center justify-center text-base-content/50 w-full h-full">
            <p>Select a whiteboard channel to start drawing!</p>
        </div>
    </AuthenticatedLayout>
</template>
