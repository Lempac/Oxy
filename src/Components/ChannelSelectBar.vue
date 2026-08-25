<script lang="ts" setup>
import {usePerms} from '@/bootstrap';
import {channel as textChannelRoute} from '@/routes/home/text';
import {channel as whiteboardChannelRoute} from '@/routes/home/whiteboard';
import {useRoute} from 'vue-router';
import pb from '@/pocketbase';
import {computed, reactive, ref} from "vue";
import {Channel, ChannelType, PermType, Server} from "@/types";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {BsChatText, BsEasel} from 'vue-icons-plus/bs';
import {RiChatVoiceLine, RiPencilFill} from 'vue-icons-plus/ri';
import {MdDragIndicator, MdOutlineDeleteForever, MdOutlineModeEdit} from 'vue-icons-plus/md';
import {GoPlus} from 'vue-icons-plus/go';
import {useChannelEvents} from "@/composables/useChannelEvents";
import {useVoiceCallStateMachine} from "@/composables/useVoiceCallStateMachine";

const perms = usePerms();
const voiceState = useVoiceCallStateMachine();
const route = useRoute();
const {selectedServer, channels} = defineProps<{
    selectedServer?: Server,
    channels?: Channel[]
}>();

const draggedChannelId = ref<string | null>(null);
const hoverChannelId = ref<string | null>(null);

const onChannelDragStart = (e: DragEvent, channel: Channel) => {
    draggedChannelId.value = channel.id;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', channel.id);
    }
};

const onChannelDragEnter = (channel: Channel) => {
    if (draggedChannelId.value && draggedChannelId.value !== channel.id) {
        hoverChannelId.value = channel.id;
    }
};

const onChannelDragLeave = (channel: Channel) => {
    if (hoverChannelId.value === channel.id) {
        hoverChannelId.value = null;
    }
};

const onChannelDragEnd = () => {
    draggedChannelId.value = null;
    hoverChannelId.value = null;
};

const onChannelDrop = (e: DragEvent, targetChannel: Channel, typeList: Channel[]) => {
    e.preventDefault();
    if (!draggedChannelId.value || draggedChannelId.value === targetChannel.id) {
        onChannelDragEnd();
        return;
    }

    const fromIndex = typeList.findIndex(c => c.id === draggedChannelId.value);
    const toIndex = typeList.findIndex(c => c.id === targetChannel.id);
    if (fromIndex === -1 || toIndex === -1) {
        onChannelDragEnd();
        return;
    }

    const reordered = [...typeList];
    const [moved] = reordered.splice(fromIndex, 1);
    reordered.splice(toIndex, 0, moved);

    const reorderedIds = reordered.map(c => c.id);
    const otherIds = (channels || []).filter(c => c.type !== targetChannel.type).map(c => c.id);
    const allChannelIds = [...reorderedIds, ...otherIds];

    onChannelDragEnd();

    if (selectedServer) {
        try {
            allChannelIds.forEach(async (id, idx) => {
                await pb.collection('channels').update(id, { position: idx });
            });
        } catch (err: unknown) {
            console.error('Error reordering channels:', err);
        }
    }
};

const activeExpanded = ref<'text' | 'voice' | 'whiteboard' | null>(null);
const pinnedExpanded = ref<'text' | 'voice' | 'whiteboard' | null>(null);

const displayMode = computed(() => pinnedExpanded.value || activeExpanded.value);
const isWhiteboardEnabled = computed(() => selectedServer?.enable_whiteboard !== false);

const textChannels = computed(() => channels?.filter((c: { type: string; }) => c.type === ChannelType.Text) || []);
const voiceChannels = computed(() => channels?.filter((c: { type: string; }) => c.type === ChannelType.Voice) || []);
const whiteboardChannels = computed(() => channels?.filter((c: {
    type: string;
}) => c.type === ChannelType.Whiteboard) || []);

const isEditMode = ref(false);

const isChannelModalOpen = ref(false);
const isEditing = ref(false);
const editCurrent = ref<() => void>();

const form = reactive({
    type: ChannelType.Text as string,
    name: '',
    errors: {} as Record<string, string>,
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
    isChannelModalOpen.value = true;
};

const loading = ref(false);

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
        isChannelModalOpen.value = false;
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
        isChannelModalOpen.value = false;
        form.name = '';
    } catch (err: unknown) {
        console.error('Error editing channel:', err);
    } finally {
        loading.value = false;
    }
};

const togglePin = (type: 'text' | 'voice' | 'whiteboard') => {
    if (pinnedExpanded.value === type) {
        pinnedExpanded.value = null;
    } else {
        pinnedExpanded.value = type;
    }
};

useChannelEvents(selectedServer?.id, ['channels']);

</script>

<template>
    <div
        v-if="selectedServer?.id"
        class="navbar bg-base-100 flex flex-col justify-center items-center py-2 relative min-h-16"
    >
        <div class="flex items-center justify-between w-full h-14 px-4 gap-2">

            <!-- Left Side: Text Channels (Expands leftwards) -->
            <div class="flex-1 flex justify-end overflow-hidden h-full">
                <div
                    :class="[
                        displayMode === 'text' ? 'opacity-100 max-w-full px-2' : 'max-w-0 opacity-0 px-0',
                        isEditMode ? 'gap-6' : 'gap-2'
                    ]"
                    class="flex flex-row-reverse items-center overflow-x-auto overflow-y-visible transition-all duration-300 ease-in-out scrollbar-hide w-full justify-start h-full pt-3"
                >
                    <button
                        v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                        class="btn btn-sm btn-square btn-ghost shrink-0"
                        @click="openModal(ChannelType.Text)">
                        <GoPlus/>
                    </button>
                    <div
                        v-for="channel in textChannels" :key="channel.id"
                        :class="[
                            hoverChannelId === channel.id && draggedChannelId && draggedChannelId !== channel.id ? 'ring-2 ring-primary ring-offset-1 rounded-lg bg-primary/15' : ''
                        ]"
                        :draggable="isEditMode"
                        class="indicator relative group whitespace-nowrap shrink-0 transition-all duration-150"
                        @dragend="onChannelDragEnd"
                        @dragleave="onChannelDragLeave(channel)"
                        @dragstart="onChannelDragStart($event, channel)"
                        @drop="onChannelDrop($event, channel, textChannels)"
                        @dragenter.prevent="onChannelDragEnter(channel)"
                        @dragover.prevent
                    >
                        <div
                            v-if="isEditMode"
                            class="indicator-item indicator-bottom indicator-center cursor-grab active:cursor-grabbing text-primary bg-base-300 rounded p-0.5"
                            title="Drag to reorder"
                        >
                            <MdDragIndicator class="size-3"/>
                        </div>
                        <div
                            v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                            class="indicator-item indicator-top">
                            <ConfirmDialog
                                :confirm="() => deleteChannel(channel)"
                                :description="`Are you sure you want to delete ${channel.name}?`"
                                class-name="badge badge-error h-auto w-auto p-0.5 cursor-pointer"
                                title="Delete Channel"
                            >
                                <MdOutlineDeleteForever/>
                            </ConfirmDialog>
                        </div>
                        <div
                            v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                            class="indicator-item indicator-top indicator-start">
                            <button
                                class="badge badge-warning h-auto w-auto p-0.5 cursor-pointer"
                                @click.prevent="openModal(ChannelType.Text, channel)">
                                <MdOutlineModeEdit/>
                            </button>
                        </div>
                        <router-link
                            :to="textChannelRoute.url({server: selectedServer.route_key, channel: channel.route_key})">
                            <button
                                :class="{'btn-primary': route.path.includes(`/text/${channel.route_key}`)}"
                                class="btn btn-outline btn-sm">
                                # {{ channel.name }}
                            </button>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Central Split Button -->
            <div class="join bg-base-200 z-10 shadow-sm border border-base-300 shrink-0">
                <button
                    :class="{'btn-primary': displayMode === 'text'}"
                    class="btn join-item hover:btn-primary"
                    @click="togglePin('text')"
                    @mouseenter="activeExpanded = 'text'"
                    @mouseleave="activeExpanded = null"
                >
                    <BsChatText class="mr-1"/>
                    TEXT
                </button>
                <button
                    :class="{'btn-secondary': displayMode === 'voice'}"
                    class="btn join-item hover:btn-secondary"
                    @click="togglePin('voice')"
                    @mouseenter="activeExpanded = 'voice'"
                    @mouseleave="activeExpanded = null"
                >
                    <RiChatVoiceLine class="mr-1"/>
                    VOICE
                </button>
                <button
                    v-if="isWhiteboardEnabled"
                    :class="{'btn-accent': displayMode === 'whiteboard'}"
                    class="btn join-item hover:btn-accent"
                    @click="togglePin('whiteboard')"
                    @mouseenter="activeExpanded = 'whiteboard'"
                    @mouseleave="activeExpanded = null"
                >
                    <BsEasel class="mr-1"/>
                    WHITEBOARD
                </button>
            </div>

            <!-- Right Side: Voice/Whiteboard Channels (Expands rightwards) -->
            <div class="flex-1 flex justify-start overflow-hidden h-full">
                <div
                    :class="[
                        (displayMode === 'voice' || (displayMode === 'whiteboard' && isWhiteboardEnabled)) ? 'opacity-100 max-w-full px-2' : 'max-w-0 opacity-0 px-0',
                        isEditMode ? 'gap-6' : 'gap-2'
                    ]"
                    class="flex items-center overflow-x-auto overflow-y-visible transition-all duration-300 ease-in-out scrollbar-hide w-full justify-start h-full pt-3"
                >
                    <!-- Voice Channels -->
                    <template v-if="displayMode === 'voice'">
                        <div
                            v-for="channel in voiceChannels" :key="channel.id"
                            :class="[
                                hoverChannelId === channel.id && draggedChannelId && draggedChannelId !== channel.id ? 'ring-2 ring-primary ring-offset-1 rounded-lg bg-primary/15' : ''
                            ]"
                            :draggable="isEditMode"
                            class="indicator relative group whitespace-nowrap shrink-0 transition-all duration-150"
                            @dragend="onChannelDragEnd"
                            @dragleave="onChannelDragLeave(channel)"
                            @dragstart="onChannelDragStart($event, channel)"
                            @drop="onChannelDrop($event, channel, voiceChannels)"
                            @dragenter.prevent="onChannelDragEnter(channel)"
                            @dragover.prevent
                        >
                            <div
                                v-if="isEditMode"
                                class="indicator-item indicator-bottom indicator-center cursor-grab active:cursor-grabbing text-secondary bg-base-300 rounded p-0.5"
                                title="Drag to reorder"
                            >
                                <MdDragIndicator class="size-3"/>
                            </div>
                            <div
                                v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                                class="indicator-item indicator-top">
                                <ConfirmDialog
                                    :confirm="() => deleteChannel(channel)"
                                    :description="`Are you sure you want to delete ${channel.name}?`"
                                    class-name="badge badge-error h-auto w-auto p-0.5 cursor-pointer"
                                    title="Delete Channel"
                                >
                                    <MdOutlineDeleteForever/>
                                </ConfirmDialog>
                            </div>
                            <div
                                v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                                class="indicator-item indicator-top indicator-start">
                                <button
                                    class="badge badge-warning h-auto w-auto p-0.5 cursor-pointer"
                                    @click.prevent="openModal(ChannelType.Voice, channel)">
                                    <MdOutlineModeEdit/>
                                </button>
                            </div>
                            <button
                                :class="voiceState.isConnected.value && voiceState.activeChannel.value?.id === channel.id ? 'btn-success' : 'btn-outline btn-secondary'"
                                class="btn btn-sm"
                                @click="voiceState.joinChannel(channel, selectedServer?.id)">
                                🔊 {{ channel.name }}
                            </button>
                        </div>
                        <button
                            v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                            class="btn btn-sm btn-square btn-ghost shrink-0"
                            @click="openModal(ChannelType.Voice)">
                            <GoPlus/>
                        </button>
                    </template>

                    <!-- Whiteboard Channels -->
                    <template v-if="displayMode === 'whiteboard' && isWhiteboardEnabled">
                        <div
                            v-for="channel in whiteboardChannels" :key="channel.id"
                            :class="[
                                hoverChannelId === channel.id && draggedChannelId && draggedChannelId !== channel.id ? 'ring-2 ring-primary ring-offset-1 rounded-lg bg-primary/15' : ''
                            ]"
                            :draggable="isEditMode"
                            class="indicator relative group whitespace-nowrap shrink-0 transition-all duration-150"
                            @dragend="onChannelDragEnd"
                            @dragleave="onChannelDragLeave(channel)"
                            @dragstart="onChannelDragStart($event, channel)"
                            @drop="onChannelDrop($event, channel, whiteboardChannels)"
                            @dragenter.prevent="onChannelDragEnter(channel)"
                            @dragover.prevent
                        >
                            <div
                                v-if="isEditMode"
                                class="indicator-item indicator-bottom indicator-center cursor-grab active:cursor-grabbing text-accent bg-base-300 rounded p-0.5"
                                title="Drag to reorder"
                            >
                                <MdDragIndicator class="size-3"/>
                            </div>
                            <div
                                v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                                class="indicator-item indicator-top">
                                <ConfirmDialog
                                    :confirm="() => deleteChannel(channel)"
                                    :description="`Are you sure you want to delete ${channel.name}?`"
                                    class-name="badge badge-error h-auto w-auto p-0.5 cursor-pointer"
                                    title="Delete Channel"
                                >
                                    <MdOutlineDeleteForever/>
                                </ConfirmDialog>
                            </div>
                            <div
                                v-if="isEditMode && perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                                class="indicator-item indicator-top indicator-start">
                                <button
                                    class="badge badge-warning h-auto w-auto p-0.5 cursor-pointer"
                                    @click.prevent="openModal(ChannelType.Whiteboard, channel)">
                                    <MdOutlineModeEdit/>
                                </button>
                            </div>
                            <router-link
                                :to="whiteboardChannelRoute.url({server: selectedServer.route_key, channel: channel.route_key})">
                                <button
                                    :class="{'btn-accent': route.path.includes(`/whiteboard/${channel.route_key}`)}"
                                    class="btn btn-outline btn-sm">
                                    🎨 {{ channel.name }}
                                </button>
                            </router-link>
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
            <div
                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                class="shrink-0 ml-2"
            >
                <button
                    :class="isEditMode ? 'btn-warning' : 'btn-ghost'"
                    :data-tip="isEditMode ? 'Edit Mode ON' : 'Edit Mode OFF'"
                    class="btn btn-circle btn-sm tooltip tooltip-left"
                    @click="isEditMode = !isEditMode"
                >
                    <RiPencilFill/>
                </button>
            </div>

        </div>
    </div>

    <!-- Create/Edit Channel Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isChannelModalOpen" class="modal modal-open">
                <div class="modal-box bg-base-200 relative z-10">
                    <form @submit.prevent="isEditing ? editCurrent!() : createChannel()">
                        <fieldset class="fieldset w-full mb-4">
                            <legend class="fieldset-legend">
                                {{
                                    form.type === ChannelType.Text ? 'Text' : (form.type === ChannelType.Voice ? 'Voice' : 'Whiteboard')
                                }} Channel Name
                            </legend>
                            <input
                                v-model="form.name" autocomplete="off" class="input input-bordered w-full"
                                data-bwignore="true" placeholder="Enter channel name"
                                type="text"/>
                            <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                        </fieldset>
                        <div class="modal-action">
                            <button :disabled="loading" class="btn btn-primary w-full mt-2" type="submit">
                                {{ isEditing ? 'Edit' : 'Create' }} Channel
                            </button>
                        </div>
                    </form>
                    <div class="modal-action">
                        <button
                            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            @click="isChannelModalOpen = false">✕
                        </button>
                    </div>
                </div>
                <div class="modal-backdrop bg-neutral/30 fixed inset-0" @click="isChannelModalOpen = false"></div>
            </div>
        </Transition>
    </Teleport>
</template>
