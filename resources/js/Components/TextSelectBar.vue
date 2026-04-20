<script lang="ts" setup>
import {create, deleteMethod, edit} from '@/routes/channel';
import {channel as channelRoute} from '@/routes/home/text';
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import axios from "axios";
import {Channel, ChannelType, Perms, PermType, Role, Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {addIcons} from "oh-vue-icons";
import {MdDeleteforeverOutlined, MdModeeditoutlineOutlined, OiPlus} from "oh-vue-icons/icons";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {bigIntToPerms} from "@/bootstrap";
import echo from "@/echo";

addIcons(OiPlus, MdDeleteforeverOutlined, MdModeeditoutlineOutlined);

const loading = ref(false);
const {selectedServer} = defineProps<{
    channels?: Channel[],
    selectedServer?: Server,
    selectedChannel?: Channel,
}>()

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<() => void>();
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const form = useForm({
    type: ChannelType.Text,
    name: ''
});

const openModal = (channel?: Channel) => {
    if (channel) {
        isEditing.value = true;
        form.name = channel?.name || '';
        editCurrent.value = () => editChannel(channel.id);
    } else {
        isEditing.value = false;
        form.name = '';
    }
    channelModal.value?.showModal();
};

const createChannel = async () => {
    if (loading.value) return;
    loading.value = true;
    axios.postForm(create.url(selectedServer!.id), form.data())
        .then(() => {
            channelModal.value?.close();
            router.reload();
            form.reset();
        })
        .catch((err) => {
            console.error('Error creating server:', err);
        })
        .finally(() => {
            loading.value = false;
        });
};

const deleteChannel = async (channelId: number) => {
    console.log(channelId)
    axios.delete(deleteMethod.url(channelId)).then(() => router.reload());
};

const editChannel = async (channelId: number) => {
    console.log(channelId)
    if (loading.value) return;
    loading.value = true;

    axios.patch(edit.url(channelId), form.data())
        .then(() => {
            channelModal.value?.close();
            router.reload()
            form.reset();
        })
        .catch((err) => {
            console.error('Error creating server:', err);
        })
        .finally(() => {
            loading.value = false;
        });
};

if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleObj => roleObj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}
if (selectedServer) {
    echo.private(`channels.${selectedServer.id}`)
        .listen('.ChannelCreated', () => {
            router.reload({only: ['channels', 'selected_channel']});
        })
        .listen('.ChannelEdited', () => {
            router.reload({only: ['channels', 'selected_channel']});
        })
        .listen('.ChannelDeleted', () => {
            router.reload({only: ['channels', 'selected_channel']});
        })
}

</script>

<template>
    <div
        class="navbar bg-base-100 border-b border-base-300 justify-evenly overflow-x-auto overflow-y-hidden whitespace-nowrap">
        <div v-for="channel in channels" :key="channel.id" class="indicator relative group m-2">
            <div
                v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_DELETE_CHANNEL)"
                class="indicator-item indicator-top absolute hidden group-hover:block">
                <ConfirmDialog
                    :confirm="() => deleteChannel(channel.id)"
                    :description="`Are you sure you want to delete ${channel.name} channel?`"
                    class-name="indicator-item badge badge-error h-auto w-auto p-0.5"
                    title="Delete Channel"
                >
                    <v-icon name="md-deleteforever-outlined"/>
                </ConfirmDialog>
            </div>
            <div
                v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_EDIT_CHANNEL)"
                class="indicator-item indicator-top indicator-start absolute hidden group-hover:block">
                <button
                    class="indicator-item badge badge-warning h-auto w-auto p-0.5"
                    @click.prevent="openModal(channel)">
                    <v-icon name="md-modeeditoutline-outlined"/>
                </button>
            </div>

            <Link :href="channelRoute.url({server : selectedServer?.id!, channel : channel.id})">
                <button
                    :class="{'bg-base-300 text-base-content' : selectedChannel?.id === channel.id}"
                    class="btn btn-outline btn-sm">
                    {{ channel.name }}
                </button>
            </Link>
        </div>
        <button
            v-if="perms.has(PermType.CAN_MANAGE_CHANNEL | PermType.CAN_CREATE_CHANNEL)"
            class="btn btn-sm btn-square btn-outline mx-9"
            @click="openModal()">
            <v-icon name="oi-plus"/>
        </button>
    </div>

    <dialog ref="channelModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="isEditing ? editCurrent!() : createChannel()">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Text Channel Name</span>
                    </label>
                    <input
                        v-model="form.name" class="input input-bordered" placeholder="Enter channel name"
                        type="text"/>
                    <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                </div>
                <div class="modal-action">
                    <button :disabled="loading" class="btn btn-primary w-full mt-2" type="submit">
                        {{ isEditing ? 'Edit Text Channel' : 'Create Text Channel' }}
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
