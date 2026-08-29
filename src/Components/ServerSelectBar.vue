<script lang="ts" setup>
import {text} from '@/routes/home';
import {server as settingsServer} from '@/routes/settings';
import {useRouter, useRoute} from 'vue-router';
import pb from '@/pocketbase';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {computed, onMounted, reactive, ref} from 'vue';
import {defaultIcon, joinServer, resolveUrl, usePerms} from "@/bootstrap";
import {PermType, Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {GoPlus} from 'vue-icons-plus/go';
import {BsDoorOpen, BsGearFill} from 'vue-icons-plus/bs';
import {MdMic, MdMicOff, MdHeadset, MdHeadsetOff, MdExitToApp} from 'vue-icons-plus/md';
import {TbKeyboard, TbKeyboardOff} from 'vue-icons-plus/tb';
import {useVoiceCallStateMachine} from '@/composables/useVoiceCallStateMachine';
import {useServerStore} from '@/composables/useServerStore';
import ImageEditorModal from '@/Components/ImageEditorModal.vue';

const serverStore = useServerStore();

const perms = usePerms();
const voiceState = useVoiceCallStateMachine();
const routerInstance = useRouter();
const authUser = computed(() => pb.authStore.model ? {
    id: pb.authStore.model.id,
    nickname: pb.authStore.model.name || pb.authStore.model.username || 'User',
    icon: pb.authStore.model.avatar || null,
    status: pb.authStore.model.status || 'online',
} : null);
const route = useRoute();
const isHomePage = computed(() => route.path !== '/profile');

const handleLogout = () => {
    pb.authStore.clear();
    routerInstance.push('/login');
};

const props = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
}>();

onMounted(() => {
    serverStore.fetchServers();
});

const displayedServers = computed(() => (props.servers && props.servers.length > 0) ? props.servers : serverStore.servers.value);


const serverModal = ref<HTMLDialogElement>();
const activeTab = ref<'create' | 'join'>('create');
const val = ref<[number, string?]>();
const joinCodeInput = ref('');
const serverInfo = ref<{ name: string; description: string; icon: string; members_count: number; online_count: number } | null>(null);
const checkLoading = ref(false);
const checkError = ref<string | null>(null);
const isEditorOpen = ref(false);
const editorImageSource = ref<File | null>(null);

let checkDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const checkServerCode = () => {
    if (checkDebounceTimer) clearTimeout(checkDebounceTimer);
    checkDebounceTimer = setTimeout(async () => {
        const codeToTest = joinCodeInput.value.trim();
        if (!codeToTest) {
            serverInfo.value = null;
            checkError.value = null;
            return;
        }
        checkLoading.value = true;
        try {
            const res = await fetch(`/invites/${encodeURIComponent(codeToTest)}/check`);
            const data = await res.json();
            if (res.ok && data.valid) {
                serverInfo.value = data.server;
                checkError.value = null;
            } else {
                serverInfo.value = null;
                checkError.value = data.message || 'Invalid or expired server code.';
            }
        } catch {
            serverInfo.value = null;
            checkError.value = 'Failed to verify invite code.';
        } finally {
            checkLoading.value = false;
        }
    }, 300);
};

const form = reactive({
    name: '',
    description: '',
    icon: null as File | null,
    error: null as string | null,
    errors: {} as Record<string, string>,
});

const loading = ref(false);

const createServer = async () => {
    if (loading.value || !pb.authStore.model?.id) return;
    loading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', form.name);
        formData.append('slug', form.name.toLowerCase().replace(/\s+/g, '-'));
        formData.append('description', form.description);
        formData.append('owner', pb.authStore.model.id);
        formData.append('enable_whiteboard', 'true');
        if (form.icon) {
            formData.append('icon', form.icon);
        }

        const serverRecord = await pb.collection('servers').create(formData);
        await pb.collection('members').create({
            server: serverRecord.id,
            user: pb.authStore.model.id,
        });

        serverModal.value?.close();
        form.name = '';
        form.description = '';
        form.icon = null;
    } catch (err: unknown) {
        console.error('Error creating server:', err);
    } finally {
        loading.value = false;
    }
};

async function leaveServer() {
    if (!props.selectedServer?.id || !pb.authStore.model?.id) return;
    try {
        await pb.send(`/api/server/${props.selectedServer.id}/leave`, { method: 'POST' });
        routerInstance.push('/home');
    } catch (err: unknown) {
        console.error('Error leaving server:', err);
    }
}

const icon = ref<string | null>(null);
const inputFile = ref<File | null>();
const isDraggingOver = ref(false);

const onFileSelected = (val: File) => {
    if (!val) return;
    editorImageSource.value = val;
    isEditorOpen.value = true;
};

const onDropServerIcon = (e: DragEvent) => {
    isDraggingOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        onFileSelected(file);
    }
};

const handleEditorSave = (editedFile: File) => {
    inputFile.value = editedFile;
    form.icon = editedFile;
    icon.value = URL.createObjectURL(editedFile);
};
</script>

<template>
    <div class="relative flex items-center justify-between h-16 bg-base-100 px-4 w-full select-none">
        <!-- Left Side: Logo -->
        <div class="flex items-center shrink-0 z-10">
            <router-link to="/">
                <ApplicationLogo class="block h-10 w-auto fill-current ml-1"/>
            </router-link>
        </div>

        <!-- Center: Server Icons (Absolute Centered so it NEVER shifts when voice pill appears) -->
        <div class="absolute left-1/2 -translate-x-1/2 flex items-center justify-center max-w-[calc(100%-480px)] overflow-x-auto overflow-y-hidden px-2 scrollbar-hide pointer-events-auto z-0">
            <div class="flex items-center gap-3 min-w-max h-full">
                <div v-for="server in displayedServers" :key="server.id" class="shrink-0">
                    <router-link :to="text.url(server.route_key)">
                        <div :data-tip="server.name" class="tooltip tooltip-bottom">
                            <div
                                :class="{'ring ring-primary ring-offset-base-100 ring-offset-2': selectedServer?.id === server.id}"
                                class="btn btn-ghost btn-circle avatar"
                            >
                                <div class="w-10 rounded-full">
                                    <img
                                        :src="resolveUrl(server.icon) || defaultIcon"
                                        @error="(e) => (e.target as HTMLImageElement).src = defaultIcon"
                                        alt="Server"/>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>

                <button v-if="isHomePage" class="btn btn-circle btn-sm shrink-0" @click="() => serverModal?.showModal()">
                    <GoPlus scale="1.5"/>
                </button>
            </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center justify-end gap-2 ml-auto shrink-0 z-10">
            <!-- Active Voice Call Status Pill -->
            <div v-if="!voiceState.isDisconnected.value" class="flex items-center gap-2 bg-base-200 border border-success/30 px-3 py-1 rounded-full shadow-xs mr-2">
                <span class="size-2.5 rounded-full bg-success animate-pulse"></span>
                <span class="text-xs font-semibold text-success">🔊 {{ voiceState.activeChannel.value?.name || 'Voice' }}</span>

                <!-- Voice Action Controls: Mute, Deafen, AFK, Disconnect -->
                <div class="flex items-center gap-1 ml-2 border-l border-base-300 pl-2">
                    <button
                        :class="voiceState.isMuted.value ? 'btn-error' : 'btn-ghost'"
                        class="btn btn-xs btn-square"
                        title="Toggle Mic"
                        @click="voiceState.toggleMute()">
                        <MdMicOff v-if="voiceState.isMuted.value" />
                        <MdMic v-else />
                    </button>
                    <button
                        :class="voiceState.isDeafened.value ? 'btn-error' : 'btn-ghost'"
                        class="btn btn-xs btn-square"
                        title="Toggle Deafen"
                        @click="voiceState.toggleDeafen()">
                        <MdHeadsetOff v-if="voiceState.isDeafened.value" />
                        <MdHeadset v-else />
                    </button>
                    <button
                        :class="voiceState.isAfk.value ? 'btn-warning' : 'btn-ghost'"
                        class="btn btn-xs btn-square"
                        title="Toggle AFK"
                        @click="voiceState.toggleAfk(authUser?.status)">
                        <TbKeyboardOff v-if="voiceState.isAfk.value" />
                        <TbKeyboard v-else />
                    </button>
                    <button
                        class="btn btn-xs btn-square btn-error btn-outline ml-1"
                        title="Disconnect Voice"
                        @click="voiceState.leaveChannel()">
                        <MdExitToApp />
                    </button>
                </div>
            </div>

            <!-- Server Settings -->
            <router-link
                v-if="selectedServer && perms.hasAny([PermType.CAN_MANAGE_SERVER, PermType.CAN_MANAGE_ROLE, PermType.CAN_MANAGE_MEMBERS])"
                :to="settingsServer.url(selectedServer?.route_key)"
                class="btn btn-ghost btn-circle tooltip tooltip-left" data-tip="Server settings">
                <BsGearFill animation="spin-hover" scale="1.2"/>
            </router-link>

            <!-- User Profile -->
            <div class="dropdown dropdown-end">
                <div class="flex items-center btn btn-ghost px-2" role="button" tabindex="0">
                    <div class="mr-2 hidden md:block">{{ authUser?.nickname }}</div>
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img
                                :src="resolveUrl(authUser?.icon) || defaultIcon"
                                @error="(e) => (e.target as HTMLImageElement).src = defaultIcon"
                                alt="User Avatar"/>
                        </div>
                    </div>
                </div>
                <ul class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow" tabindex="0">
                    <li>
                        <router-link to="/profile">Profile</router-link>
                    </li>
                    <li v-if="selectedServer">
                        <ConfirmDialog
                            :confirm="leaveServer"
                            class-name="text-error hover:bg-error hover:text-error-content flex items-center justify-between w-full"
                            description="Are you sure you want to leave this server?"
                            title="Leave server"
                        >
                            Leave Server
                            <BsDoorOpen scale="1.1"/>
                        </ConfirmDialog>
                    </li>
                    <div class="divider my-0"></div>
                    <li>
                        <button type="button" @click="handleLogout">Log Out</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <dialog ref="serverModal" class="modal">
            <div class="modal-box bg-base-200">
                <!-- Create Server-->
                <div class="tabs tabs-bordered flex justify-center mb-4" role="tablist">
                    <button
                        :class="{'tab-active': activeTab === 'create'}"
                        class="tab text-lg h-10 w-1/2"
                        role="tab"
                        @click="activeTab = 'create'">Create Server
                    </button>
                    <button
                        :class="{'tab-active': activeTab === 'join'}"
                        class="tab text-lg h-10 w-1/2"
                        role="tab"
                        @click="activeTab = 'join'">Join Server
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="py-2">
                    <!-- Create Server Tab Content -->
                    <div v-if="activeTab === 'create'">
                        <!-- Create Server Form -->
                        <form @submit.prevent="createServer">
                            <div
                                class="form-control flex flex-row items-center gap-4 mb-4"
                                @dragover.prevent="isDraggingOver = true"
                                @dragleave.prevent="isDraggingOver = false"
                                @drop.prevent="onDropServerIcon"
                            >
                                <label
                                    :class="{'ring-2 ring-primary ring-offset-2 scale-105': isDraggingOver}"
                                    class="cursor-pointer rounded-full bg-base-300 transition-all duration-300 ease-in-out hover:bg-base-100 flex items-center justify-center size-16 shadow-inner"
                                    for="serverIcon">
                                    <img
v-if="icon !== null" :src="icon" alt=""
                                         class="size-16 rounded-full object-cover"/>
                                    <GoPlus v-else scale="2"/>
                                </label>
                                <label class="cursor-pointer font-medium text-sm" for="serverIcon">
                                    <span>Upload server icon</span>
                                    <span class="block text-xs text-base-content/50">Click or drag image</span>
                                </label>
                                <input
                                    id="serverIcon"
                                    ref="inputFile"
                                    accept="image/png, image/jpeg, image/webp"
                                    autocomplete="off"
                                    class="hidden"
                                    data-bwignore="true"
                                    type="file"
                                    @input="onFileSelected((<HTMLInputElement>$event.target).files![0])"
                                />
                            </div>
                            <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>

                            <ImageEditorModal
                                v-model="isEditorOpen"
                                :image-source="editorImageSource"
                                title="Edit Server Icon"
                                :aspect-ratio-lock="1"
                                :circle-mask="true"
                                @save="handleEditorSave"
                            />

                            <fieldset class="fieldset w-full">
                                <legend class="fieldset-legend">Server Name</legend>
                                <input
                                    v-model="form.name" autocomplete="off" class="input input-bordered w-full bg-base-100" data-bwignore="true"
                                    placeholder="Enter server name"
                                    type="text"/>
                                <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                            </fieldset>

                            <fieldset class="fieldset w-full mt-4">
                                <legend class="fieldset-legend">Description (Optional)</legend>
                                <input
                                    v-model="form.description" autocomplete="off" class="input input-bordered w-full bg-base-100" data-bwignore="true"
                                    placeholder="Enter server description"
                                    type="text"/>
                            </fieldset>

                            <div class="modal-action mt-6">
                                <button class="btn btn-primary w-full" type="submit">Create Server</button>
                            </div>
                        </form>
                    </div>

                    <!-- Join Server Tab Content -->
                    <div v-if="activeTab === 'join'">
                        <fieldset class="fieldset w-full mb-4">
                            <legend class="fieldset-legend">Server Invite Code</legend>
                            <input
                                id="join-code"
                                v-model="joinCodeInput"
                                autocomplete="off"
                                class="input input-bordered w-full bg-base-100"
                                data-bwignore="true"
                                name="code"
                                placeholder="Enter invite code"
                                type="text"
                                @input="checkServerCode"
                                @blur="checkServerCode"
                            />
                        </fieldset>

                        <!-- Server Preview Card -->
                        <div v-if="serverInfo" class="mb-4 p-3 bg-success/10 border border-success/30 rounded-lg flex items-center gap-3">
                            <img v-if="serverInfo.icon" :src="serverInfo.icon" class="size-10 rounded-full object-cover border border-base-300" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-success truncate">Joining: {{ serverInfo.name }}</p>
                                <p v-if="serverInfo.description" class="text-xs text-base-content/70 truncate">{{ serverInfo.description }}</p>
                                <div class="flex items-center gap-4 mt-1 text-xs text-base-content/80 font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-success"></span>
                                        {{ serverInfo.online_count ?? 0 }} Online
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-base-content/40"></span>
                                        {{ serverInfo.members_count ?? 0 }} Members
                                    </span>
                                </div>
                            </div>
                        </div>

                        <ErrorAlert v-if="checkError" :message="checkError" class="mb-4" />

                        <button
                            class="btn btn-primary w-full"
                            :disabled="!serverInfo || checkLoading"
                            @click="async () => { val = await joinServer(joinCodeInput); if (val[0] === 200) { serverModal?.close(); joinCodeInput = ''; serverInfo = null; } }">
                            <span v-if="checkLoading" class="loading loading-spinner loading-xs"></span>
                            Join Server
                        </button>
                        <ErrorAlert v-if="val && val[0] !== 200" :message="val[1]" class="mt-3"/>
                    </div>
                </div>
                <!-- Close Button -->
                <button
                    class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    @click="() => serverModal?.close()">✕
                </button>
            </div>
            <form class="modal-backdrop" method="dialog">
                <button>close</button>
            </form>
        </dialog>
    </Teleport>
</template>
