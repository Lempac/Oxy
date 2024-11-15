<script setup lang="ts">
import {defineProps, ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {Link, router, usePage} from '@inertiajs/vue3';
import {useForm} from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {baseUrl} from "@/bootstrap";

const {selected_server} = usePage().props;

const icon = ref<string | null>(selected_server?.icon ? baseUrl + selected_server?.icon : null);
const inputFile = ref<File | null>(null);

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

const showModal = ref(false);

</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-6xl p-6">
            <!-- navbar -->
            <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
                <div class="flex-1">
                    <h1 class="text-2xl truncate max-w-[50%]" :title="selected_server?.name">
                        {{ selected_server?.name }}</h1>
                </div>
                <div class="flex space-x-6">
                    <Link :href="route('settings.server', { serverId: selected_server?.id })"
                          class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                        Server
                    </Link>
                    <Link :href="route('settings.role', { id: selected_server?.id })"
                          class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
                        Roles
                    </Link>
                </div>
            </div>

            <div class="flex justify-end mb-6 space-x-4">
                <button @click="handleSave" :class="`btn ${form.isDirty ? 'btn-neutral' : ''} px-6`">Save Changes
                </button>
                <Link :href="route('home.server', { server: selected_server?.id })" class="btn btn-neutral">Cancel
                </Link>

            </div>

            <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
                <h1 class="text-3xl text-white mb-6">Server Settings</h1>
                <!-- Server info -->
                <div class="bg-gray-800 p-6 rounded-lg mb-8">
                    <div class="flex items-center">
                        <label for="serverIcon" class="relative cursor-pointer">
                            <div
                                class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-600 flex justify-center items-center transition-all duration-300 ease-in-out hover:bg-transparent">
                                <img v-if="icon" :src="icon" class="w-full h-full rounded-full object-cover"
                                     alt="Server Icon"/>
                                <span v-else class="text-4xl text-gray-500">+</span>
                            </div>
                            <input
                                id="serverIcon"
                                type="file"
                                class="hidden"
                                accept="image/png, image/jpeg"
                                @change="updateIcon((<HTMLInputElement>$event.target).files![0])"
                            />
                        </label>

                        <div class="w-full ml-[15%]">
                            <label for="serverName" class="text-white">Server Name</label>
                            <input
                                type="text"
                                id="serverName"
                                class="input input-bordered w-full mt-2 bg-gray-600 text-white"
                                v-model="form.name"
                                placeholder="Enter your server name"
                            />
                            <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2" />
                            <label for="description" class="text-white mt-auto">Description</label>
                            <input
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
                <div class="bg-gray-800 p-6 rounded-lg mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xl text-white">Allow Attachments</span>
                            <p class="text-sm text-gray-300">Let's you send images and videos in chat.</p>
                        </div>
                        <input type="checkbox" class="toggle toggle-primary"/>
                    </div>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xl text-white">Other function</span>
                            <p class="text-sm text-gray-300">idk, add smth here...</p>
                        </div>
                        <input type="checkbox" class="toggle toggle-primary"/>
                    </div>
                </div>
                <ConfirmDialog
                    title="Delete Message"
                    description="Are you sure you want to delete this message?"
                    :confirm="deleteServer"
                    text="Delete Server"
                    class-name="btn btn-danger mt-10 bg-red-500 text-white"
                />
            </div>
        </div>
    </div>
</template>
