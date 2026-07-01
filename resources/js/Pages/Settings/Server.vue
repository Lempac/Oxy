<script lang="ts" setup>
import { usePerms } from '@/bootstrap';
import {destroy, update} from '@/routes/server';
import {ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {router, useForm} from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {baseUrl} from "@/bootstrap";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import {PermType, Server} from "@/types";
import { HiClipboardCopy } from 'vue-icons-plus/hi';
import { server } from '@/routes/home';
import { Link } from '@inertiajs/vue3';

const perms = usePerms();
const {selectedServer, inviteCode} = defineProps<{
    selectedServer?: Server,
    inviteCode?: string,
}>();

const icon = ref<string | null>(selectedServer?.icon ? baseUrl + selectedServer?.icon : null);
const inputFile = ref<File | null>(null);

const form = useForm({
    name: selectedServer?.name,
    description: selectedServer?.description,
    icon: null as File | null,
});

form.defaults();

const updateIcon = (file: File | null) => {
    console.log('Selected file:', file);
    if (!file) return;
    inputFile.value = file;
    form.icon = file;
    icon.value = URL.createObjectURL(file);
};

function handleSave() {
    if (!form.name || form.name.trim() === "") {
        form.setError("name", "Server name cannot be empty.");
        return;
    }
    form.clearErrors(); // Clear any existing errors
    form.post(update.url(selectedServer!.route_key), {
        onSuccess: () => {
            router.reload(); // This will reload the current Inertia page without a full page reload
        },
    });
}

function deleteServer() {
    router.delete(destroy.url(selectedServer!.route_key), {
        onSuccess: () => {
            router.visit('/home');
        },
    });
}

const copyText = ref('Copy');

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    copyText.value = 'Copied!';
    setTimeout(() => {
        copyText.value = 'Copy';
    }, 2000);
}
</script>

<template>
    <div class="flex h-screen bg-base-100 overflow-hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <div class="px-6 pt-6 md:px-10 md:pt-10 max-w-6xl mx-auto w-full pb-0">
                <SettingsHeader :selected-server="selectedServer!"/>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 md:p-10 pt-0">
                <div class="max-w-4xl mx-auto space-y-8 pb-20">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-base-content">Server Settings</h1>
                        <div class="flex space-x-3">
                            <Link :href="server.url(selectedServer!.route_key)" class="btn btn-neutral px-6">
                                ← Back to Server
                            </Link>
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
                                <div class="flex-shrink-0">
                                    <label class="label"><span class="label-text font-medium">Server Icon</span></label>
                                    <label class="relative cursor-pointer has-[:disabled]:cursor-not-allowed block mt-2" for="serverIcon">
                                        <input
                                            id="serverIcon"
                                            :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                            accept="image/png, image/jpeg"
                                            class="hidden peer"
                                            type="file"
                                            @change="updateIcon((<HTMLInputElement>$event.target).files![0])"
                                        />
                                        <div
                                            class="w-32 h-32 rounded-full bg-base-100 border border-base-300 flex justify-center items-center transition-all duration-300 ease-in-out peer-enabled:hover:border-primary peer-disabled:opacity-50 overflow-hidden">
                                            <img
                                                v-if="icon" :src="icon" alt="Server Icon"
                                                class="w-full h-full object-cover"/>
                                            <span v-else class="text-4xl text-base-content/30">+</span>
                                        </div>
                                    </label>
                                    <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text font-medium">Server Name</span></label>
                                        <input
                                            id="serverName"
                                            v-model="form.name"
                                            :disabled="!perms.has([PermType.CAN_EDIT_SERVER])"
                                            class="input input-bordered w-full bg-base-100 text-base-content"
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Features</h2>
                            <div class="flex justify-between items-center bg-base-100 p-4 rounded-xl border border-base-300">
                                <div>
                                    <span class="font-semibold text-base-content">Allow Attachments</span>
                                    <p class="text-sm text-base-content/70 mt-1">Let's you send images and videos in chat.</p>
                                </div>
                                <input class="toggle toggle-primary" disabled type="checkbox"/>
                            </div>
                        </div>
                    </div>

                    <!-- Invite Code -->
                    <div v-if="inviteCode && perms.has([PermType.CAN_INVITE])" class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Invite Code</h2>
                            <div class="flex justify-between items-center bg-base-100 p-4 rounded-xl border border-base-300">
                                <div>
                                    <span class="font-semibold text-base-content">Server Invite Code</span>
                                    <p class="text-sm text-base-content/70 mt-1">Share this code with others so they can join.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono bg-base-200 p-2 px-3 rounded-lg border border-base-300 font-bold tracking-wider">{{ inviteCode }}</span>
                                    <div class="tooltip tooltip-top" :data-tip="copyText">
                                        <button class="btn btn-square btn-ghost" @click="copyToClipboard(inviteCode)">
                                            <HiClipboardCopy class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
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
