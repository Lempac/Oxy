<script lang="ts" setup>
import {baseUrl, defaultIcon, getMemberRoleColor, usePerms} from '@/bootstrap';

import {create, deleteMethod, edit} from '@/routes/message';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import {router, useForm} from "@inertiajs/vue3";
import {Channel, Message, MessageType, PermType, Server} from "@/types";
import {nextTick, onMounted, onUpdated, ref, watch} from "vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {Filter} from 'bad-words';
import {FaRegFile, FaRegPaperPlane} from 'vue-icons-plus/fa';
import {MdOutlineDeleteForever, MdOutlineFileUpload, MdOutlineModeEdit, MdDragIndicator} from 'vue-icons-plus/md';
import {useMessageEvents} from "@/composables/useMessageEvents";
import {usePaneDrag} from "@/composables/usePaneDrag";

const { isDragModeActive, startPaneSwapDrag, dropOnPane } = usePaneDrag();

const filter = new Filter({placeHolder: '#'})
filter.addWords()

const perms = usePerms();
const {selectedChannel, messages, selectedServer} = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    selectedChannel?: Channel,
    messages?: Message[],
    inviteCode?: string,
}>();

let isDisabled = false;
const fileInput = ref<HTMLInputElement | null>(null);
const messageContainer = ref<HTMLElement>();
const messageModal = ref<HTMLDialogElement>();
const messageIdToEdit = ref<number | null>(null);
const inputFile = ref<File | null>();
const mdata = ref<string | null>(null);

function formatDate(dateString: string): string {
    const date = new Date(dateString);

    const year = String(date.getFullYear());
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDay()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

const form = useForm<{ type: typeof MessageType[keyof typeof MessageType], mdata: File | string | null }>({
    type: MessageType.Text,
    mdata: null
});

useMessageEvents(selectedChannel?.id);

const clearFile = () => {
    if (fileInput.value) fileInput.value.value = '';
    form.reset();
    isDisabled = false;
}

const loading = ref(false);
const hasError = ref(false);

const createMessage = async () => {
    if (loading.value) return;
    loading.value = true;
    try {
        if (typeof (form.mdata) === "string") {
            form.type = MessageType.Text;
        }
        if (typeof (form.mdata) === "string" && form.mdata.length > 500) {
            hasError.value = true;
            return;
        }

        form.post(create.url({server: selectedServer!.route_key, channel: selectedChannel!.route_key}), {
            preserveScroll: true,
            onSuccess: () => {
                clearFile();
                hasError.value = false;
            },
            onFinish: () => {
                loading.value = false;
            }
        });

    } catch (error) {
        console.error('Error sending message:', error);
        loading.value = false;
    }
};

const scrollToBottom = () => {
    if (messageContainer.value) messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
};

onMounted(() => scrollToBottom);

onUpdated(() => nextTick(() => scrollToBottom()));

watch(
    () => messages,
    () => {
        nextTick(() => {
            scrollToBottom();
        });
    }
);

const deleteMessage = async (messageId: number) => {
    router.delete(deleteMethod.url(messageId), {preserveScroll: true});
};

const editMessage = async () => {
    if (messageIdToEdit.value !== null) {
        router.patch(edit.url(messageIdToEdit.value), {mdata: form.mdata}, {
            preserveScroll: true,
            onSuccess: () => {
                messageModal.value?.close();
                form.reset();
                router.reload();
            }
        });
    }
};

const openModal = (messageId: number, messageContent: string) => {
    messageIdToEdit.value = messageId;
    form.mdata = messageContent;
    messageModal.value?.showModal();
};

const uploadFile = (val: File) => {
    inputFile.value = val;
    form.mdata = inputFile.value;
    if (form.mdata.type.startsWith('image/')) {
        form.type = MessageType.Image;
    } else {
        form.type = MessageType.File;
    }
    mdata.value = URL.createObjectURL(inputFile.value);
    isDisabled = true;
}


</script>

<template>
    <AuthenticatedLayout
        :channels="channels" :invite-code="inviteCode" :selected-server="selectedServer"
        :servers="servers">

        <div
            v-if="selectedChannel"
            class="w-full flex-1 bg-base-100 flex flex-col overflow-hidden"
            @dragover.prevent
            @drop="dropOnPane('main')"
        >
            <!-- Channel Header Bar with Drag Handle -->
            <div class="px-4 py-2 bg-base-200/50 border-b border-base-300 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-sm text-base-content">
                    <span>#</span>
                    <span>{{ selectedChannel.name }}</span>
                </div>
                <div
                    v-if="isDragModeActive"
                    draggable="true"
                    class="cursor-grab active:cursor-grabbing text-primary p-1 rounded hover:bg-base-300 transition-colors"
                    title="Drag handle: Hold Alt to drag and swap pane order"
                    @dragstart="startPaneSwapDrag('main')"
                >
                    <MdDragIndicator class="size-4" />
                </div>
            </div>
            <div ref="messageContainer" class="overflow-y-auto grow p-3 mx-5 mt-5">
                <div v-if="messages && messages.length > 0">
                    <div
                        v-for="message in messages" :key="message.id"
                        :class="{'chat chat-start': message.user_id !== $page.props.user?.id, 'chat chat-end': message.user_id === $page.props.user?.id}"
                    >
                        <div class="chat-image avatar">
                            <div class="w-10 rounded-full">
                                <img
                                    :src="message.sender.icon ? baseUrl + message.sender.icon : defaultIcon"
                                    alt="User Avatar"/>
                            </div>
                        </div>
                        <div class="chat-header">
                            <span class="font-semibold" :style="{ color: getMemberRoleColor(message.sender, selectedServer?.roles) }">{{ message.sender.nickname || message.sender.name }}</span>
                            <time class="text-xs opacity-50 ml-1">{{ formatDate(message.created_at) }}</time>
                        </div>

                        <div class="indicator">
                            <div
                                class="chat-bubble group max-w-full bg-base-200 text-base-content">
                                <div v-if="MessageType.Text === message.type" class="text-wrap break-all max-w-[40vw]">
                                    {{ filter.clean(message.mdata) }}
                                </div>
                                <img
                                    v-if="MessageType.Image === message.type" :src="message.mdata" alt="img"
                                    class="max-w-[40vw] h-auto"/>
                                <div v-if="MessageType.File === message.type">
                                    <FaRegFile/>
                                    <a :href="baseUrl + message.mdata.split('|*|')[1]" download>
                                        {{ message.mdata.split('|*|')[0] }}
                                    </a>
                                </div>

                                <div
                                    v-if="message.user_id === $page.props.user?.id || perms.has([PermType.CAN_DELETE_MESSAGE])"
                                    :class="{'indicator-end': message.user_id !== $page.props.user?.id, 'indicator-start': message.user_id === $page.props.user?.id}"
                                    class="indicator-item indicator-top absolute hidden group-hover:block">
                                    <ConfirmDialog
                                        :confirm="() => deleteMessage(message.id)"
                                        class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                                        description="Are you sure you want to delete this message?"
                                        title="Delete Message"
                                    >
                                        <MdOutlineDeleteForever/>
                                    </ConfirmDialog>
                                </div>

                                <div
                                    v-if="message.user_id === $page.props.user?.id && MessageType.Text === message.type"
                                    :class="{'indicator-end': message.user_id !== $page.props.user?.id, 'indicator-start': message.user_id === $page.props.user?.id}"
                                    class="indicator-item indicator-bottom absolute hidden group-hover:block">
                                    <button
                                        class="indicator-item badge badge-warning h-auto w-auto p-0.5"
                                        @click="openModal(message.id, message.mdata)">
                                        <MdOutlineModeEdit/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <p>no messages rn :(</p>
                </div>
            </div>

            <form class="flex items-center gap-2 p-2 border-t border-base-300 bg-base-100" @submit.prevent="createMessage">
                <label
                    :class="{'btn-disabled opacity-50': !perms.has([PermType.CAM_CREATE_ATTACHMENTS])}"
                    class="btn btn-sm btn-square btn-ghost shrink-0"
                    for="file-upload"
                >
                    <MdOutlineFileUpload class="size-4" />
                </label>
                <input
                    id="file-upload"
                    ref="fileInput"
                    :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                    autocomplete="off"
                    class="hidden"
                    data-bwignore="true"
                    type="file"
                    @input="uploadFile((<HTMLInputElement>$event.target).files![0])"
                />

                <div v-if="inputFile" class="badge badge-sm badge-primary gap-1 max-w-[150px] truncate shrink-0">
                    <span class="truncate">{{ inputFile.name }}</span>
                    <button class="btn btn-ghost btn-xs p-0 min-h-0 h-auto" @click.prevent="clearFile">✕</button>
                </div>

                <input
                    v-model="form.mdata"
                    :class="`input input-sm input-bordered flex-1 focus:outline-none ${hasError ? 'input-error' : ''}`"
                    :disabled="loading || (isDisabled && !inputFile) || !perms.has([PermType.CAN_CREATE_MESSAGE])"
                    autocomplete="off"
                    data-bwignore="true"
                    :placeholder="inputFile ? 'File ready to upload...' : 'Type message...'"
                    type="text"
                    @keydown.enter="createMessage"
                />
                <button
                    :disabled="loading || (!form.mdata && !inputFile) || !perms.hasAny([PermType.CAN_CREATE_MESSAGE, PermType.CAM_CREATE_ATTACHMENTS])"
                    class="btn btn-sm btn-square btn-primary shrink-0"
                    type="submit"
                >
                    <FaRegPaperPlane class="size-3.5" />
                </button>
            </form>
        </div>
    </AuthenticatedLayout>

    <dialog ref="messageModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="editMessage">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Editing Message</span>
                    </label>
                    <input v-model="form.mdata" autocomplete="off" class="input input-bordered" data-bwignore="true" type="text"/>
                </div>
                <div class="modal-action">
                    <button class="btn btn-primary w-full mt-2" type="submit">Edit Message</button>
                </div>
                <div class="modal-action">
                    <button
                        class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                        @click.prevent="() => { messageModal?.close(); form.reset()}">✕
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
