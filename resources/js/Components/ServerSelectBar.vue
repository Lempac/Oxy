<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { ref, Transition } from 'vue';

const isModalOpen = ref(false);
const activeTab = ref<'create' | 'join'>('create'); // Explicitly type activeTab

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const setActiveTab = (tab: 'create' | 'join') => {
    activeTab.value = tab;
};

const form = useForm({
    name: '',
    description: '', 
    icon: null 
});

const createServer = () => {
    form.post(route('servers.create'), {
        onSuccess: () => {
            closeModal();
        }
    });
};
</script>


<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <div class="navbar-start">
            <Link :href="route('home')">
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"/>
            </Link>
        </div>
        <div v-for="server in $page.props.servers" :key="server.id" class="navbar-center">
            <div class="hidden space-x-5 sm:-my-px sm:m-3 sm:flex">
                <Link :href="`/home/${server.id}`">
                    <div class="tooltip tooltip-bottom" :data-tip="server.name">
                        <div class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full">
                                <img alt="Server"
                                     src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s"/>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
        <div class="navbar-center">
            <button class="ml-3 btn btn-circle btn-primary" @click="openModal">
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
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s"/>
                        </div>
                    </div>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                    <li>
                        <Link :href="route('profile.edit')">Profile</Link>
                    </li>
                    <li>
                        <Link :href="route('logout')" method="post">Log Out</Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal with 2 tabs -->
    <Transition leave-active-class="transition-opacity ease-out duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isModalOpen" class="modal modal-open">
            <div class="modal-box">                
                <!-- Tabs Navigation with active tab underline -->
                <div class="tabs flex justify-center">
                    <button
                        @click="setActiveTab('create')"
                        :class="{
                            'tab-active border-b-2 border-blue-500': activeTab === 'create',
                            'tab-bordered': activeTab !== 'create' 
                        }"
                        class="tab px-3 py-2 mr-9 text-lg">Create Server</button>
                    <button
                        @click="setActiveTab('join')"
                        :class="{
                            'tab-active border-b-2 border-blue-500': activeTab === 'join',
                            'tab-bordered': activeTab !== 'join'
                        }"
                        class="tab px-3 py-2 ml-9 text-lg">Join Server</button>
                </div>
                
                <!-- Tab Contents -->
                <div class="py-4">
                    <!-- Create Server Tab Content -->
                    <div v-if="activeTab === 'create'">
                        <!-- Create Server Form -->
                        <form @submit.prevent="createServer">
                            <div class="form-control">
                            <button class="ml-3 btn btn-circle size-24">
                            <span class="text-2xl">+</span>
                            </button>
                            <p>Upload server icon</p>
                            <input v-model="form.icon" type="text" placeholder="Leave empty for default"/>
                            </div>
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text">Server Name</span>
                                </label>
                                <input v-model="form.name" type="text" placeholder="Enter server name" class="input input-bordered"/>
                            </div>
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text">Description (Optional)</span>
                                </label>
                                <input v-model="form.description" type="text" placeholder="Enter server description" class="input input-bordered"/>
                            </div>
                            <button type="submit" class="btn btn-primary w-full mt-2">Create Server</button>
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
                </div>

                <!-- Close Button -->
                <div class="modal-action">
                    <button @click="closeModal" class="btn">Close</button>
                </div>
            </div>
        </div>
    </Transition>
</template>
