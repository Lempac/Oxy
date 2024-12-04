<script setup lang="ts">
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {computed, onMounted, ref, watch} from 'vue';
import {baseUrl, defaultIcon, joinServer} from "@/bootstrap";
import axios from "axios";
import {addIcons} from "oh-vue-icons";
import {OiPlus, HiSolidSun, RiMoonClearFill} from "oh-vue-icons/icons";
import {Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";

addIcons(OiPlus, HiSolidSun, RiMoonClearFill);

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

let loading = ref(false);
const createServer = async () => {
    if (loading.value) return;
    loading.value = true;
    axios.postForm(route('server.create'), form.data())
        .then(() => {
            serverModal.value?.close();
            router.reload({only: ['servers']});
            form.reset();
        })
        .catch((err) => {
            for (const [name, errors] of (Object.entries(err.response.data.errors) as [name: string, errors: string[]][])) {
                errors.forEach((error: any) => form.setError(name as "description" | "icon" | "name", error))
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
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <div class="navbar-start">
            <Link :href="route('home')">
                <ApplicationLogo class="block h-auto w-auto fill-current ml-5"/>
            </Link>
        </div>
        <div class="navbar-center w-3/5 overflow-x-auto overflow-y-hidden whitespace-nowrap flex justify-center items-center">
            <div v-for="server in servers" :key="server.id">
                <div class="hidden space-x-5 sm:-my-px sm:m-3 sm:flex">
                    <Link :href="route('home.server', { id: server.id})">
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

            <button v-if="isHomePage" class="btn btn-circle ml-2 mb-1" @click="serverModal?.showModal">
                <v-icon name="oi-plus" scale="1.5"/>
            </button>
        </div>

        <!-- User Profile -->
        <div class="navbar-end gap-2">
            <!--            <label class="btn btn-circle swap swap-rotate">-->
            <!--                <input type="checkbox" />-->
            <!--                <v-icon name="hi-solid-sun" scale="1.5" class="swap-on fill-current"></v-icon>-->
            <!--                <v-icon name="ri-moon-clear-fill" scale="1.5" class="swap-off fill-current"></v-icon>-->
            <!--            </label>-->

            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="flex items-center btn btn-ghost">
                    <div class="mr-2">{{ $page.props.user?.name }}</div>
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img
                                alt="User Avatar"
                                :src="$page.props.user?.icon ? `${baseUrl}${$page.props.user?.icon}` : defaultIcon"/>
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
            <!-- Create Server-->
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
                        <div class="form-control flex flex-row items-center gap-4">
                            <label
                                class="cursor-pointer rounded-full bg-gray-200 dark:bg-gray-600 transition-all duration-300 ease-in-out hover:bg-transparent"
                                for="serverIcon">
                                <img v-if="icon !== null" :src="icon" class="size-16 rounded-full" alt=""/>
                                <v-icon v-else name="oi-plus" scale="3.333"/>
                            </label>
                            <label for="serverIcon" class="cursor-pointer">Upload server icon</label>
                            <input
                                ref="inputFile"
                                id="serverIcon"
                                type="file"
                                class="hidden"
                                accept="image/png, image/jpeg"
                                @input="updateIcon((<HTMLInputElement>$event.target).files![0])"
                            />
                        </div>
                        <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Server Name</span>
                            </label>
                            <input v-model="form.name" type="text" placeholder="Enter server name"
                                   class="input input-bordered"/>
                            <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
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
                    <div class="form-control mb-4">
                        <label class="label" for="code">
                            <span class="label-text">Server Invite Code</span>
                        </label>
                        <input ref="code" type="text" name="code" id="code"
                               placeholder="Enter invite code" class="input input-bordered"/>
                    </div>
                    <button class="btn btn-secondary w-full" @click="async () => {val = await joinServer(code!.value); val[0] === 200 ? serverModal?.close() : ''; form.reset();}">Join Server</button>
                    <ErrorAlert v-if="val && val[0] !== 200" :message="val[1]" class="mt-3"/>
                </div>
                <!-- Close Button -->
                <div class="modal-action">
                    <button @click="() => serverModal?.close()"
                            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕
                    </button>
                </div>
            </div>
        </div>
    </dialog>
</template>
