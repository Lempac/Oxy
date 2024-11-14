<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextSelectBar from "@/Components/TextSelectBar.vue";
import {addIcons} from "oh-vue-icons";
import {router, useForm, usePage} from "@inertiajs/vue3";
import echo from "@/echo";
import {MessageType} from "@/types";
import axios from "axios";
import {nextTick, onMounted, onUpdated, ref, watch} from "vue";
import {FaRegularPaperPlane, MdDeleteforeverOutlined, MdModeeditoutlineOutlined, MdFileuploadOutlined, FaRegularFile} from "oh-vue-icons/icons";
import {baseUrl, defaultIcon} from "@/bootstrap";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

addIcons(FaRegularPaperPlane, MdDeleteforeverOutlined, MdModeeditoutlineOutlined, MdFileuploadOutlined, FaRegularFile);

function formatDate(dateString: string): string {
    const date = new Date(dateString);

    const year = String(date.getFullYear());
    const month = String(date.getMonth()+ 1).padStart(2, '0');
    const day = String(date.getDay()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');


    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

const form = useForm<{type: MessageType , mdata: File | string | null}>({
    type: MessageType.Text,
    mdata: null
});

let channel = usePage().props.selected_channel
if(channel !== undefined){
    echo.channel(`messages.${channel?.id}`)
        .listen('.MessageCreated', () => {
            router.reload({only: ['messages']});
            scrollToBottom();
        });

    // echo.private(`messages.${channel?.id}`)
    //     .listen('MessageCreated', () => {
    //         console.log("New message!");
    //         router.reload({only: ['messages']});
    //     });
}

let isDisabled = false;
const fileInput = ref<HTMLInputElement | null>(null);

const clearFile = () => {
    fileInput.value!.value = '';
    form.reset();
    isDisabled = false;
}

let loading = ref(false);

const createMessage = async () => {
    if (loading.value) return;
    loading.value = true;

    try {
        if (typeof(form.mdata) === "string") {
            form.type = MessageType.Text;
        }
        axios.postForm(route('message.create', { channel: channel?.id }), form.data()).then(() => {
            clearFile()
        });
        await new Promise((resolve) => setTimeout(resolve, 1000))
    } catch (error) {
        console.error('Error sending message:', error);
    } finally {
        loading.value = false;
    }
};

const messageContainer = ref<HTMLElement | null>(null);

const scrollToBottom = () => {
    if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
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
    () => usePage().props.messages,
    () => {
        nextTick(() => {
            scrollToBottom();
        });
    }
);

const deleteMessage = async (messageId: number) =>  {
    axios.delete(route('message.delete', {message: messageId})).then(() => {
        router.reload();
    });
};

const messageModal = ref<HTMLDialogElement>();
const messageIdToEdit = ref<number | null>(null);

const editMessage = async () => {
    if (messageIdToEdit.value !== null) {
        await axios.patch(route('message.edit', { message: messageIdToEdit.value }), { mdata: form.mdata });
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

// doesnt work
// const modalCheck = () => {
//     return form.mdata;
// }

const inputFile = ref<File | null>();
const mdata = ref<string | null>(null);

const uploadFile = (val: File) => {
    inputFile.value = val;
    form.mdata = inputFile.value;
    if (form.mdata.type === 'image/jpeg' || form.mdata.type === 'image/png') {
        form.type = MessageType.Image;
    }
    else {
        form.type = MessageType.File;
    }
    mdata.value = URL.createObjectURL(inputFile.value);
    isDisabled = true;
}

</script>

<template>
    <AuthenticatedLayout>
      <TextSelectBar/>

      <div class="w-2/3 h-[calc(100vh-64px-80px-64px-80px-16px)] bg-white dark:bg-gray-800 m-5 rounded-lg mx-auto mt-3 flex flex-col" v-if="$page.url.match(/\/text\/\d+/)">
          <div class="overflow-y-auto flex-grow p-3 mx-5 mt-5" ref="messageContainer">
              <div v-if="$page.props.messages && $page.props.messages.length > 0">
                  <div v-for="message in $page.props.messages" :key="message.id" :class="{'chat chat-start': message.user_id !== $page.props.auth.user.id, 'chat chat-end': message.user_id === $page.props.auth.user.id}">
                      <div class="chat-image avatar">
                          <div class="w-10 rounded-full">
                              <img :src="message.sender.icon ? baseUrl + message.sender.icon : defaultIcon"
                                   alt="User Avatar"
                              />
                          </div>
                      </div>
                      <div class="chat-header">
                          {{ message.sender.name }}
                          <time class="text-xs opacity-50">{{ formatDate(message.created_at) }}</time>
                      </div>

                      <div class="indicator">
                          <div class="chat-bubble group max-w-full bg-gray-100 text-black dark:bg-gray-900 dark:text-white">
                              <div v-if="MessageType.Text === message.type">
                                  {{ message.mdata }}
                              </div>
                              <img v-if="MessageType.Image === message.type" :src="message.mdata" alt="img" class="max-w-3xl h-auto" />
                              <div v-if="MessageType.File === message.type">
                                  <v-icon name="fa-regular-file" />
                                  <a :href="baseUrl + message.mdata" download>
                                      {{ message.mdata }}
                                  </a>
                              </div>

                              <div v-if="message.user_id === $page.props.auth.user.id" class="indicator-item indicator-top absolute hidden group-hover:block" :class="{'indicator-end': message.user_id !== $page.props.auth.user.id, 'indicator-start': message.user_id === $page.props.auth.user.id}">
                                  <ConfirmDialog
                                      title="Delete Message"
                                      description="Are you sure you want to delete this message?"
                                      class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                                      :confirm="() => deleteMessage(message.id)"
                                  >
                                      <v-icon name="md-deleteforever-outlined"/>
                                  </ConfirmDialog>
                              </div>
                              <div v-if="message.user_id === $page.props.auth.user.id && MessageType.Text === message.type" class="indicator-item indicator-bottom absolute hidden group-hover:block" :class="{'indicator-end': message.user_id !== $page.props.auth.user.id, 'indicator-start': message.user_id === $page.props.auth.user.id}">
                                  <button @click="openModal(message.id, message.mdata)" class="indicator-item badge badge-warning h-auto w-auto p-0.5">
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
              </label>.
              <div class="items-center hidden">
                  <input id="file-upload" type="file" @input="uploadFile((<HTMLInputElement>$event.target).files![0])" ref="fileInput"
                         class="file-input file-input-bordered ml-5 mb-5 focus:outline-none focus:ring-0"
                  />
              </div>
              <button @click.prevent="clearFile" class="btn btn-sm btn-circle btn-ghost mr-3 mb-5 ml-1">✕</button>

              <div class="join w-full items-center">
                  <input type="text" placeholder="Type here" v-model="form.mdata" :disabled="loading || isDisabled" @keydown.enter="createMessage"
                         class="input input-bordered w-full join-item focus:outline-none focus:ring-0 mb-5"
                  />
                  <button class="btn join-item mr-5 mb-5" :disabled="loading">
                      <v-icon name="fa-regular-paper-plane" />
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
                    <button @click.prevent="() => { messageModal?.close(); form.reset()}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </div>
            </form>
        </div>
    </dialog>
</template>
