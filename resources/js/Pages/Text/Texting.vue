<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextSelectBar from "@/Components/TextSelectBar.vue";
import {addIcons} from "oh-vue-icons";
import {FaRegularPaperPlane} from "oh-vue-icons/icons";
import {router, useForm, usePage} from "@inertiajs/vue3";
import echo from "@/echo";
import {MessageType} from "@/types";
import axios from "axios";
import {nextTick, onMounted, onUpdated, ref, watch} from "vue";
import MembersList from "@/Components/MembersList.vue";

addIcons(FaRegularPaperPlane);

const baseUrl = window.location.origin;

function formatDate(dateString: string): string {
    const date = new Date(dateString);

    const year = String(date.getFullYear());
    const month = String(date.getMonth()+ 1).padStart(2, '0');
    const day = String(date.getDay()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');


    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

const form = useForm({
    type: MessageType.Text,
    mdata: ''
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
};

const createMessage = async () => {
    axios.postForm(route('message.create', { channel: channel?.id }), form.data()).then(() => form.reset());
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

</script>

<template>
  <AuthenticatedLayout>
      <TextSelectBar></TextSelectBar>

      <div class="w-2/3 h-[calc(100vh-64px-80px-64px-80px-16px)] m-5 rounded-lg dark:bg-gray-800 mx-auto mt-3 flex flex-col" v-if="$page.url.match(/\/text\/\d+/)">

          <div class="overflow-y-auto flex-grow p-3 mx-5 mt-5" ref="messageContainer">
              <div v-if="$page.props.messages && $page.props.messages.length > 0">
                  <div v-for="message in $page.props.messages.filter(messageObj => messageObj.type == MessageType.Text)" :key="message.id" :class="{'chat chat-start': message.user_id !== $page.props.auth.user.id, 'chat chat-end': message.user_id === $page.props.auth.user.id}">
                      <div class="chat-image avatar">
                          <div class="w-10 rounded-full">
                              <img :src="message.sender.icon ? baseUrl + message.sender.icon : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s'"
                                   alt="User Avatar"
                              />
                          </div>
                      </div>
                      <div class="chat-header">
                          {{ message.sender.name }}
                          <time class="text-xs opacity-50">{{ formatDate(message.created_at) }}</time>
                      </div>
                      <div class="chat-bubble">{{ message.mdata }}</div>
                  </div>
              </div>
              <div v-else>
                  <p>no messages rn :(</p>
              </div>
          </div>

          <form @submit.prevent="createMessage">
              <div class="join w-full">
                  <input type="text"
                         placeholder="Type here"
                         v-model="form.mdata"
                         class="input input-bordered w-full join-item focus:outline-none focus:ring-0 mb-5 ml-5"
                  />
                  <button class="btn join-item mr-5">
                      <v-icon name="fa-regular-paper-plane" />
                  </button>
              </div>
          </form>
      </div>

      <MembersList></MembersList>
  </AuthenticatedLayout>
</template>
