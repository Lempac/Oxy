<script lang="ts" setup>
import { FaPlus } from "vue-icons-plus/fa";
import { home, logout } from '@/routes';
import { server as serverRoute } from '@/routes/home';
import { edit } from '@/routes/profile';
import { create } from '@/routes/server';
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {computed, ref} from 'vue';
import {baseUrl, defaultIcon, joinServer} from "@/bootstrap";
import axios from "axios";
import {Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";


const isHomePage = computed(() => usePage().component !== 'Profile/Edit');

defineProps<{
    servers?: Server[]
}>();


const serverModal = ref<HTMLDialogElement>();
const activeTab = ref<'create' | 'join'>('create');
const val = ref<[number, string?]>();
const code = ref<HTMLInputElement>();

const form = useForm<{ name: string, description: string, icon: File | null }>({
    name: '',
    description: '',
    icon: null
});

const loading = ref(false);
const createServer = async () => {
    if (loading.value) return;
    loading.value = true;
    axios.postForm(create.url(), form.data())
        .then(() => {
            serverModal.value?.close();
            router.reload({only: ['servers']});
            form.reset();
        })
        .catch((err) => {
            for (const [name, errors] of (Object.entries(err.response.data.errors) as [name: string, errors: string[]][])) {
                errors.forEach(error => form.setError(name as "description" | "icon" | "name", error))
            }
            console.error('Error creating server:', err);
        })
        .finally(() => {
            loading.value = false;
        });
};

const icon = ref<string | null>(null);
const inputFile = ref<File | null>();

const updateIcon = (val: File) => {
    inputFile.value = val;
    form.icon = inputFile.value;
    icon.value = URL.createObjectURL(inputFile.value);
}

</script>


<template>
    <div class="navbar bg-base-100 border-b border-base-300">
        <div class="navbar-start">
            <Link :href="home.url()">
                <ApplicationLogo class="block h-auto w-auto fill-current ml-5"/>
            </Link>
        </div>
        <div
            class="navbar-center w-3/5 overflow-x-auto overflow-y-hidden whitespace-nowrap flex justify-center items-center">
            <div v-for="server in servers" :key="server.id">
                <div class="hidden space-x-5 sm:-my-px sm:m-3 sm:flex">
                    <Link :href="serverRoute.url(server.id)">
                        <div :data-tip="server.name" class="tooltip tooltip-bottom">
                            <div class="btn btn-ghost btn-circle avatar">
                                <div class="w-14 rounded-full">
                                    <img
                                        :src="server.icon ? `${baseUrl}${server.icon}` : defaultIcon"
                                        alt="Server"/>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <button v-if="isHomePage" class="btn btn-circle ml-2 mb-1" @click="serverModal?.showModal">
                <FaPlus size="36"/>
            </button>
        </div>

        <!-- User Profile -->
        <div class="navbar-end gap-2">
            <!--            <label class="btn btn-circle swap swap-rotate">-->
            <!--                <input type="checkbox" />-->
            <!--                <FaPlus size="36" class="swap-on fill-current"></FaPlus>-->
            <!--                <FaMoon size="36" class="swap-off fill-current"></FaMoon>-->
            <!--            </label>-->

            <div class="dropdown dropdown-end">
                <div class="flex items-center btn btn-ghost" role="button" tabindex="0">
                    <div class="mr-2">{{ $page.props.user?.name }}</div>
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img
                                :src="$page.props.user?.icon ? `${baseUrl}${$page.props.user?.icon}` : defaultIcon"
                                alt="User Avatar"/>
                        </div>
                    </div>
                </div>
                <ul class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow" tabindex="0">
                    <li>
                        <Link :href="edit.url()">Profile</Link>
                    </li>
                    <li>
                        <Link :href="logout.url()" as="button" method="post">Log Out</Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal with 2 tabs -->
    <dialog ref="serverModal" class="modal">
        <div class="modal-box">
            <!-- Create Server-->
            <div class="tabs flex justify-center">
                <button
                    :class="{
                            'tab-active border-b-2 border-blue-500': activeTab === 'create',
                            'tab-bordered': activeTab !== 'create'
                        }"
                    class="tab px-3 py-1 mr-9 text-lg"
                    @click="activeTab = 'create'">Create Server
                </button>
                <button
                    :class="{
                            'tab-active border-b-2 border-blue-500': activeTab === 'join',
                            'tab-bordered': activeTab !== 'join'
                        }"
                    class="tab px-3 py-1 ml-9 text-lg"
                    @click="activeTab = 'join'">Join Server
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="py-4">
                <!-- Create Server Tab Content -->
                <div v-if="activeTab === 'create'">
                    <!-- Create Server Form -->
                    <form @submit.prevent="createServer">
                        <div class="form-control flex flex-row items-center gap-4">
                            <label
                                class="cursor-pointer rounded-full bg-base-200 transition-all duration-300 ease-in-out hover:bg-transparent"
                                for="serverIcon">
                                <img v-if="icon !== null" :src="icon" alt="" class="size-16 rounded-full"/>
                                <FaPlus v-else size="79"/>
                            </label>
                            <label class="cursor-pointer" for="serverIcon">Upload server icon</label>
                            <input
                                id="serverIcon"
                                ref="inputFile"
                                accept="image/png, image/jpeg"
                                class="hidden"
                                type="file"
                                @input="updateIcon((<HTMLInputElement>$event.target).files![0])"
                            />
                        </div>
                        <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Server Name</span>
                            </label>
                            <input
                                v-model="form.name" class="input input-bordered" placeholder="Enter server name"
                                type="text"/>
                            <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                        </div>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Description (Optional)</span>
                            </label>
                            <input
                                v-model="form.description" class="input input-bordered"
                                placeholder="Enter server description"
                                type="text"/>
                        </div>
                        <div class="modal-action">
                            <button class="btn btn-primary w-full mt-2" type="submit">Create Server</button>
                        </div>
                    </form>

                </div>

                <!-- Join Server Tab Content -->
                <div v-if="activeTab === 'join'">
                    <div class="form-control mb-4">
                        <label class="label" for="code">
                            <span class="label-text">Server Invite Code</span>
                        </label>
                        <input
                            id="code" ref="code" class="input input-bordered" name="code"
                            placeholder="Enter invite code" type="text"/>
                    </div>
                    <button
                        class="btn btn-secondary w-full"
                        @click="async () => {val = await joinServer(code!.value); val[0] === 200 ? serverModal?.close() : ''; form.reset();}">
                        Join Server
                    </button>
                    <ErrorAlert v-if="val && val[0] !== 200" :message="val[1]" class="mt-3"/>
                </div>
                <!-- Close Button -->
                <div class="modal-action">
                    <button
                        class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                        @click="() => serverModal?.close()">✕
                    </button>
                </div>
            </div>
        </div>
    </dialog>
</template>
