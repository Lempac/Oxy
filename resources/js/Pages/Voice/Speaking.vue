<script lang="ts" setup>
import { create, deleteMethod, edit } from '@/routes/channel';
import { channel } from '@/routes/home/voice';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {baseUrl, bigIntToPerms, defaultIcon} from "@/bootstrap";
import axios from "axios";
import {ref} from "vue";
import {Channel, ChannelType, Perms, PermType, Role, Server} from "@/types";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {addIcons} from "oh-vue-icons";
import {MdDeleteforeverOutlined, MdModeeditoutlineOutlined, OiPlus} from "oh-vue-icons/icons";

addIcons(OiPlus, MdDeleteforeverOutlined, MdModeeditoutlineOutlined);

const {selectedServer} = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    inviteCode?: string,
}>()

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<Function>();
const perms = ref<Perms>(bigIntToPerms([]));

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
    axios.postForm(create.url(selectedServer!.id), form.data()).then(() => {
        channelModal.value?.close();
        router.reload()
    });
};

const deleteText = async (channelId: number) => {
    axios.delete(deleteMethod.url(channelId)).then(() => {
        router.reload()
    });
};

const editText = async (channelId: number) => {
    axios.patch(edit.url(channelId), form.data()).then(() => {
        channelModal.value?.close();
        router.reload()
    });
};

if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: string[], curr: Role) => [...new Set([...acc, ...curr.perms])], []));
}

const isInVoice = ref(false);
let mediaRecorder: MediaRecorder | undefined;
const chunks: Blob[] = [];

const joinChannel = async () => {
    isInVoice.value = true;
    if (isInVoice.value) {
        try {
            const stream: MediaStream = await navigator.mediaDevices.getUserMedia({audio: true});
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
    <AuthenticatedLayout :invite-code :selected-server :servers>
        <div class="mt-3 pb-20">
            <div
                v-for="channel in channels"
                :key="channel.id"
                class="indicator relative group w-2/3 h-auto mx-auto flex items-center justify-center m-7">
                <span
                    v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_DELETE_CHANNEL)"
                    class="indicator-item indicator-top absolute hidden group-hover:block">
                    <ConfirmDialog
                        :confirm="() => deleteText(channel.id)"
                        :description="`Are you sure you want to delete ${channel.name} channel?`"
                        class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                        title="Delete Channel"
                    >
                        <v-icon name="md-deleteforever-outlined"/>
                    </ConfirmDialog>
                </span>

                <span
                    v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_EDIT_CHANNEL)"
                    class="indicator-item indicator-top indicator-start absolute hidden group-hover:block">
                    <button
                        class="indicator-item badge badge-warning h-auto w-auto p-0.5"
                        @click.prevent="openModal(channel)"
                    >
                        <v-icon name="md-modeeditoutline-outlined"/>
                    </button>
                </span>

                <div class="w-full rounded-lg bg-base-100">
                    <div class="flex items-center justify-center text-xl pt-2">
                        {{ channel.name }}
                    </div>

                    <div class="grid grid-cols-3 gap-1 p-3">
                        <div
                            v-for="user in selectedServer?.users" :key="user.id"
                            class="avatar rounded-lg items-center justify-center h-16 bg-base-300"
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
                        <!--                        <Link :href="channel.url({server : selected_server?.id, channel : channel.id})">-->
                        <button v-if="!isInVoice" class="btn btn-success btn-wide mr-3 mb-3" @click="joinChannel">
                            Join
                        </button>
                        <button v-else class="btn btn-error btn-wide mr-3 mb-3" @click="leaveChannel">
                            Leave
                        </button>
                        <!--                        </Link>-->
                    </div>
                </div>
            </div>

            <button
                v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_CREATE_CHANNEL)"
                class="btn w-2/3 h-auto p-3 rounded-lg mx-auto flex items-center justify-center bg-base-100"
                @click="openModal()">
                <v-icon name="oi-plus" scale="3"/>
            </button>
        </div>
    </AuthenticatedLayout>

    <dialog ref="channelModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="isEditing ? editCurrent!() : createText()">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Voice Channel Name</span>
                    </label>
                    <input
                        v-model="form.name" class="input input-bordered" placeholder="Enter channel name"
                        type="text"/>
                </div>
                <div class="modal-action">
                    <button class="btn btn-primary w-full mt-2" type="submit">
                        {{ isEditing ? 'Edit Voice Channel' : 'Create Voice Channel' }}
                    </button>
                </div>
            </form>
            <div class="modal-action">
                <button
                    class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    @click="() => channelModal?.close()">✕
                </button>
            </div>
        </div>
    </dialog>
</template>
