<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextSelectBar from "@/Components/TextSelectBar.vue";
import {addIcons} from "oh-vue-icons";
import {router, useForm, usePage} from "@inertiajs/vue3";
import echo from "@/echo";
import {Channel, Message, MessageType, Perms, PermType, Role, Server} from "@/types";
import axios from "axios";
import {nextTick, onMounted, onUpdated, ref, watch} from "vue";
import {
    FaRegularPaperPlane,
    MdDeleteforeverOutlined,
    MdModeeditoutlineOutlined,
    MdFileuploadOutlined,
    FaRegularFile
} from "oh-vue-icons/icons";
import {baseUrl, bigIntToPerms, defaultIcon} from "@/bootstrap";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { Filter } from 'bad-words';

const filter = new Filter({ placeHolder: '#' })
filter.addWords()

addIcons(FaRegularPaperPlane, MdDeleteforeverOutlined, MdModeeditoutlineOutlined, MdFileuploadOutlined, FaRegularFile);

const {selected_channel, messages, selected_server} = defineProps<{
    servers: Server[],
    selected_server?: Server,
    channels?: Channel[],
    selected_channel?: Channel,
    messages?: Message[],
    invite_code?: string,
}>();

let isDisabled = false;
const fileInput = ref<HTMLInputElement | null>(null);
const messageContainer = ref<HTMLElement>();
const messageModal = ref<HTMLDialogElement>();
const messageIdToEdit = ref<number | null>(null);
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));
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

const form = useForm<{ type: MessageType, mdata: File | string | null }>({
    type: MessageType.Text,
    mdata: null
});


if (selected_channel) {
    echo.private(`messages.${selected_channel.id}`)
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
    fileInput.value!.value = '';
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
        if((form.mdata as string).length > 500){
            hasError.value = true;
            return;
        }
        axios.postForm(route('message.create', {channel: selected_channel?.id}), form.data())
            .then(() => {
                clearFile();
                hasError.value = false;
            });
        await new Promise((resolve) => setTimeout(resolve, 500))
    } catch (error) {
        console.error('Error sending message:', error);
    } finally {
        loading.value = false;
    }
};

const scrollToBottom = () => {
    if (messageContainer.value) messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
};

onMounted(() => {
    scrollToBottom();
});

onUpdated(() => {
    nextTick(() => {
        scrollToBottom();
    });
});

watch(
    () => messages,
    () => {
        nextTick(() => {
            scrollToBottom();
        });
    }
);

const deleteMessage = async (messageId: number) => {
    axios.delete(route('message.delete', {message: messageId}));
};

const editMessage = async () => {
    if (messageIdToEdit.value !== null) {
        await axios.patch(route('message.edit', {message: messageIdToEdit.value}), {mdata: form.mdata});
        messageModal.value?.close();
        form.reset();
        router.reload();
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

if (selected_server && selected_server.roles !== null) {
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <AuthenticatedLayout :selected_server :invite_code :servers>
        <TextSelectBar :selected_server :selected_channel :channels/>
        <div v-if="$page.url.match(/\/text\/\d+/)"
             class="w-2/3 h-[calc(100vh-64px-80px-64px-80px-16px)] bg-white dark:bg-gray-800 m-5 rounded-lg mx-auto mt-3 flex flex-col"
        >
            <div class="overflow-y-auto flex-grow p-3 mx-5 mt-5" ref="messageContainer">
                <div v-if="messages && messages.length > 0">
                    <div v-for="message in messages" :key="message.id"
                         :class="{'chat chat-start': message.user_id !== $page.props.user?.id, 'chat chat-end': message.user_id === $page.props.user?.id}"
                    >
                        <div class="chat-image avatar">
                            <div class="w-10 rounded-full">
                                <img :src="message.sender.icon ? baseUrl + message.sender.icon : defaultIcon" alt="User Avatar" />
                            </div>
                        </div>
                        <div class="chat-header">
                            {{ message.sender.name }}
                            <time class="text-xs opacity-50">{{ formatDate(message.created_at) }}</time>
                        </div>

                        <div class="indicator">
                            <div class="chat-bubble group max-w-full bg-gray-100 text-black dark:bg-gray-900 dark:text-white">
                                <div v-if="MessageType.Text === message.type" class="text-wrap break-all max-w-[40vw]">
                                    {{ filter.clean(message.mdata) }}
                                </div>
                                <img v-if="MessageType.Image === message.type" :src="message.mdata" alt="img" class="max-w-[40vw] h-auto"/>
                                <div v-if="MessageType.File === message.type">
                                    <v-icon name="fa-regular-file"/>
                                    <a :href="baseUrl + message.mdata.split('|*|')[1]" download>
                                        {{ message.mdata.split('|*|')[0] }}
                                    </a>
                                </div>

                                <div
                                    v-if="message.user_id === $page.props.user?.id || perms.has(PermType.CAN_DELETE_MESSAGE)"
                                    class="indicator-item indicator-top absolute hidden group-hover:block"
                                    :class="{'indicator-end': message.user_id !== $page.props.user?.id, 'indicator-start': message.user_id === $page.props.user?.id}">
                                    <ConfirmDialog
                                        title="Delete Message"
                                        description="Are you sure you want to delete this message?"
                                        class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                                        :confirm="() => deleteMessage(message.id)"
                                    >
                                        <v-icon name="md-deleteforever-outlined"/>
                                    </ConfirmDialog>
                                </div>

                                <div
                                    v-if="message.user_id === $page.props.user?.id && MessageType.Text === message.type"
                                    class="indicator-item indicator-bottom absolute hidden group-hover:block"
                                    :class="{'indicator-end': message.user_id !== $page.props.user?.id, 'indicator-start': message.user_id === $page.props.user?.id}">
                                    <button @click="openModal(message.id, message.mdata)"
                                            class="indicator-item badge badge-warning h-auto w-auto p-0.5">
                                        <v-icon name="md-modeeditoutline-outlined"/>
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

            <form @submit.prevent="createMessage" class="flex items-center mt-1">
                <label for="file-upload" class="btn join-item ml-5 mb-5">
                    <v-icon name="md-fileupload-outlined"/>
                </label>
                <div class="items-center hidden">
                    <input id="file-upload" type="file" @input="uploadFile((<HTMLInputElement>$event.target).files![0])"
                           ref="fileInput"
                           :disabled="!perms.has(PermType.CAM_CREATE_ATTACHMENTS)"
                           class="file-input file-input-bordered ml-5 mb-5 focus:outline-none focus:ring-0"
                    />
                    <button @click.prevent="clearFile" :disabled="!perms.has(PermType.CAM_CREATE_ATTACHMENTS)"
                            class="btn btn-sm btn-circle btn-ghost mr-3 mb-5 ml-1">✕
                    </button>
                </div>

                <div class="join w-full items-center">
                    <input type="text" placeholder="Type here" v-model="form.mdata"
                           :disabled="loading || isDisabled || !perms.has(PermType.CAN_CREATE_MESSAGE)"
                           @keydown.enter="createMessage"
                           :class="`input input-bordered w-full join-item focus:outline-none focus:ring-0 mb-5 ${hasError ? 'input-error' : ''}`"
                    />
                    <button class="btn join-item mr-5 mb-5"
                            :disabled="!perms.hasAny(PermType.CAN_CREATE_MESSAGE | PermType.CAM_CREATE_ATTACHMENTS)"
                    >
                        <v-icon name="fa-regular-paper-plane"/>
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
                    <input v-model="form.mdata" type="text" class="input input-bordered"/>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary w-full mt-2">Edit Message</button>
                </div>
                <div class="modal-action">
                    <button @click.prevent="() => { messageModal?.close(); form.reset()}"
                            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
