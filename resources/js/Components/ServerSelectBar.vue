<script setup lang="ts">
import {Link, useForm, usePage} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {computed, ref} from 'vue';
import {defaultIcon} from "@/bootstrap";

const serverModal = ref<HTMLDialogElement>();
const activeTab = ref<'create' | 'join'>('create');
const form = useForm<{name: string, description: string, icon: File | null}>({
    name: '',
    description: '',
    icon: null
});

const createServer = () => {
    form.post(route('server.create'), {
        onSuccess: () => {
            serverModal.value?.close();
        }
    });
};
const baseUrl = window.location.origin;
const url = computed(() => form.icon ? URL.createObjectURL(form.icon) : "");

</script>


<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <div class="navbar-start">
            <Link :href="route('home')">
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"/>
            </Link>
        </div>
        <div class="navbar-center">
            <div v-for="server in $page.props.servers" :key="server.id">
                <div class="hidden space-x-5 sm:-my-px sm:m-3 sm:flex">
                    <Link :href="`/home/${server.id}`">
                        <div class="tooltip tooltip-bottom" :data-tip="server.name">
                            <div class="btn btn-ghost btn-circle avatar">
                                <div class="w-14 rounded-full">
                                    <img alt="Server"
                                         :src="server.icon ? `${baseUrl}${server.icon}` : defaultIcon"/>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
            <button class="ml-3 btn btn-circle btn-primary" @click="serverModal?.showModal">
                <span class="text-2xl">+</span>
            </button>
        </div>

        <!-- User Profile -->
        <div class="navbar-end gap-2">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="flex items-center btn btn-ghost">
                    <div class="mr-2">{{ $page.props.auth.user.name }}</div>
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img
                                alt="User Avatar"
                                :src="$page.props.auth.user.icon ? `${baseUrl}${$page.props.auth.user.icon}` : defaultIcon" />
                        </div>
                    </div>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                    <li>
                        <Link :href="route('profile.edit')">Profile</Link>
                    </li>
                    <li>
                        <Link :href="route('logout')" method="post" as="button">Log Out</Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal with 2 tabs -->
    <dialog ref="serverModal" class="modal">
        <div class="modal-box">
            <!-- Tabs Navigation with active tab underline -->
            <div class="tabs flex justify-center">
                <button
                    @click="activeTab = 'create'"
                    :class="{
                            'tab-active border-b-2 border-blue-500': activeTab === 'create',
                            'tab-bordered': activeTab !== 'create'
                        }"
                    class="tab px-3 py-2 mr-9 text-lg">Create Server
                </button>
                <button
                    @click="activeTab = 'join'"
                    :class="{
                            'tab-active border-b-2 border-blue-500': activeTab === 'join',
                            'tab-bordered': activeTab !== 'join'
                        }"
                    class="tab px-3 py-2 ml-9 text-lg">Join Server
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="py-4">
                <!-- Create Server Tab Content -->
                <div v-if="activeTab === 'create'">
                    <!-- Create Server Form -->
                    <form @submit.prevent="createServer">
                        <div class="form-control">
                            <label class="text-gray-600 dark:text-gray-400 flex flex-row items-center gap-4 cursor-pointer" for="serverIcon">

                        <img
                            v-if="url"  
                            class="size-24 rounded-full bg-gray-200 dark:bg-gray-600 transition-all duration-300 ease-in-out hover:bg-transparent"
                            :src="url"
                            alt="Server Icon"
                        />
                        
                        <!-- Placeholder when no image is uploaded -->
                        <div v-else class="size-24 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-gray-400">+</span> <!-- Placeholder content -->
                        </div>

                        <!-- Label text for the file input -->
                        <span>Upload server icon</span>
                    </label>

                    <!-- File input -->
                    <input
                        id="serverIcon"
                        type="file"
                        class="hidden"
                        accept="image/png, image/jpeg"
                        @input="form.icon = (<HTMLInputElement>$event.target).files![0]"
                    />

                        </div>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Server Name</span>
                            </label>
                            <input v-model="form.name" type="text" placeholder="Enter server name"
                                   class="input input-bordered"/>
                        </div>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Description (Optional)</span>
                            </label>
                            <input v-model="form.description" type="text" placeholder="Enter server description"
                                   class="input input-bordered"/>
                        </div>
                        <div class="modal-action">
                            <button type="submit" class="btn btn-primary w-full mt-2">Create Server</button>
                        </div>
                    </form>

                </div>

                <!-- Join Server Tab Content -->
                <div v-if="activeTab === 'join'">
                    <form>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Server Invite Code</span>
                            </label>
                            <input type="text" placeholder="Enter invite code" class="input input-bordered"/>
                        </div>
                        <button type="submit" class="btn btn-secondary w-full">Join Server</button>
                    </form>
                </div>
                <!-- Close Button -->
                <div class="modal-action">
                    <button @click="() => serverModal?.close()" class="btn">Close</button>
                </div>
            </div>
        </div>
    </dialog>
</template>
