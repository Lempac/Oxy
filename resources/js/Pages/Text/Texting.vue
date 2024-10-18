<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextSelectBar from "@/Components/TextSelectBar.vue";
import {addIcons} from "oh-vue-icons";
import { FaRegularPaperPlane } from "oh-vue-icons/icons";
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

</script>

<template>
  <AuthenticatedLayout>
    <TextSelectBar></TextSelectBar>
      <div class="w-3/4 h-[calc(100vh-21vh)] rounded-lg dark:bg-gray-800 mx-auto mt-3 flex flex-col">

          <div class="overflow-y-auto flex-grow p-3">
              <div v-for="message in $page.props.messages" v-if="$page.props.messages == null || $page.props.messages.length > 0" :key="message.id" :class="{'chat chat-start': message.user_id !== $page.props.auth.user.id, 'chat chat-end': message.user_id === $page.props.auth.user.id}">
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
                  <div class="chat-bubble">{{ message.data }}</div>
              </div>
              <div v-else>
                  <p>No messages found in this channel.</p>
              </div>
          </div>

          <div class="join w-full">
              <input type="text"
                  placeholder="Type here"
                  class="input input-bordered w-full join-item"
              />
              <button class="btn btn-circle btn-outline join-vertical">
                    <v-icon name="fa-regular-paper-plane" />
              </button>
          </div>
      </div>

  </AuthenticatedLayout>
</template>
