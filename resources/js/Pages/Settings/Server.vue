<script setup lang="ts">
import {defineProps, ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {Link, router, usePage} from '@inertiajs/vue3';
import {useForm} from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {baseUrl, bigIntToPerms} from "@/bootstrap";
import SettingsHeader from "@/Components/SettingsHeader.vue";
import {Perms, PermType, Role, Server} from "@/types";

const {selected_server} = defineProps<{
    selected_server: Server,
}>();

const icon = ref<string | null>(selected_server?.icon ? baseUrl + selected_server?.icon : null);
const inputFile = ref<File | null>(null);
const perms = ref<Perms>(bigIntToPerms(BigInt(0)));

const form = useForm({
    name: selected_server?.name,
    description: selected_server?.description,
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
    form.post(route('server.update', { id: selected_server?.id }), {
        onSuccess: () => {
            router.reload(); // This will reload the current Inertia page without a full page reload
        },
    });
}

function deleteServer() {
    router.delete(route('server.destroy', {id: selected_server?.id}), {
        onSuccess: () => {
            router.visit('/home');
        },
    });
}

if (selected_server && selected_server.roles !== null){
    perms.value = bigIntToPerms(selected_server.roles.filter(role => usePage().props.user?.roles?.some(roleobj => roleobj.id === role.id)).reduce((acc: bigint, curr: Role) => acc | BigInt(curr.perms), BigInt(0)));
}

</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- navbar -->
            <SettingsHeader :selected_server/>

            <div class="flex justify-end mb-6 space-x-4">
                <button :disabled="!perms.has(PermType.CAN_EDIT_SERVER)" @click="handleSave" :class="`btn ${form.isDirty ? 'btn-neutral' : ''} px-6`">
                    Save Changes
                </button>
                <Link :href="route('home.server', { server: selected_server?.id })" class="btn btn-neutral">
                    Cancel
                </Link>
            </div>

            <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-white mb-6">Server Settings</h1>
                <!-- Server info -->
                <div class="bg-gray-800 p-6 rounded-lg mb-8">
                    <div class="flex items-center">
                        <label for="serverIcon" class="relative cursor-pointer has-[:disabled]:cursor-not-allowed">
                            <input
                                id="serverIcon"
                                type="file"
                                class="hidden peer"
                                accept="image/png, image/jpeg"
                                :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                                @change="updateIcon((<HTMLInputElement>$event.target).files![0])"
                            />
                            <div
                                class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-600 flex justify-center items-center transition-all duration-300 ease-in-out peer-enabled:hover:bg-transparent peer-disabled:input-disabled">
                                <img v-if="icon" :src="icon" class="w-full h-full rounded-full object-cover"
                                     alt="Server Icon"/>
                                <span v-else class="text-4xl text-gray-500">+</span>
                            </div>
                        </label>

                        <div class="w-full ml-[15%]">
                            <label for="serverName" class="text-white">Server Name</label>
                            <input
                                :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                                type="text"
                                id="serverName"
                                class="input input-bordered w-full mt-2 bg-gray-600 text-white"
                                v-model="form.name"
                                placeholder="Enter your server name"
                            />
                            <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2" />
                            <label for="description" class="text-white mt-auto">Description</label>
                            <input
                                :disabled="!perms.has(PermType.CAN_EDIT_SERVER)"
                                type="text"
                                id="description"
                                class="input input-bordered w-full mt-2 bg-gray-600 text-white h-24"
                                v-model="form.description"
                                placeholder="Enter server description"
                            />
                        </div>

                    </div>
                </div>

                <!-- Other Settings Section -->
                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xl text-white">Allow Attachments</span>
                            <p class="text-sm text-gray-300">Let's you send images and videos in chat.</p>
                        </div>
                        <input type="checkbox" class="toggle toggle-primary" disabled/>
                    </div>
                </div>
                <ConfirmDialog
                    title="Delete server"
                    description="Are you sure you want to delete this server?"
                    :confirm="deleteServer"
                    text="Delete Server"
                    :class-name="`btn hover:btn-error mt-10 ${!perms.has(PermType.CAN_DELETE_SERVER) ? 'btn-disabled' : ''}`"
                />
            </div>
        </div>
    </div>
</template>
