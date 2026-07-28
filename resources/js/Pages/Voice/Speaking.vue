<script lang="ts" setup>
import {baseUrl, defaultIcon, usePerms} from '@/bootstrap';

import {create, deleteMethod, edit} from '@/routes/channel';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {router, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {Channel, ChannelType, PermType, Server, VoiceParticipantState} from "@/types";
import {useVoiceCallStateMachine} from "@/composables/useVoiceCallStateMachine";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { MdOutlineDeleteForever, MdOutlineModeEdit } from 'vue-icons-plus/md';
import { GoPlus } from 'vue-icons-plus/go';


const perms = usePerms();
const {selectedServer} = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    inviteCode?: string,
}>()

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<() => void>();

const form = useForm({
    type: ChannelType.Voice,
    name: ''
});

const voiceState = useVoiceCallStateMachine();

const openModal = (channel?: Channel) => {
    if (channel) {
        isEditing.value = true;
        form.name = channel?.name || '';
        editCurrent.value = () => editText(channel.id, channel.route_key);
    } else {
        isEditing.value = false;
        form.name = '';
    }
    channelModal.value?.showModal();
};

const createText = async () => {
    form.post(create.url(selectedServer!.route_key), {
        onSuccess: () => {
            channelModal.value?.close();
            router.reload();
        }
    });
};

const deleteText = async (channel: Channel) => {
    router.delete(deleteMethod.url({server: selectedServer!.route_key, channel: channel.route_key}));
};

const editText = async (channelId: number, channelKey: string) => {
    form.patch(edit.url({server: selectedServer!.route_key, channel: channelKey}), {
        onSuccess: () => {
            channelModal.value?.close();
            router.reload();
        }
    });
};


let mediaRecorder: MediaRecorder | undefined;
const chunks: Blob[] = [];

const joinChannel = async () => {
    if (!voiceState.transitionTo(VoiceParticipantState.Joining)) {
        return;
    }

    try {
        const stream: MediaStream = await navigator.mediaDevices.getUserMedia({audio: true});
        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.start(100);
        console.log("Recording started");

        mediaRecorder.ondataavailable = (event: BlobEvent) => {
            chunks.push(event.data);
            console.log(chunks);
        };

        voiceState.transitionTo(VoiceParticipantState.Connected);
    } catch (error) {
        console.error("Error accessing microphone:", error);
        voiceState.transitionTo(VoiceParticipantState.Disconnected);
    }
};

const leaveChannel = async () => {
    if (!voiceState.transitionTo(VoiceParticipantState.Leaving)) {
        return;
    }

    mediaRecorder?.stop();
    console.log("Recording stopped");
    console.log(chunks);
    voiceState.transitionTo(VoiceParticipantState.Disconnected);
};

</script>

<template>
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        <div class="mt-3 pb-20">
            <div
                v-for="channel in channels"
                :key="channel.id"
                class="indicator relative group w-2/3 h-auto mx-auto flex items-center justify-center m-7">
                <span
                    v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                    class="indicator-item indicator-top absolute hidden group-hover:block">
                    <ConfirmDialog
                        :confirm="() => deleteText(channel)"
                        :description="`Are you sure you want to delete ${channel.name} channel?`"
                        class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                        title="Delete Channel"
                    >
                        <MdOutlineDeleteForever/>
                    </ConfirmDialog>
                </span>

                <span
                    v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                    class="indicator-item indicator-top indicator-start absolute hidden group-hover:block">
                    <button
                        class="indicator-item badge badge-warning h-auto w-auto p-0.5"
                        @click.prevent="openModal(channel)"
                    >
                        <MdOutlineModeEdit/>
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
                                <img :src="user.icon ? `${baseUrl}${user.icon}` : defaultIcon" alt=""/>
                            </div>
                            <div class="flex items-center h-full w-full p-4">
                                {{ user.nickname }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button v-if="voiceState.isDisconnected.value" class="btn btn-success btn-wide mr-3 mb-3" @click="joinChannel">
                            Join
                        </button>
                        <button v-else-if="voiceState.isJoining.value" class="btn btn-warning btn-wide mr-3 mb-3" disabled>
                            Connecting...
                        </button>
                        <button v-else class="btn btn-error btn-wide mr-3 mb-3" @click="leaveChannel">
                            Leave
                        </button>
                    </div>
                </div>
            </div>

            <button
                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                class="btn w-2/3 h-auto p-3 rounded-lg mx-auto flex items-center justify-center bg-base-100"
                @click="openModal()">
                <GoPlus scale="3"/>
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
                        v-model="form.name" autocomplete="off" class="input input-bordered" data-bwignore="true" placeholder="Enter channel name"
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
