<script lang="ts" setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Channel, ChannelType, PermType, Server, User } from '@/types';
import { baseUrl, defaultIcon, getMemberRoleColor, resolveUrl, usePerms } from '@/bootstrap';
import { create, deleteMethod, edit } from '@/routes/channel';
import { channel as textChannelRoute } from '@/routes/home/text';
import { channel as whiteboardChannelRoute } from '@/routes/home/whiteboard';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ErrorAlert from '@/Components/ErrorAlert.vue';
import UserProfileModal from '@/Components/UserProfileModal.vue';
import { GoPlus } from 'vue-icons-plus/go';
import { MdOutlineDeleteForever, MdOutlineModeEdit, MdDragIndicator, MdExitToApp, MdMicOff, MdHeadsetOff } from 'vue-icons-plus/md';
import { TbKeyboardOff } from 'vue-icons-plus/tb';
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';
import { usePaneDrag } from '@/composables/usePaneDrag';
import { useChannelEvents } from '@/composables/useChannelEvents';
import echo from '@/echo';

const perms = usePerms();
const voiceState = useVoiceCallStateMachine();
const page = usePage();
const { startPaneSwapDrag, endPaneSwapDrag } = usePaneDrag();

const props = defineProps<{
    selectedServer?: Server;
    channels?: Channel[];
    selectedChannel?: Channel;
}>();

const isEditMode = ref(false);
const isChannelModalOpen = ref(false);
const isEditing = ref(false);
const editCurrent = ref<() => void>();
const selectedProfileUser = ref<User | null>(null);
const draggedChannelId = ref<string | null>(null);
const hoverChannelId = ref<string | null>(null);

const openUserProfile = (user: User) => {
    selectedProfileUser.value = {
        ...user,
        rolesWithServer: user.rolesWithServer ? [...user.rolesWithServer] : (user.roles ? [...user.roles] : [])
    };
};

const textChannels = computed(() => props.channels?.filter(c => c.type === ChannelType.Text) || []);
const voiceChannels = computed(() => props.channels?.filter(c => c.type === ChannelType.Voice) || []);
const whiteboardChannels = computed(() => props.channels?.filter(c => c.type === ChannelType.Whiteboard) || []);
const isWhiteboardEnabled = computed(() => props.selectedServer?.enable_whiteboard !== false);

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

const onChannelDragOver = (channel: Channel) => {
    if (draggedChannelId.value && draggedChannelId.value !== channel.id) {
        hoverChannelId.value = channel.id;
    }
};

const onChannelDragLeave = (e: DragEvent, channel: Channel) => {
    const currentTarget = e.currentTarget as HTMLElement | null;
    const relatedTarget = e.relatedTarget as Node | null;
    if (!currentTarget || !relatedTarget || !currentTarget.contains(relatedTarget)) {
        if (hoverChannelId.value === channel.id) {
            hoverChannelId.value = null;
        }
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
    const otherIds = (props.channels || []).filter(c => c.type !== targetChannel.type).map(c => c.id);
    const allChannelIds = [...reorderedIds, ...otherIds];

    onChannelDragEnd();

    if (props.selectedServer) {
        router.post(`/api/channel/${props.selectedServer.route_key}/reorder`, {
            channel_ids: allChannelIds,
        }, { preserveScroll: true });
    }
};

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
    isChannelModalOpen.value = true;
};

const loading = ref(false);

const createChannel = async () => {
    if (loading.value) return;
    loading.value = true;
    form.post(create.url(props.selectedServer!.route_key), {
        onSuccess: () => {
            isChannelModalOpen.value = false;
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
    router.delete(deleteMethod.url({ server: props.selectedServer!.route_key, channel: channel.route_key }));
};

const editChannel = async (channelKey: string) => {
    if (loading.value) return;
    loading.value = true;

    form.patch(edit.url({ server: props.selectedServer!.route_key, channel: channelKey }), {
        onSuccess: () => {
            isChannelModalOpen.value = false;
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

const isChannelActive = (channel: Channel) => {
    return voiceState.isConnected.value && voiceState.activeChannel.value?.id === channel.id;
};

const isChannelJoining = (channel: Channel) => {
    return voiceState.isJoining.value && voiceState.activeChannel.value?.id === channel.id;
};

const getChannelParticipants = (channel: Channel): User[] => {
    return voiceState.getChannelUsers(channel.id);
};

const isCurrentUser = (user: User) => {
    return String(user.id) === String(page.props.user?.id);
};

const isUserAfk = (user: User) => {
    if (isCurrentUser(user)) {
        return voiceState.isAfk.value;
    }
    return Boolean((user as any).is_afk);
};

const isUserDeafened = (user: User) => {
    if (isCurrentUser(user)) {
        return voiceState.isDeafened.value;
    }
    return Boolean((user as any).is_deafened);
};

const isUserMuted = (user: User) => {
    if (isCurrentUser(user)) {
        return voiceState.isMuted.value;
    }
    return Boolean((user as any).is_muted);
};

const handleVoiceChannelClick = async (channel: Channel) => {
    if (isEditMode.value) return;
    if (isChannelActive(channel)) return;
    await voiceState.joinChannel(channel, props.selectedServer?.id, page.props.user as any);
};

useChannelEvents(props.selectedServer?.id, ['channels']);
</script>

<template>
    <aside
        v-if="selectedServer"
        class="bg-base-100 flex flex-col h-full shrink-0 select-none relative group transition-[width] duration-75 w-full"
    >
        <!-- Top Drag Handle & Server Title Bar -->
        <div class="h-12 px-4 border-b border-base-300 flex items-center justify-between bg-base-200/50 shrink-0">
            <span class="font-bold text-sm truncate text-base-content max-w-[140px]">{{ selectedServer.name }}</span>
            <div class="flex items-center gap-1">
                <!-- Pencil Mode Toggle -->
                <button
                    v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                    :class="isEditMode ? 'btn-warning' : 'btn-ghost'"
                    class="btn btn-xs btn-circle"
                    title="Toggle Edit Channels"
                    @click="isEditMode = !isEditMode"
                >
                    <MdOutlineModeEdit />
                </button>
                
                <!-- Window Swap Drag Handle -->
                <div
                    draggable="true"
                    class="cursor-grab active:cursor-grabbing text-base-content/70 hover:text-primary p-1 rounded hover:bg-base-300 transition-colors"
                    title="Drag to swap sidebar position"
                    @dragstart="startPaneSwapDrag('sidebar')"
                    @dragend="endPaneSwapDrag"
                >
                    <MdDragIndicator class="size-4" />
                </div>
            </div>
        </div>

        <!-- Scrollable Channel Navigation Tree -->
        <div class="flex-1 overflow-y-auto p-3 space-y-4">
            <!-- 1. TEXT CHANNELS CATEGORY -->
            <div>
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-base-content/60 px-2 mb-1">
                    <span>Text Channels</span>
                    <button
                        v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                        class="btn btn-xs btn-ghost btn-square"
                        title="Create Text Channel"
                        @click="openModal(ChannelType.Text)"
                    >
                        <GoPlus />
                    </button>
                </div>
                <div class="space-y-0.5">
                    <div
                        v-for="channel in textChannels"
                        :key="channel.id"
                        class="flex items-center justify-between group/item rounded-lg px-2 py-1.5 transition-all duration-150 relative border-2"
                        :class="[
                            page.url.includes(`/text/${channel.route_key}`) ? 'bg-primary text-primary-content font-semibold' : 'hover:bg-base-200 text-base-content/80',
                            hoverChannelId === channel.id && draggedChannelId && draggedChannelId !== channel.id
                                ? 'border-dotted border-primary bg-primary/15'
                                : 'border-transparent'
                        ]"
                        :draggable="isEditMode"
                        @dragstart="onChannelDragStart($event, channel)"
                        @dragenter.prevent="onChannelDragEnter(channel)"
                        @dragover.prevent="onChannelDragOver(channel)"
                        @dragleave="onChannelDragLeave($event, channel)"
                        @dragend="onChannelDragEnd"
                        @drop="onChannelDrop($event, channel, textChannels)"
                    >
                        <!-- Channel Drag Handle (visible in Edit Mode) -->
                        <div
                            v-if="isEditMode"
                            class="cursor-grab active:cursor-grabbing text-base-content/60 hover:text-primary mr-1 shrink-0"
                            title="Drag to reorder channel"
                        >
                            <MdDragIndicator class="size-3.5" />
                        </div>

                        <Link
                            v-if="!page.url.includes(`/text/${channel.route_key}`)"
                            :href="textChannelRoute.url({ server: selectedServer.route_key, channel: channel.route_key })"
                            class="flex-1 truncate text-sm"
                        >
                            # {{ channel.name }}
                        </Link>
                        <span
                            v-else
                            class="flex-1 truncate text-sm cursor-default"
                        >
                            # {{ channel.name }}
                        </span>
                        <div v-if="isEditMode" class="flex items-center gap-1 shrink-0">
                            <button
                                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                                class="btn btn-xs btn-circle btn-ghost text-warning p-0"
                                @click.prevent="openModal(ChannelType.Text, channel)"
                            >
                                <MdOutlineModeEdit />
                            </button>
                            <ConfirmDialog
                                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                                :confirm="() => deleteChannel(channel)"
                                :description="`Are you sure you want to delete #${channel.name}?`"
                                class-name="btn btn-xs btn-circle btn-ghost text-error p-0"
                                title="Delete Channel"
                            >
                                <MdOutlineDeleteForever />
                            </ConfirmDialog>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. VOICE CHANNELS CATEGORY -->
            <div>
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-base-content/60 px-2 mb-1">
                    <span>Voice Channels</span>
                    <button
                        v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                        class="btn btn-xs btn-ghost btn-square"
                        title="Create Voice Channel"
                        @click="openModal(ChannelType.Voice)"
                    >
                        <GoPlus />
                    </button>
                </div>
                <div class="space-y-1">
                    <div
                        v-for="channel in voiceChannels"
                        :key="channel.id"
                        class="rounded-lg p-1.5 transition-all duration-150 cursor-pointer border-2"
                        :class="[
                            isChannelActive(channel) ? 'bg-success/15 text-success border-success/30 font-medium' : (isChannelJoining(channel) ? 'bg-warning/15 text-warning border-warning/30' : 'hover:bg-base-200 text-base-content/80'),
                            hoverChannelId === channel.id && draggedChannelId && draggedChannelId !== channel.id
                                ? 'border-dotted border-primary bg-primary/15'
                                : (isChannelActive(channel) ? 'border-success/30' : 'border-transparent')
                        ]"
                        :draggable="isEditMode"
                        @dragstart="onChannelDragStart($event, channel)"
                        @dragenter.prevent="onChannelDragEnter(channel)"
                        @dragover.prevent="onChannelDragOver(channel)"
                        @dragleave="onChannelDragLeave($event, channel)"
                        @dragend="onChannelDragEnd"
                        @drop="onChannelDrop($event, channel, voiceChannels)"
                        @click="handleVoiceChannelClick(channel)"
                    >
                        <div class="flex items-center justify-between">
                            <!-- Channel Drag Handle (visible in Edit Mode) -->
                            <div
                                v-if="isEditMode"
                                class="cursor-grab active:cursor-grabbing text-base-content/60 hover:text-primary mr-1 shrink-0"
                                title="Drag to reorder channel"
                                @click.stop
                            >
                                <MdDragIndicator class="size-3.5" />
                            </div>

                            <div class="flex-1 font-medium text-sm truncate flex items-center gap-1.5 min-w-0">
                                <span v-if="isChannelJoining(channel)" class="loading loading-spinner loading-xs text-warning shrink-0"></span>
                                <span v-else-if="isChannelActive(channel)" class="size-2 rounded-full bg-success shrink-0 animate-pulse"></span>
                                <span v-else class="shrink-0">🔊</span>
                                <span class="truncate">{{ channel.name }}</span>
                            </div>

                            <div v-if="isChannelActive(channel) && !isEditMode" class="flex items-center gap-1 shrink-0 ml-1" @click.stop>
                                <span class="badge badge-xs badge-success text-[10px] uppercase font-bold px-1 py-0.5">Connected</span>
                                <button
                                    class="btn btn-xs btn-circle btn-ghost text-error hover:bg-error/20 p-0 size-5 min-h-0"
                                    title="Disconnect from voice"
                                    @click.stop="voiceState.leaveChannel()"
                                >
                                    <MdExitToApp class="size-3.5" />
                                </button>
                            </div>

                            <div v-if="isEditMode" class="flex items-center gap-1 shrink-0" @click.stop>
                                <button
                                    v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                                    class="btn btn-xs btn-circle btn-ghost text-warning p-0"
                                    @click.stop.prevent="openModal(ChannelType.Voice, channel)"
                                >
                                    <MdOutlineModeEdit />
                                </button>
                                <ConfirmDialog
                                    v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                                    :confirm="() => deleteChannel(channel)"
                                    :description="`Are you sure you want to delete voice channel ${channel.name}?`"
                                    class-name="btn btn-xs btn-circle btn-ghost text-error p-0"
                                    title="Delete Channel"
                                >
                                    <MdOutlineDeleteForever />
                                </ConfirmDialog>
                            </div>
                        </div>

                        <!-- Nested Connected Voice Participants List: Only show users if they are in this channel -->
                        <div v-if="getChannelParticipants(channel).length > 0" class="mt-1.5 pl-4 space-y-1" @click.stop>
                            <div
                                v-for="user in getChannelParticipants(channel)"
                                :key="user.id"
                                class="flex items-center justify-between text-xs py-0.5 px-1 rounded hover:bg-base-300/60 cursor-pointer transition-colors group/user"
                                title="Click to view profile"
                                @click.stop="openUserProfile(user)"
                            >
                                <div class="flex items-center gap-2 truncate min-w-0">
                                    <div class="avatar size-5 rounded-full overflow-hidden shrink-0">
                                        <img :src="resolveUrl(user.icon) || defaultIcon" :alt="user.nickname" @error="(e) => (e.target as HTMLImageElement).src = defaultIcon" />
                                    </div>
                                    <span class="truncate font-medium text-base-content" :style="{ color: getMemberRoleColor(user, selectedServer?.roles) }">
                                        {{ user.nickname || user.name }}
                                    </span>
                                </div>

                                <!-- Status Icons: AFK (only AFK icon), Deafen, Mute, or Green dot -->
                                <div class="shrink-0 flex items-center gap-1 pl-1">
                                    <span v-if="isUserAfk(user)" class="text-warning flex items-center" title="AFK">
                                        <TbKeyboardOff class="size-3.5" />
                                    </span>
                                    <template v-else-if="isUserMuted(user) || isUserDeafened(user)">
                                        <span v-if="isUserMuted(user)" class="text-error flex items-center" title="Muted">
                                            <MdMicOff class="size-3.5" />
                                        </span>
                                        <span v-if="isUserDeafened(user)" class="text-error flex items-center" title="Deafened">
                                            <MdHeadsetOff class="size-3.5" />
                                        </span>
                                    </template>
                                    <span v-else class="size-1.5 rounded-full bg-success shrink-0" title="Connected"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. WHITEBOARD CHANNELS CATEGORY -->
            <div v-if="isWhiteboardEnabled">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-base-content/60 px-2 mb-1">
                    <span>Whiteboards</span>
                    <button
                        v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_CREATE_CHANNEL])"
                        class="btn btn-xs btn-ghost btn-square"
                        title="Create Whiteboard Channel"
                        @click="openModal(ChannelType.Whiteboard)"
                    >
                        <GoPlus />
                    </button>
                </div>
                <div class="space-y-0.5">
                    <div
                        v-for="channel in whiteboardChannels"
                        :key="channel.id"
                        class="flex items-center justify-between group/item rounded-lg px-2 py-1.5 transition-all duration-150 border-2"
                        :class="[
                            page.url.includes(`/whiteboard/${channel.route_key}`) ? 'bg-accent text-accent-content font-semibold' : 'hover:bg-base-200 text-base-content/80',
                            hoverChannelId === channel.id && draggedChannelId && draggedChannelId !== channel.id
                                ? 'border-dotted border-primary bg-primary/15'
                                : 'border-transparent'
                        ]"
                        :draggable="isEditMode"
                        @dragstart="onChannelDragStart($event, channel)"
                        @dragenter.prevent="onChannelDragEnter(channel)"
                        @dragover.prevent="onChannelDragOver(channel)"
                        @dragleave="onChannelDragLeave($event, channel)"
                        @dragend="onChannelDragEnd"
                        @drop="onChannelDrop($event, channel, whiteboardChannels)"
                    >
                        <!-- Channel Drag Handle (visible in Edit Mode) -->
                        <div
                            v-if="isEditMode"
                            class="cursor-grab active:cursor-grabbing text-base-content/60 hover:text-primary mr-1 shrink-0"
                            title="Drag to reorder channel"
                        >
                            <MdDragIndicator class="size-3.5" />
                        </div>

                        <Link
                            v-if="!page.url.includes(`/whiteboard/${channel.route_key}`)"
                            :href="whiteboardChannelRoute.url({ server: selectedServer.route_key, channel: channel.route_key })"
                            class="flex-1 truncate text-sm flex items-center gap-1.5"
                        >
                            <span>🎨</span>
                            <span>{{ channel.name }}</span>
                        </Link>
                        <span
                            v-else
                            class="flex-1 truncate text-sm flex items-center gap-1.5 cursor-default"
                        >
                            <span>🎨</span>
                            <span>{{ channel.name }}</span>
                        </span>
                        <div v-if="isEditMode" class="flex items-center gap-1 shrink-0">
                            <button
                                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_EDIT_CHANNEL])"
                                class="btn btn-xs btn-circle btn-ghost text-warning p-0"
                                @click.prevent="openModal(ChannelType.Whiteboard, channel)"
                            >
                                <MdOutlineModeEdit />
                            </button>
                            <ConfirmDialog
                                v-if="perms.has([PermType.CAN_MANAGE_CHANNEL, PermType.CAN_DELETE_CHANNEL])"
                                :confirm="() => deleteChannel(channel)"
                                :description="`Are you sure you want to delete whiteboard ${channel.name}?`"
                                class-name="btn btn-xs btn-circle btn-ghost text-error p-0"
                                title="Delete Channel"
                            >
                                <MdOutlineDeleteForever />
                            </ConfirmDialog>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

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
                                v-model="form.name" autocomplete="off" class="input input-bordered w-full" data-bwignore="true" placeholder="Enter channel name"
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

    <!-- User Profile Popup Modal -->
    <UserProfileModal
        :selected-server="selectedServer"
        :user="selectedProfileUser"
        @close="selectedProfileUser = null"
    />
</template>
