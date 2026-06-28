<script lang="ts" setup>
import { usePerms, fetchJson } from '@/bootstrap';

import { create, deleteMethod, edit } from '@/routes/message';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import {router, useForm, usePage} from "@inertiajs/vue3";
import echo from "@/echo";
import {Channel, Message, MessageType, Perms, PermType, Role, Server} from "@/types";
import {nextTick, onMounted, onUpdated, ref, watch} from "vue";
import {baseUrl, bigIntToPerms, defaultIcon} from "@/bootstrap";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {Filter} from 'bad-words';
import { FaRegFile, FaRegPaperPlane } from 'vue-icons-plus/fa';
import { MdOutlineDeleteForever, MdOutlineFileUpload, MdOutlineModeEdit } from 'vue-icons-plus/md';

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


if (selectedChannel) {
    echo.private(`messages.${selectedChannel.id}`)
        .listen('.MessageCreated', () => {
            router.reload({only: ['messages']});
        })
        .listen('.MessageDeleted', () => {
            router.reload({only: ['messages']});
        })
        .listen('.MessageEdited', () => {
            router.reload({only: ['messages']});
        });
}

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
    router.delete(deleteMethod.url(messageId), { preserveScroll: true });
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
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        
        <div
            v-if="selectedChannel"
            class="w-full max-w-5xl flex-1 bg-base-100 m-5 rounded-lg mx-auto mt-0 mb-0 flex flex-col overflow-hidden"
        >
            <div ref="messageContainer" class="overflow-y-auto flex-grow p-3 mx-5 mt-5">
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
                            {{ message.sender.name }}
                            <time class="text-xs opacity-50">{{ formatDate(message.created_at) }}</time>
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

            <form class="flex items-center mt-1" @submit.prevent="createMessage">
                <label class="btn join-item ml-5 mb-5" for="file-upload">
                    <MdOutlineFileUpload/>
                </label>
                <div class="items-center hidden">
                    <input
                        id="file-upload" ref="fileInput" :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                        class="file-input file-input-bordered ml-5 mb-5 focus:outline-none focus:ring-0"
                        type="file"
                        @input="uploadFile((<HTMLInputElement>$event.target).files![0])"
                    />
                    <button
                        :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                        class="btn btn-sm btn-circle btn-ghost mr-3 mb-5 ml-1"
                        @click.prevent="clearFile">✕
                    </button>
                </div>

                <div class="join w-full items-center">
                    <input
                        v-model="form.mdata"
                        :class="`input input-bordered w-full join-item focus:outline-none focus:ring-0 mb-5 ${hasError ? 'input-error' : ''}`"
                        :disabled="loading || isDisabled || !perms.has([PermType.CAN_CREATE_MESSAGE])"
                        placeholder="Type here"
                        type="text"
                        @keydown.enter="createMessage"
                    />
                    <button
                        :disabled="!perms.hasAny([PermType.CAN_CREATE_MESSAGE, PermType.CAM_CREATE_ATTACHMENTS])"
                        class="btn join-item mr-5 mb-5"
                    >
                        <FaRegPaperPlane/>
                    </button>
                </div>
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
                    <input v-model="form.mdata" class="input input-bordered" type="text"/>
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
