<script lang="ts" setup>
import { home, logout } from '@/routes';
import { server as serverRoute, text } from '@/routes/home';
import { edit } from '@/routes/profile';
import { create, leave } from '@/routes/server';
import { server as settingsServer } from '@/routes/settings';
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {computed, ref} from 'vue';
import {baseUrl, defaultIcon, joinServer, usePerms} from "@/bootstrap";
import {Server, PermType} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { GoPlus } from 'vue-icons-plus/go';
import { BsGearFill, BsDoorOpen } from 'vue-icons-plus/bs';

const perms = usePerms();
const isHomePage = computed(() => usePage().component !== 'Profile/Edit');

const { servers, selectedServer } = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
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
    form.post(create.url(), {
        onSuccess: () => {
            serverModal.value?.close();
            router.reload({only: ['servers', 'user']});
            form.reset();
        },
        onError: (errors) => {
            console.error('Error creating server:', errors);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

function leaveServer() {
    if (!selectedServer) return;
    router.delete(leave.url(selectedServer.route_key), {
        onSuccess: () => router.visit('/home')
    });
}

const icon = ref<string | null>(null);
const inputFile = ref<File | null>();

const updateIcon = (val: File) => {
    inputFile.value = val;
    form.icon = inputFile.value;
    icon.value = URL.createObjectURL(inputFile.value);
}

</script>

<template>
    <div class="navbar bg-base-100">
        <div class="navbar-start">
            <Link href="/">
                <ApplicationLogo class="block h-10 w-auto fill-current ml-5"/>
            </Link>
        </div>
        <div class="navbar-center flex-1 overflow-x-auto overflow-y-hidden whitespace-nowrap flex justify-center items-center px-4 scrollbar-hide">
            <div v-for="server in servers" :key="server.id">
                <div class="hidden space-x-5 sm:-my-px sm:m-3 sm:flex">
                    <Link :href="serverRoute.url(server.route_key)">
                        <div :data-tip="server.name" class="tooltip tooltip-bottom">
                            <div class="btn btn-ghost btn-circle avatar" :class="{'ring ring-primary ring-offset-base-100 ring-offset-2': selectedServer?.id === server.id}">
                                <div class="w-10 rounded-full">
                                    <img
                                        :src="server.icon ? `${baseUrl}${server.icon}` : defaultIcon"
                                        alt="Server"/>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <button v-if="isHomePage" class="btn btn-circle btn-sm ml-2 mb-1" @click="serverModal?.showModal">
                <GoPlus scale="1.5"/>
            </button>
        </div>

        <!-- Right Side -->
        <div class="navbar-end gap-2 pr-4">
            <!-- Server Settings -->
            <Link
                v-if="selectedServer && perms.hasAny([PermType.CAN_MANAGE_SERVER, PermType.CAN_MANAGE_ROLE, PermType.CAN_MANAGE_MEMBERS])"
                :href="settingsServer.url(selectedServer?.route_key)"
                class="btn btn-ghost btn-circle tooltip tooltip-left" data-tip="Server settings">
                <BsGearFill animation="spin-hover" scale="1.2"/>
            </Link>

            <!-- User Profile -->
            <div class="dropdown dropdown-end">
                <div class="flex items-center btn btn-ghost px-2" role="button" tabindex="0">
                    <div class="mr-2 hidden md:block">{{ $page.props.user?.name }}</div>
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
                    <li v-if="selectedServer">
                        <ConfirmDialog
                            id="leave-server"
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
                        <Link :href="logout.url()" as="button" method="post">Log Out</Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <dialog ref="serverModal" class="modal">
            <div class="modal-box bg-base-200">
                <!-- Create Server-->
                <div role="tablist" class="tabs tabs-bordered flex justify-center mb-4">
                    <button
                        role="tab"
                        :class="{'tab-active': activeTab === 'create'}"
                        class="tab text-lg h-10 w-1/2"
                        @click="activeTab = 'create'">Create Server
                    </button>
                    <button
                        role="tab"
                        :class="{'tab-active': activeTab === 'join'}"
                        class="tab text-lg h-10 w-1/2"
                        @click="activeTab = 'join'">Join Server
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="py-2">
                    <!-- Create Server Tab Content -->
                    <div v-if="activeTab === 'create'">
                        <!-- Create Server Form -->
                        <form @submit.prevent="createServer">
                            <div class="form-control flex flex-row items-center gap-4 mb-4">
                                <label
                                    class="cursor-pointer rounded-full bg-base-300 transition-all duration-300 ease-in-out hover:bg-base-100 flex items-center justify-center size-16 shadow-inner"
                                    for="serverIcon">
                                    <img v-if="icon !== null" :src="icon" alt="" class="size-16 rounded-full object-cover"/>
                                    <GoPlus v-else scale="2"/>
                                </label>
                                <label class="cursor-pointer font-medium" for="serverIcon">Upload server icon</label>
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
                            
                            <fieldset class="fieldset w-full">
                                <legend class="fieldset-legend">Server Name</legend>
                                <input
                                    v-model="form.name" class="input input-bordered w-full bg-base-100" placeholder="Enter server name"
                                    type="text"/>
                                <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                            </fieldset>
                            
                            <fieldset class="fieldset w-full mt-4">
                                <legend class="fieldset-legend">Description (Optional)</legend>
                                <input
                                    v-model="form.description" class="input input-bordered w-full bg-base-100"
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
                        <fieldset class="fieldset w-full mb-6">
                            <legend class="fieldset-legend">Server Invite Code</legend>
                            <input
                                id="code" ref="code" class="input input-bordered w-full bg-base-100" name="code"
                                placeholder="Enter invite code" type="text"/>
                        </fieldset>
                        <button
                            class="btn btn-primary w-full"
                            @click="async () => {val = await joinServer(code!.value); val[0] === 200 ? serverModal?.close() : ''; form.reset();}">
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
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </Teleport>
</template>
