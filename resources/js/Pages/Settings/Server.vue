<script lang="ts" setup>
import { server as serverRoute } from '@/routes/home';
import { destroy, update } from '@/routes/server';
import { ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {Link, router, useForm, usePage} from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {baseUrl, bigIntToPerms} from "@/bootstrap";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import {Perms, PermType, Role, Server} from "@/types";

const {selectedServer} = defineProps<{
    selectedServer?: Server,
}>();

const icon = ref<string | null>(selectedServer?.icon ? baseUrl + selectedServer?.icon : null);
const inputFile = ref<File | null>(null);
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

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
    form.post(update.url(selectedServer!.id), {
        onSuccess: () => {
            router.reload(); // This will reload the current Inertia page without a full page reload
        },
    });
}

function deleteServer() {
    router.delete(destroy.url(selectedServer!.id), {
        onSuccess: () => {
            router.visit('/home');
        },
    });
}

if (selectedServer && selectedServer.roles !== null) {
    perms.value = bigIntToPerms(selectedServer.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- navbar -->
            <SettingsHeader :selected-server="selectedServer!"/>

            <div class="flex justify-end mb-6 space-x-4">
                <button
                    :class="`btn ${form.isDirty ? 'btn-neutral' : ''} px-6`"
                    :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                    @click="handleSave">
                    Save Changes
                </button>
                <Link :href="serverRoute.url(selectedServer!.id)" class="btn btn-neutral">
                    Cancel
                </Link>
            </div>

            <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-white mb-6">Server Settings</h1>
                <!-- Server info -->
                <div class="bg-gray-800 p-6 rounded-lg mb-8">
                    <div class="flex items-center">
                        <label class="relative cursor-pointer has-[:disabled]:cursor-not-allowed" for="serverIcon">
                            <input
                                id="serverIcon"
                                :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                                accept="image/png, image/jpeg"
                                class="hidden peer"
                                type="file"
                                @change="updateIcon((<HTMLInputElement>$event.target).files![0])"
                            />
                            <div
                                class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-600 flex justify-center items-center transition-all duration-300 ease-in-out peer-enabled:hover:bg-transparent peer-disabled:input-disabled">
                                <img
                                    v-if="icon" :src="icon" alt="Server Icon"
                                    class="w-full h-full rounded-full object-cover"/>
                                <span v-else class="text-4xl text-gray-500">+</span>
                            </div>
                        </label>
                        <div class="w-full ml-[15%]">
                            <label class="text-white" for="serverName">Server Name</label>
                            <input
                                id="serverName"
                                v-model="form.name"
                                :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                                class="input input-bordered w-full mt-2 bg-gray-600 text-white"
                                placeholder="Enter your server name"
                                type="text"
                            />
                            <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                            <label class="text-white mt-auto" for="description">Description</label>
                            <input
                                id="description"
                                v-model="form.description"
                                :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                                class="input input-bordered w-full mt-2 bg-gray-600 text-white h-24"
                                placeholder="Enter server description"
                                type="text"
                            />
                        </div>
                    </div>
                    <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>
                </div>

                <!-- Other Settings Section -->
                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xl text-white">Allow Attachments</span>
                            <p class="text-sm text-gray-300">Let's you send images and videos in chat.</p>
                        </div>
                        <input class="toggle toggle-primary" disabled type="checkbox"/>
                    </div>
                </div>
                <ConfirmDialog
                    :class-name="`btn hover:btn-error mt-10 ${!perms.has(PermType.CAN_DELETE_SERVER) ? 'btn-disabled' : ''}`"
                    :confirm="deleteServer"
                    description="Are you sure you want to delete this server?"
                    text="Delete Server"
                    title="Delete server"
                />
            </div>
        </div>
    </div>
</template>
