<script setup lang="ts">
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import {addIcons} from "oh-vue-icons";
import {PxPlus} from "oh-vue-icons/icons";
import {ref} from "vue";
import axios from "axios";
import {ChannelType} from "@/types";
addIcons(PxPlus);

const { selected_server } = usePage().props;
const serverId = selected_server?.id;

const channelModal = ref<HTMLDialogElement>();

const form = useForm({
    type: ChannelType.Text,
    name: ''
});

const createText = async () => {
    axios.postForm(route('channel.create', {server: serverId}), form.data()).then(() => {
        channelModal.value?.close();
        router.reload()
    });
};

</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-evenly" v-if="$page.props.channels && $page.props.channels.length > 0">
        <Link v-for="channel in $page.props.channels" :key="channel.id" :href="route('home.channel', {server : serverId, channel : channel.id} )">
            <button class="btn btn-outline btn-sm">
                {{ channel.name }}
            </button>
        </Link>
        <button class="btn btn-sm btn-square btn-outline mx-[35px]" @click="channelModal?.showModal">
            <v-icon name="px-plus"/>
        </button>
    </div>

    <dialog ref="channelModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="createText">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Text Channel Name</span>
                    </label>
                    <input v-model="form.name" type="text" placeholder="Enter channel name"
                           class="input input-bordered"/>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary w-full mt-2">Create Text Channel</button>
                </div>
            </form>
        </div>
    </dialog>
</template>
