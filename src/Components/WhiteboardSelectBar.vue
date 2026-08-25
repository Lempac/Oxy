<script lang="ts" setup>
import {usePerms} from '@/bootstrap';
import {channel as channelRoute} from '@/routes/home/whiteboard';
import pb from '@/pocketbase';
import {reactive, ref} from "vue";
import {Channel, ChannelType, PermType, Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {MdOutlineDeleteForever, MdOutlineModeEdit} from 'vue-icons-plus/md';
import {GoPlus} from 'vue-icons-plus/go';
import {useChannelEvents} from "@/composables/useChannelEvents";

const loading = ref(false);
const perms = usePerms();
const {selectedServer, channels, selectedChannel} = defineProps<{
    channels?: Channel[],
    selectedServer?: Server,
    selectedChannel?: Channel,
}>()

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<() => void>();

const form = reactive({
    type: ChannelType.Whiteboard,
    name: '',
    errors: {} as Record<string, string>,
});

const openModal = (channel?: Channel) => {
    if (channel) {
        isEditing.value = true;
        form.name = channel?.name || '';
        editCurrent.value = () => editChannel(channel.route_key);
    } else {
        isEditing.value = false;
        form.name = '';
    }
    channelModal.value?.showModal();
};

const createChannel = async () => {
    if (loading.value || !selectedServer?.id) return;
    loading.value = true;
    try {
        await pb.collection('channels').create({
            server: selectedServer.id,
            name: form.name,
            slug: form.name.toLowerCase().replace(/\s+/g, '-'),
            type: form.type,
            position: channels?.length || 0,
        });
        channelModal.value?.close();
        form.name = '';
    } catch (err: unknown) {
        console.error('Error creating channel:', err);
    } finally {
        loading.value = false;
    }
};

const deleteChannel = async (channel: Channel) => {
    try {
        await pb.collection('channels').delete(channel.id);
    } catch (err: unknown) {
        console.error('Error deleting channel:', err);
    }
};

const editChannel = async (channelKey: string) => {
    if (loading.value || !selectedServer?.id) return;
    loading.value = true;

    try {
        const channelRecord = channels?.find(c => c.route_key === channelKey || c.id === channelKey);
        if (channelRecord) {
            await pb.collection('channels').update(channelRecord.id, {
                name: form.name,
                slug: form.name.toLowerCase().replace(/\s+/g, '-'),
            });
        }
        channelModal.value?.close();
        form.name = '';
    } catch (err: unknown) {
        console.error('Error editing channel:', err);
    } finally {
        loading.value = false;
    }
};

useChannelEvents(selectedServer?.id, ['channels', 'selected_channel']);

</script>

<template>
    <div
        class="navbar bg-base-100 border-b border-base-300 justify-evenly overflow-x-auto overflow-y-hidden whitespace-nowrap">
        <div v-for="channel in channels" :key="channel.id" class="indicator relative group m-2">
            <div
                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                class="indicator-item indicator-top absolute hidden group-hover:block">
                <ConfirmDialog
                    :confirm="() => deleteChannel(channel)"
                    :description="`Are you sure you want to delete ${channel.name} whiteboard channel?`"
                    class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                    title="Delete Channel"
                >
                    <MdOutlineDeleteForever/>
                </ConfirmDialog>
            </div>
            <div
                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                class="indicator-item indicator-top indicator-start absolute hidden group-hover:block">
                <button
                    class="indicator-item badge badge-warning h-auto w-auto p-0.5"
                    @click.prevent="openModal(channel)">
                    <MdOutlineModeEdit/>
                </button>
            </div>

            <router-link :to="channelRoute.url({server : selectedServer?.route_key!, channel : channel.route_key})">
                <button
                    :class="{'bg-base-300 text-base-content' : selectedChannel?.id === channel.id}"
                    class="btn btn-outline btn-sm">
                    {{ channel.name }}
                </button>
            </router-link>
        </div>
        <button
            v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
            class="btn btn-sm btn-square btn-outline mx-9"
            @click="openModal()">
            <GoPlus/>
        </button>
    </div>

    <dialog ref="channelModal" class="modal">
        <div class="modal-box text-base-content">
            <form @submit.prevent="isEditing ? editCurrent!() : createChannel()">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Whiteboard Channel Name</span>
                    </label>
                    <input
                        v-model="form.name" autocomplete="off" class="input input-bordered w-full" data-bwignore="true" placeholder="Enter channel name"
                        type="text"/>
                    <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                </div>
                <div class="modal-action">
                    <button :disabled="loading" class="btn btn-primary w-full mt-2" type="submit">
                        {{ isEditing ? 'Edit Whiteboard Channel' : 'Create Whiteboard Channel' }}
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
