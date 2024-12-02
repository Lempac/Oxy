<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import {baseUrl, bigIntToPerms, defaultIcon} from "@/bootstrap";
import axios from "axios";
import {ref} from "vue";
import {Channel, ChannelType, Perms, PermType, Role, Server} from "@/types";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {addIcons} from "oh-vue-icons";
import {OiPlus, MdDeleteforeverOutlined, MdModeeditoutlineOutlined} from "oh-vue-icons/icons";

addIcons(OiPlus, MdDeleteforeverOutlined, MdModeeditoutlineOutlined);

const {selected_server} = defineProps<{
    servers: Server[],
    selected_server?: Server,
    channels?: Channel[],
    invite_code?: string,
}>()

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<Function>();
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

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
    axios.postForm(route('channel.create', {server: selected_server?.id}), form.data()).then(() => {
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

if (selected_server && selected_server.roles !== null){
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

let isInVoice = ref(false);
let mediaRecorder: MediaRecorder | undefined;
const chunks: Blob[] = [];

const joinChannel = async () => {
    isInVoice.value = true;
    if (isInVoice.value) {
        try {
            const stream: MediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);

            mediaRecorder.start(100);
            console.log("Recording started");

            mediaRecorder.ondataavailable = (event: BlobEvent) => {
                chunks.push(event.data);
                console.log(chunks)
            };

        } catch (error) {
            console.error("Error accessing microphone:", error);
        }
    }
};

const leaveChannel = async () => {
    isInVoice.value = false;
    if (!isInVoice.value) {
        mediaRecorder?.stop();
        console.log("Recording stopped");
        console.log(chunks)
    }
}

</script>

<template>
    <AuthenticatedLayout :selected_server :servers :invite_code>
        <div class="mt-3 pb-20">
            <div class="indicator relative group w-2/3 h-auto mx-auto flex items-center justify-center m-7"
                 v-for="channel in channels" :key="channel.id">
                <span class="indicator-item indicator-top absolute hidden group-hover:block" v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_DELETE_CHANNEL)">
                    <ConfirmDialog title="Delete Channel"
                        :description="`Are you sure you want to delete ${channel.name} channel?`"
                        class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                        :confirm="() => deleteText(channel.id)"
                    >
                        <v-icon name="md-deleteforever-outlined"/>
                    </ConfirmDialog>
                </span>

                <span class="indicator-item indicator-top indicator-start absolute hidden group-hover:block" v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_EDIT_CHANNEL)">
                    <button @click.prevent="openModal(channel)"
                            class="indicator-item badge badge-warning h-auto w-auto p-0.5"
                    >
                        <v-icon name="md-modeeditoutline-outlined"/>
                    </button>
                </span>

                <div class="w-full rounded-lg bg-white dark:bg-gray-800">
                    <div class="flex items-center justify-center text-xl pt-2">
                        {{ channel.name }}
                    </div>

                    <div class="grid grid-cols-3 gap-1 p-3">
                        <div v-for="user in selected_server?.users" :key="user.id"
                             class="avatar rounded-lg items-center justify-center h-16 bg-gray-100 dark:bg-gray-700"
                        >
                            <div class="flex w-10 h-auto rounded-full ml-5">
                                <img :src="user.icon ? `${baseUrl}${user.icon}` : defaultIcon"/>
                            </div>
                            <div class="flex items-center h-full w-full p-4">
                                {{ user.name }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
<!--                        <Link :href="route('home.voice.channel', {server : selected_server?.id, channel : channel.id})">-->
                            <button v-if="!isInVoice" @click="joinChannel" class="btn btn-success btn-wide mr-3 mb-3">
                                Join
                            </button>
                            <button v-else @click="leaveChannel" class="btn btn-error btn-wide mr-3 mb-3">
                                Leave
                            </button>
<!--                        </Link>-->
                    </div>
                </div>
            </div>

            <button class="btn w-2/3 h-auto p-3 rounded-lg mx-auto flex items-center justify-center bg-white dark:bg-gray-800"
                    @click="openModal()" v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_CREATE_CHANNEL)">
                <v-icon name="oi-plus" scale="3"/>
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
                    <input v-model="form.name" type="text" placeholder="Enter channel name"
                           class="input input-bordered"/>
                </div>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary w-full mt-2">
                        {{ isEditing ? 'Edit Text Channel' : 'Create Text Channel' }}
                    </button>
                </div>
            </form>
            <div class="modal-action">
                <button @click="() => channelModal?.close()"
                        class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕
                </button>
            </div>
        </div>
    </dialog>
</template>
