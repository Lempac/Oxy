<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {baseUrl, defaultIcon} from "@/bootstrap";
import axios from "axios";
import {ref} from "vue";
import {Channel, ChannelType} from "@/types";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {addIcons} from "oh-vue-icons";
import {OiPlus, MdDeleteforeverOutlined, MdModeeditoutlineOutlined} from "oh-vue-icons/icons";

addIcons(OiPlus, MdDeleteforeverOutlined, MdModeeditoutlineOutlined);

const { selected_server } = usePage().props;
const serverId = selected_server?.id;

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<Function>();

const form = useForm({
    type: ChannelType.Voice,
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
};

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
    <AuthenticatedLayout>

        <div class="mt-3 pb-20">
            <div class="flex items-center justify-center mx-auto text-5xl my-5">IN DEVELOPMENT!!!</div>
            <div class="indicator relative group w-2/3 h-auto mx-auto flex items-center justify-center m-7" v-for="channel in $page.props.channels" :key="channel.id">
                <span class="indicator-item indicator-top absolute hidden group-hover:block">
                    <ConfirmDialog
                        title="Delete Channel"
                        :description="`Are you sure you want to delete ${channel.name} channel?`"
                        class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                        :confirm="() => deleteText(channel.id)"
                    >
                        <v-icon name="md-deleteforever-outlined"/>
                    </ConfirmDialog>
                </span>

                <span class="indicator-item indicator-top indicator-start absolute hidden group-hover:block">
                    <button @click.prevent="openModal(channel)" class="indicator-item badge badge-warning h-auto w-auto p-0.5">
                        <v-icon name="md-modeeditoutline-outlined"/>
                    </button>
                </span>

                <div class="w-full rounded-lg bg-white dark:bg-gray-800">
                    <div class="flex items-center justify-center text-xl pt-2">
                        {{channel.name}}
                    </div>

                    <div class="grid grid-cols-3 gap-1 p-3">
                        <div v-for="user in $page.props.selected_server?.users" :key="user.id" class="avatar rounded-lg items-center justify-center h-16 bg-gray-100 dark:bg-gray-700">
                            <div class="flex w-10 h-auto rounded-full ml-5">
                                <img :src="user.icon ? `${baseUrl}${user.icon}` : defaultIcon" />
                            </div>
                            <div class="flex items-center h-full w-full p-4">
                                {{ user.name }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="btn btn-success btn-wide mr-3 mb-3">Join</button>
                    </div>
                </div>
            </div>

            <button class="btn w-2/3 h-auto p-3 rounded-lg mx-auto flex items-center justify-center bg-white dark:bg-gray-800" @click="openModal()">
                <v-icon name="oi-plus" scale="3" />
            </button>

        </div>

    </AuthenticatedLayout>

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
