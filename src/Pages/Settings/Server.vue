<script lang="ts" setup>
import { usePerms, fetchJson } from '@/bootstrap';
import { ref, reactive, onMounted } from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { resolveUrl } from "@/bootstrap";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import { PermType, Role, Server } from "@/types";
import { HiClipboardCopy } from 'vue-icons-plus/hi';
import { BsCheckLg } from 'vue-icons-plus/bs';
import { MdLink } from 'vue-icons-plus/md';
import { server } from '@/routes/home';
import ImageEditorModal from '@/Components/ImageEditorModal.vue';
import pb from '@/pocketbase';
import { useRoute, useRouter } from 'vue-router';

const perms = usePerms();
const route = useRoute();
const router = useRouter();

const {selectedServer, inviteCode} = defineProps<{
    selectedServer?: Server,
    inviteCode?: string,
}>();

const currentServer = ref<Server | undefined>(selectedServer);
const code = ref<string>(inviteCode || '');
const icon = ref<string | null>(selectedServer?.icon ? resolveUrl(selectedServer?.icon) : null);
const inputFile = ref<File | null>(null);
const isEditorOpen = ref(false);
const editorImageSource = ref<File | null>(null);
const isDraggingOver = ref(false);

const form = reactive({
    name: selectedServer?.name || '',
    description: selectedServer?.description || '',
    icon: null as File | null,
    default_role_id: selectedServer?.default_role_id || null as string | null,
    enable_whiteboard: selectedServer?.enable_whiteboard ?? true,
    isDirty: false,
    errors: {} as Record<string, string>,
});

const serverRoles = ref<Role[]>([]);

const loadSettingsData = async () => {
    const serverId = (route.params.serverId as string) || selectedServer?.id;
    if (!serverId) return;
    try {
        const s = await pb.collection('servers').getOne(serverId, { requestKey: null });
        currentServer.value = {
            id: s.id,
            name: s.name,
            slug: s.slug,
            description: s.description,
            owner: s.owner,
            enable_whiteboard: s.enable_whiteboard,
            icon: s.icon,
            route_key: s.id
        } as unknown as Server;

        form.name = s.name;
        form.description = s.description || '';
        form.enable_whiteboard = s.enable_whiteboard ?? true;

        if (s.icon) {
            icon.value = resolveUrl(s.icon);
        }

        try {
            const roleRecs = await pb.collection('roles').getFullList({
                filter: `server = "${serverId}"`,
                sort: 'importance',
                requestKey: null
            });
            serverRoles.value = roleRecs.map(r => ({
                id: r.id,
                server_id: r.server,
                name: r.name,
                color: r.color,
                importance: r.importance,
                perms: r.perms || []
            })) as unknown as Role[];
        } catch {
            // ignore role fetch error
        }

        try {
            const invites = await pb.collection('invites').getList(1, 1, {
                filter: `server = "${serverId}"`,
                sort: '-created',
                requestKey: null
            });
            if (invites.items.length > 0) {
                code.value = invites.items[0].code;
            }
        } catch {
            // ignore invite fetch error
        }
    } catch (err) {
        console.error('Error loading server settings:', err);
    }
};

onMounted(() => {
    loadSettingsData();
});

const error = ref<string | null>(null);

const onFileSelected = (file: File | null) => {
    if (!file) return;
    editorImageSource.value = file;
    isEditorOpen.value = true;
};

const onDropServerIcon = (e: DragEvent) => {
    if (!perms.value?.has([PermType.CAN_EDIT_SERVER])) return;
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

async function handleSave() {
    if (!form.name || form.name.trim() === "") {
        error.value = "Server name cannot be empty.";
        return;
    }
    const targetId = currentServer.value?.id || selectedServer?.id;
    if (!targetId) return;
    error.value = null;

    try {
        const formData = new FormData();
        formData.append('name', form.name);
        formData.append('description', form.description);
        formData.append('enable_whiteboard', String(form.enable_whiteboard));
        if (form.icon) {
            formData.append('icon', form.icon);
        }

        await pb.collection('servers').update(targetId, formData);
    } catch (err: unknown) {
        error.value = (err as { message?: string })?.message || 'Failed to update server.';
    }
}

async function deleteServer() {
    const targetId = currentServer.value?.id || selectedServer?.id;
    if (!targetId) return;
    try {
        await pb.collection('servers').delete(targetId);
        router.push('/home');
    } catch (err: unknown) {
        error.value = (err as { message?: string })?.message || 'Failed to delete server.';
    }
}

const copyCodeStatus = ref<Record<string, boolean>>({});
const copyLinkStatus = ref<Record<string, boolean>>({});

const getInviteLink = (inviteCodeString: string) => {
    const base = pb.baseUrl || (typeof window !== 'undefined' ? window.location.origin : '');
    return `${base}/?invite=${encodeURIComponent(inviteCodeString)}`;
};

const copyCodeToClipboard = (code: string) => {
    navigator.clipboard.writeText(code);
    copyCodeStatus.value[code] = true;
    setTimeout(() => {
        delete copyCodeStatus.value[code];
    }, 2000);
};

const copyLinkToClipboard = (code: string) => {
    const link = getInviteLink(code);
    navigator.clipboard.writeText(link);
    copyLinkStatus.value[code] = true;
    setTimeout(() => {
        delete copyLinkStatus.value[code];
    }, 2000);
};

const createdInviteCode = ref<string | null>(null);

const generateInvite = async () => {
    try {
        const {data} = await fetchJson(`/server/${selectedServer!.id}/invites`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        if (data && data.invite) {
            createdInviteCode.value = data.invite.code;
        }
    } catch {
        // Handle error
    }
};
</script>

<template>
    <div class="flex h-screen bg-base-100 overflow-hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <div class="px-6 pt-6 md:px-10 md:pt-10 max-w-6xl mx-auto w-full pb-0">
                <SettingsHeader :selected-server="(currentServer || selectedServer) as Server"/>
            </div>

            <div class="flex-1 overflow-y-auto p-6 md:p-10 pt-0">
                <div class="max-w-4xl mx-auto space-y-8 pb-20">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-base-content">Server Settings</h1>
                        <div class="flex space-x-3">
                            <router-link :to="server.url((currentServer || selectedServer)?.id || '')" class="btn btn-neutral px-6">
                                ← Back to Server
                            </router-link>
                            <button
                                class="btn btn-success px-8"
                                :disabled="!perms.has([PermType.CAN_EDIT_SERVER]) || !form.isDirty"
                                @click="handleSave">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- General Settings -->
                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">General Overview</h2>
                            <div class="flex flex-col md:flex-row gap-8">
                                <div
                                    class="flex-shrink-0"
                                    @dragover.prevent="isDraggingOver = true"
                                    @dragleave.prevent="isDraggingOver = false"
                                    @drop.prevent="onDropServerIcon"
                                >
                                    <label class="label"><span class="label-text font-medium">Server Icon</span></label>
                                    <label class="relative cursor-pointer has-[:disabled]:cursor-not-allowed block mt-2" for="serverIcon">
                                        <input
                                            id="serverIcon"
                                            :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                            accept="image/png, image/jpeg, image/webp"
                                            autocomplete="off"
                                            class="hidden peer"
                                            data-bwignore="true"
                                            type="file"
                                            @change="onFileSelected((<HTMLInputElement>$event.target).files![0])"
                                        />
                                        <div
                                            :class="{'ring-2 ring-primary ring-offset-2 scale-105': isDraggingOver}"
                                            class="w-32 h-32 rounded-full bg-base-100 border border-base-300 flex justify-center items-center transition-all duration-300 ease-in-out peer-enabled:hover:border-primary peer-disabled:opacity-50 overflow-hidden">
                                            <img
                                                v-if="icon" :src="icon" alt="Server Icon"
                                                class="w-full h-full object-cover"/>
                                            <span v-else class="text-4xl text-base-content/30">+</span>
                                        </div>
                                    </label>
                                    <span class="text-[11px] text-base-content/50 block mt-1">Click or drag image</span>
                                    <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>

                                    <ImageEditorModal
                                        v-model="isEditorOpen"
                                        :image-source="editorImageSource"
                                        title="Edit Server Icon"
                                        :aspect-ratio-lock="1"
                                        :circle-mask="true"
                                        @save="handleEditorSave"
                                    />
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Server Name</span></label>
                                        <input
                                            id="serverName"
                                            v-model="form.name"
                                            :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                            autocomplete="off"
                                            class="input input-bordered w-full bg-base-100 text-base-content"
                                            data-bwignore="true"
                                            placeholder="Enter your server name"
                                            type="text"
                                        />
                                        <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Description</span></label>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                            class="textarea textarea-bordered w-full bg-base-100 text-base-content h-24 resize-none"
                                            placeholder="Enter server description"
                                        ></textarea>
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Default Role</span></label>
                                        <div class="dropdown dropdown-top w-full">
                                            <button
                                                id="defaultRole"
                                                type="button"
                                                :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                                class="btn btn-sm bg-base-100 hover:bg-base-300 border-base-300 w-full justify-between font-normal h-10 min-h-0"
                                                tabindex="0">
                                                <span>{{ (serverRoles.length > 0 ? serverRoles : selectedServer?.roles)?.find((r: Role) => r.id === form.default_role_id)?.name || 'None' }}</span>
                                                <span class="opacity-50 text-xs">▲</span>
                                            </button>
                                            <ul
                                                class="dropdown-content menu bg-base-100 rounded-box z-[50] w-full p-2 shadow-lg gap-y-1 mb-2 border border-base-300"
                                                tabindex="0">
                                                <li>
                                                    <button
                                                        type="button"
                                                        :class="form.default_role_id === null ? 'bg-primary/10 text-primary hover:bg-primary/20' : 'hover:bg-base-200'"
                                                        class="btn btn-ghost btn-sm justify-start w-full"
                                                        @click="form.default_role_id = null">
                                                        <BsCheckLg
                                                            v-if="form.default_role_id === null"
                                                            class="w-4 h-4 mr-2"
                                                        />
                                                        <span v-else class="w-4 h-4 mr-2"></span>
                                                        None
                                                    </button>
                                                </li>
                                                <li v-for="role in (serverRoles.length > 0 ? serverRoles : (selectedServer?.roles || []))" :key="role.id">
                                                    <button
                                                        type="button"
                                                        :class="form.default_role_id === role.id ? 'bg-primary/10 text-primary hover:bg-primary/20' : 'hover:bg-base-200'"
                                                        class="btn btn-ghost btn-sm justify-start w-full"
                                                        @click="form.default_role_id = role.id">
                                                        <BsCheckLg
                                                            v-if="form.default_role_id === role.id"
                                                            class="w-4 h-4 mr-2"
                                                        />
                                                        <span v-else class="w-4 h-4 mr-2"></span>
                                                        <div class="w-3 h-3 rounded-full shrink-0 border border-black/10 mr-1" :style="{ backgroundColor: role.color }"></div>
                                                        {{ role.name }}
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                        <p class="text-xs text-base-content/60 mt-1">Automatically assign this role to new members when they join.</p>
                                        <ErrorAlert v-if="form.errors.default_role_id" :message="form.errors.default_role_id" class="mt-2"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Features</h2>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center bg-base-100 p-4 rounded-xl border border-base-300">
                                    <div>
                                        <span class="font-semibold text-base-content">Enable Whiteboard</span>
                                        <p class="text-sm text-base-content/70 mt-1">Allow creation and use of whiteboard channels in this server.</p>
                                    </div>
                                    <input
                                        v-model="form.enable_whiteboard"
                                        :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                        class="toggle toggle-primary"
                                        type="checkbox"
                                    />
                                </div>
                                <div class="flex justify-between items-center bg-base-100 p-4 rounded-xl border border-base-300">
                                    <div>
                                        <span class="font-semibold text-base-content">Allow Attachments</span>
                                        <p class="text-sm text-base-content/70 mt-1">Let's you send images and videos in chat.</p>
                                    </div>
                                    <input class="toggle toggle-primary" disabled type="checkbox"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invite Code -->
                    <div v-if="perms.has([PermType.CAN_INVITE])" class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Invite Code</h2>
                            <div class="space-y-4">
                                <div v-if="inviteCode" class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-base-100 p-4 rounded-xl border border-base-300">
                                    <div>
                                        <span class="font-semibold text-base-content">Server Invite Code & Link</span>
                                        <p class="text-sm text-base-content/70 mt-1">Share this code or link with others so they can join.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono bg-base-200 p-2 px-3 rounded-lg border border-base-300 font-bold tracking-wider text-xs select-all">{{ inviteCode }}</span>
                                        <button class="btn btn-sm btn-ghost gap-1.5" @click="copyCodeToClipboard(inviteCode)">
                                            <HiClipboardCopy class="w-4 h-4" />
                                            <span class="text-xs">{{ copyCodeStatus[inviteCode] ? 'Copied Code' : 'Copy Code' }}</span>
                                        </button>
                                        <button class="btn btn-sm btn-primary gap-1.5" @click="copyLinkToClipboard(inviteCode)">
                                            <MdLink class="w-4 h-4" />
                                            <span class="text-xs">{{ copyLinkStatus[inviteCode] ? 'Link Copied!' : 'Copy Link' }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div v-if="createdInviteCode" class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-base-100 p-4 rounded-xl border border-primary/30">
                                    <div>
                                        <span class="font-semibold text-base-content">Generated Invite Code & Link</span>
                                        <p class="text-sm text-base-content/70 mt-1">A new invite code has been generated.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono bg-base-200 p-2 px-3 rounded-lg border border-base-300 font-bold tracking-wider text-xs select-all">{{ createdInviteCode }}</span>
                                        <button class="btn btn-sm btn-ghost gap-1.5" @click="copyCodeToClipboard(createdInviteCode)">
                                            <HiClipboardCopy class="w-4 h-4" />
                                            <span class="text-xs">{{ copyCodeStatus[createdInviteCode] ? 'Copied Code' : 'Copy Code' }}</span>
                                        </button>
                                        <button class="btn btn-sm btn-primary gap-1.5" @click="copyLinkToClipboard(createdInviteCode)">
                                            <MdLink class="w-4 h-4" />
                                            <span class="text-xs">{{ copyLinkStatus[createdInviteCode] ? 'Link Copied!' : 'Copy Link' }}</span>
                                        </button>
                                    </div>
                                </div>
                                <button
                                    class="btn btn-primary btn-sm"
                                    @click="generateInvite">
                                    Generate Invite Code
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card bg-error/10 shadow-sm border border-error/20">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-error/20 pb-2 mb-4 text-error">Danger Zone</h2>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-semibold text-base-content">Delete Server</span>
                                    <p class="text-sm text-base-content/70 mt-1">Once you delete a server, there is no going back. Please be certain.</p>
                                </div>
                                <ConfirmDialog
                                    :class-name="`btn btn-error px-6 ${!perms.has([PermType.CAN_DELETE_SERVER]) ? 'btn-disabled' : ''}`"
                                    :confirm="deleteServer"
                                    description="Are you sure you want to delete this server? This action cannot be undone."
                                    text="Delete Server"
                                    title="Delete server"
                                />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
