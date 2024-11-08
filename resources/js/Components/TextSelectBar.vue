<script setup lang="ts">
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import axios from "axios";
import {Channel, ChannelType} from "@/types";
import {addIcons} from "oh-vue-icons";
import {OiPlus, MdDeleteforeverOutlined} from "oh-vue-icons/icons";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
addIcons(OiPlus, MdDeleteforeverOutlined);

const { selected_server } = usePage().props;
const serverId = selected_server?.id;

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<Function>();

const form = useForm({
    type: ChannelType.Text,
    name: ''
});

const openModal = (channel?: Channel) => {
    if (channel) {
        isEditing.value = true;
        form.name = channel?.name || '';
        editCurrent.value = () => editText(channel.id);
    } else {
        isEditing.value = false;
        form.name = '';
    }
    channelModal.value?.showModal();
};``

const createText = async () => {
    axios.postForm(route('channel.create', {server: serverId}), form.data()).then(() => {
        channelModal.value?.close();
        router.reload()
    });
};

const deleteText = async (channelId: number) => {
    axios.delete(route('channel.delete', {channel: channelId})).then(() => {
        router.reload()
    });
};

const editText = async (channelId: number) => {
    axios.patch(route('channel.edit', {channel: channelId}), form.data()).then(() => {
        channelModal.value?.close();
        router.reload()
    });
};

</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-evenly">
        <div class="indicator relative group" v-for="channel in $page.props.channels" :key="channel.id">
            <div class="indicator-item indicator-top absolute hidden group-hover:block">
                <ConfirmDialog
                    title="Delete Channel"
                    :description="`Are you sure you want to delete ${channel.name} channel?`"
                    class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                    :confirm="() => deleteText(channel.id)"
                >
                    <v-icon name="md-deleteforever-outlined"/>
                </ConfirmDialog>
            </div>
            <div class="indicator-item indicator-top indicator-start absolute hidden group-hover:block">
                <button @click.prevent="openModal(channel)" class="indicator-item badge badge-warning h-auto w-auto p-0.5">
                    <v-icon name="md-modeeditoutline-outlined"/>
                </button>
            </div>
            <Link :href="route('home.channel', {server : serverId, channel : channel.id})">
                <button class="btn btn-outline btn-sm" :class="{'bg-gray-400 text-black' : $page.props.selected_channel?.id === channel.id}">
                    {{ channel.name }}
                </button>
            </Link>
        </div>
        <button class="btn btn-sm btn-square btn-outline mx-[35px]" @click="openModal()">
            <v-icon name="oi-plus"/>
        </button>
    </div>

    <dialog ref="channelModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="isEditing ? editCurrent!() : createText()">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Text Channel Name</span>
                    </label>
                    <input v-model="form.name" type="text" placeholder="Enter channel name" class="input input-bordered"/>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary w-full mt-2">
                        {{ isEditing ? 'Edit Text Channel' : 'Create Text Channel' }}
                    </button>
                </div>
            </form>
            <div class="modal-action">
                <button @click="() => channelModal?.close()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </div>
        </div>
    </dialog>
</template>
