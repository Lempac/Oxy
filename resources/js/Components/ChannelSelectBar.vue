<script lang="ts" setup>
import { usePerms } from '@/bootstrap';
import { text, voice, whiteboard } from '@/routes/home';
import { create, deleteMethod, edit } from '@/routes/channel';
import { channel as textChannelRoute } from '@/routes/home/text';
import { channel as voiceChannelRoute } from '@/routes/home/voice';
import { channel as whiteboardChannelRoute } from '@/routes/home/whiteboard';
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import {ref, computed} from "vue";
import {Channel, ChannelType, Perms, PermType, Server} from "@/types";
import {bigIntToPerms} from "@/bootstrap";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { BsChatText, BsEasel } from 'vue-icons-plus/bs';
import { RiChatVoiceLine, RiPencilFill } from 'vue-icons-plus/ri';
import { MdOutlineDeleteForever, MdOutlineModeEdit } from 'vue-icons-plus/md';
import { GoPlus } from 'vue-icons-plus/go';
import echo from "@/echo";

const perms = usePerms();
const {selectedServer, channels} = defineProps<{
    selectedServer?: Server,
    channels?: Channel[]
}>();

const activeExpanded = ref<'text' | 'voice' | 'whiteboard' | null>(null);
const pinnedExpanded = ref<'text' | 'voice' | 'whiteboard' | null>(null);

const displayMode = computed(() => pinnedExpanded.value || activeExpanded.value);

const textChannels = computed(() => channels?.filter(c => c.type === ChannelType.Text) || []);
const voiceChannels = computed(() => channels?.filter(c => c.type === ChannelType.Voice) || []);
const whiteboardChannels = computed(() => channels?.filter(c => c.type === ChannelType.Whiteboard) || []);

const isEditMode = ref(false);

const channelModal = ref<HTMLDialogElement>();
const isEditing = ref(false);
const editCurrent = ref<() => void>();

const form = useForm({
    type: ChannelType.Text as string,
    name: ''
});

const openModal = (type: string, channel?: Channel) => {
    form.type = type;
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

const loading = ref(false);

const createChannel = async () => {
    if (loading.value) return;
    loading.value = true;
    form.post(create.url(selectedServer!.route_key), {
        onSuccess: () => {
            channelModal.value?.close();
            router.reload();
            form.reset();
        },
        onError: (errors) => {
            console.error('Error creating channel:', errors);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const deleteChannel = async (channel: Channel) => {
    router.delete(deleteMethod.url({server: selectedServer!.route_key, channel: channel.route_key}));
};

const editChannel = async (channelKey: string) => {
    if (loading.value) return;
    loading.value = true;

    form.patch(edit.url({server: selectedServer!.route_key, channel: channelKey}), {
        onSuccess: () => {
            channelModal.value?.close();
            router.reload();
            form.reset();
        },
        onError: (errors) => {
            console.error('Error editing channel:', errors);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const togglePin = (type: 'text' | 'voice' | 'whiteboard') => {
    if (pinnedExpanded.value === type) {
        pinnedExpanded.value = null;
    } else {
        pinnedExpanded.value = type;
    }
};

if (selectedServer) {
    echo.private(`channels.${selectedServer.id}`)
        .listen('.ChannelCreated', () => {
            router.reload({only: ['channels']});
        })
        .listen('.ChannelEdited', () => {
            router.reload({only: ['channels']});
        })
        .listen('.ChannelDeleted', () => {
            router.reload({only: ['channels']});
        })
}

</script>

<template>
    <div v-if="selectedServer?.id" class="navbar bg-base-100 flex flex-col justify-center items-center py-2 relative min-h-[4rem]">
        
        <div class="flex items-center justify-between w-full h-14 px-4 gap-2">
            
            <!-- Left Side: Text Channels (Expands leftwards) -->
            <div class="flex-1 flex justify-end overflow-hidden h-full">
                <div 
                    class="flex flex-row-reverse items-center gap-2 overflow-x-auto overflow-y-visible transition-all duration-300 ease-in-out scrollbar-hide w-full justify-start h-full pt-3"
                    :class="displayMode === 'text' ? 'opacity-100 max-w-full px-2' : 'max-w-0 opacity-0 px-0'"
                >
                    <button
                        v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                        class="btn btn-sm btn-square btn-ghost shrink-0"
                        @click="openModal(ChannelType.Text)">
                        <GoPlus/>
                    </button>
                    <div v-for="channel in textChannels" :key="channel.id" class="indicator relative group whitespace-nowrap shrink-0">
                        <div v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])" class="indicator-item indicator-top">
                            <ConfirmDialog
                                :confirm="() => deleteChannel(channel)"
                                :description="`Are you sure you want to delete ${channel.name}?`"
                                class-name="badge badge-error h-auto w-auto p-0.5 cursor-pointer"
                                title="Delete Channel"
                            >
                                <MdOutlineDeleteForever/>
                            </ConfirmDialog>
                        </div>
                        <div v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])" class="indicator-item indicator-top indicator-start">
                            <button class="badge badge-warning h-auto w-auto p-0.5 cursor-pointer" @click.prevent="openModal(ChannelType.Text, channel)">
                                <MdOutlineModeEdit/>
                            </button>
                        </div>
                        <Link :href="textChannelRoute.url({server: selectedServer.route_key, channel: channel.route_key})">
                            <button class="btn btn-outline btn-sm" :class="{'btn-active': $page.url.includes(`/text/${channel.id}`)}">
                                # {{ channel.name }}
                            </button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Central Split Button -->
            <div class="join bg-base-200 z-10 shadow-sm border border-base-300 shrink-0">
                <button 
                    class="btn join-item hover:btn-primary" 
                    :class="{'btn-primary': displayMode === 'text'}"
                    @mouseenter="activeExpanded = 'text'" 
                    @mouseleave="activeExpanded = null"
                    @click="togglePin('text')"
                >
                    <BsChatText class="mr-1"/> TEXT
                </button>
                <button 
                    class="btn join-item hover:btn-secondary" 
                    :class="{'btn-secondary': displayMode === 'voice'}"
                    @mouseenter="activeExpanded = 'voice'" 
                    @mouseleave="activeExpanded = null"
                    @click="togglePin('voice')"
                >
                    <RiChatVoiceLine class="mr-1"/> VOICE
                </button>
                <button 
                    class="btn join-item hover:btn-accent" 
                    :class="{'btn-accent': displayMode === 'whiteboard'}"
                    @mouseenter="activeExpanded = 'whiteboard'" 
                    @mouseleave="activeExpanded = null"
                    @click="togglePin('whiteboard')"
                >
                    <BsEasel class="mr-1"/> WHITEBOARD
                </button>
            </div>

            <!-- Right Side: Voice/Whiteboard Channels (Expands rightwards) -->
            <div class="flex-1 flex justify-start overflow-hidden h-full">
                <div 
                    class="flex items-center gap-2 overflow-x-auto overflow-y-visible transition-all duration-300 ease-in-out scrollbar-hide w-full justify-start h-full pt-3"
                    :class="(displayMode === 'voice' || displayMode === 'whiteboard') ? 'opacity-100 max-w-full px-2' : 'max-w-0 opacity-0 px-0'"
                >
                    <!-- Voice Channels -->
                    <template v-if="displayMode === 'voice'">
                        <div v-for="channel in voiceChannels" :key="channel.id" class="indicator relative group whitespace-nowrap shrink-0">
                            <div v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])" class="indicator-item indicator-top">
                                <ConfirmDialog
                                    :confirm="() => deleteChannel(channel)"
                                    :description="`Are you sure you want to delete ${channel.name}?`"
                                    class-name="badge badge-error h-auto w-auto p-0.5 cursor-pointer"
                                    title="Delete Channel"
                                >
                                    <MdOutlineDeleteForever/>
                                </ConfirmDialog>
                            </div>
                            <div v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])" class="indicator-item indicator-top indicator-start">
                                <button class="badge badge-warning h-auto w-auto p-0.5 cursor-pointer" @click.prevent="openModal(ChannelType.Voice, channel)">
                                    <MdOutlineModeEdit/>
                                </button>
                            </div>
                            <Link :href="voiceChannelRoute.url({server: selectedServer.route_key, channel: channel.route_key})">
                                <button class="btn btn-outline btn-sm" :class="{'btn-active': $page.url.includes(`/voice/${channel.id}`)}">
                                    🔊 {{ channel.name }}
                                </button>
                            </Link>
                        </div>
                        <button
                            v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                            class="btn btn-sm btn-square btn-ghost shrink-0"
                            @click="openModal(ChannelType.Voice)">
                            <GoPlus/>
                        </button>
                    </template>

                    <!-- Whiteboard Channels -->
                    <template v-if="displayMode === 'whiteboard'">
                        <div v-for="channel in whiteboardChannels" :key="channel.id" class="indicator relative group whitespace-nowrap shrink-0">
                            <div v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])" class="indicator-item indicator-top">
                                <ConfirmDialog
                                    :confirm="() => deleteChannel(channel)"
                                    :description="`Are you sure you want to delete ${channel.name}?`"
                                    class-name="badge badge-error h-auto w-auto p-0.5 cursor-pointer"
                                    title="Delete Channel"
                                >
                                    <MdOutlineDeleteForever/>
                                </ConfirmDialog>
                            </div>
                            <div v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])" class="indicator-item indicator-top indicator-start">
                                <button class="badge badge-warning h-auto w-auto p-0.5 cursor-pointer" @click.prevent="openModal(ChannelType.Whiteboard, channel)">
                                    <MdOutlineModeEdit/>
                                </button>
                            </div>
                            <Link :href="whiteboardChannelRoute.url({server: selectedServer.route_key, channel: channel.route_key})">
                                <button class="btn btn-outline btn-sm" :class="{'btn-active': $page.url.includes(`/whiteboard/${channel.id}`)}">
                                    🎨 {{ channel.name }}
                                </button>
                            </Link>
                        </div>
                        <button
                            v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                            class="btn btn-sm btn-square btn-ghost shrink-0"
                            @click="openModal(ChannelType.Whiteboard)">
                            <GoPlus/>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Pencil Mode Toggle -->
            <div class="shrink-0 ml-2" v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL, PermType.CAN_DELETE_CHANNEL])">
                <button 
                    class="btn btn-circle btn-sm tooltip tooltip-left" 
                    :class="isEditMode ? 'btn-warning' : 'btn-ghost'"
                    :data-tip="isEditMode ? 'Edit Mode ON' : 'Edit Mode OFF'"
                    @click="isEditMode = !isEditMode"
                >
                    <RiPencilFill />
                </button>
            </div>

        </div>
    </div>

    <!-- Create/Edit Channel Modal -->
    <dialog ref="channelModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="isEditing ? editCurrent!() : createChannel()">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">
                            {{ form.type === ChannelType.Text ? 'Text' : (form.type === ChannelType.Voice ? 'Voice' : 'Whiteboard') }} Channel Name
                        </span>
                    </label>
                    <input
                        v-model="form.name" class="input input-bordered" placeholder="Enter channel name"
                        type="text"/>
                    <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                </div>
                <div class="modal-action">
                    <button :disabled="loading" class="btn btn-primary w-full mt-2" type="submit">
                        {{ isEditing ? 'Edit' : 'Create' }} Channel
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
