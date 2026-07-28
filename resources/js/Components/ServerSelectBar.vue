<script lang="ts" setup>
import {logout} from '@/routes';
import {text} from '@/routes/home';
import {edit} from '@/routes/profile';
import {create, leave} from '@/routes/server';
import {server as settingsServer} from '@/routes/settings';
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {computed, ref} from 'vue';
import {baseUrl, defaultIcon, joinServer, usePerms} from "@/bootstrap";
import {PermType, Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {GoPlus} from 'vue-icons-plus/go';
import {BsDoorOpen, BsGearFill} from 'vue-icons-plus/bs';

const perms = usePerms();
const isHomePage = computed(() => usePage().component !== 'Profile/Edit');

const {servers, selectedServer} = defineProps<{
    servers?: Server[];
    selectedServer?: Server;
}>();


const serverModal = ref<HTMLDialogElement>();
const activeTab = ref<'create' | 'join'>('create');
const val = ref<[number, string?]>();
const joinCodeInput = ref('');
const serverInfo = ref<{ name: string; description: string; icon: string; members_count: number; online_count: number } | null>(null);
const checkLoading = ref(false);
const checkError = ref<string | null>(null);

let checkDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const checkServerCode = () => {
    if (checkDebounceTimer) clearTimeout(checkDebounceTimer);
    checkDebounceTimer = setTimeout(async () => {
        const codeToTest = joinCodeInput.value.trim();
        if (!codeToTest) {
            serverInfo.value = null;
            checkError.value = null;
            return;
        }
        checkLoading.value = true;
        try {
            const res = await fetch(`/invites/${encodeURIComponent(codeToTest)}/check`);
            const data = await res.json();
            if (res.ok && data.valid) {
                serverInfo.value = data.server;
                checkError.value = null;
            } else {
                serverInfo.value = null;
                checkError.value = data.message || 'Invalid or expired server code.';
            }
        } catch {
            serverInfo.value = null;
            checkError.value = 'Failed to verify invite code.';
        } finally {
            checkLoading.value = false;
        }
    }, 300);
};

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
        <div class="navbar-start w-auto">
            <Link href="/">
                <ApplicationLogo class="block h-10 w-auto fill-current ml-5"/>
            </Link>
        </div>
        <div class="navbar-center flex-1 overflow-x-auto overflow-y-hidden px-4 scrollbar-hide">
            <div class="flex items-center gap-3 min-w-max h-full w-full">
                <!-- Empty spacer to help center items if they don't overflow -->
                <div class="grow"></div>

                <div v-for="server in servers" :key="server.id" class="shrink-0">
                    <Link :href="text.url(server.route_key)">
                        <div :data-tip="server.name" class="tooltip tooltip-bottom">
                            <div
:class="{'ring ring-primary ring-offset-base-100 ring-offset-2': selectedServer?.id === server.id}"
                                 class="btn btn-ghost btn-circle avatar">
                                <div class="w-10 rounded-full">
                                    <img
                                        :src="server.icon ? `${baseUrl}${server.icon}` : defaultIcon"
                                        alt="Server"/>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <button v-if="isHomePage" class="btn btn-circle btn-sm shrink-0" @click="serverModal?.showModal">
                    <GoPlus scale="1.5"/>
                </button>

                <!-- Empty spacer to help center items if they don't overflow -->
                <div class="grow"></div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="navbar-end gap-2 pr-4 w-auto">
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
                    <div class="mr-2 hidden md:block">{{ $page.props.user?.nickname }}</div>
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img
                                :src="$page.props.user?.icon ? `${baseUrl}${$page.props.user?.icon}` : defaultIcon"
                                alt="User Avatar"/>
                        </div>
                    </div>
                </div>
                <ul class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow" tabindex="0">
                    <li>
                        <Link :href="edit.url()">Profile</Link>
                    </li>
                    <li v-if="selectedServer">
                        <ConfirmDialog
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
                <div class="tabs tabs-bordered flex justify-center mb-4" role="tablist">
                    <button
                        :class="{'tab-active': activeTab === 'create'}"
                        class="tab text-lg h-10 w-1/2"
                        role="tab"
                        @click="activeTab = 'create'">Create Server
                    </button>
                    <button
                        :class="{'tab-active': activeTab === 'join'}"
                        class="tab text-lg h-10 w-1/2"
                        role="tab"
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
                                    <img
v-if="icon !== null" :src="icon" alt=""
                                         class="size-16 rounded-full object-cover"/>
                                    <GoPlus v-else scale="2"/>
                                </label>
                                <label class="cursor-pointer font-medium" for="serverIcon">Upload server icon</label>
                                <input
                                    id="serverIcon"
                                    ref="inputFile"
                                    accept="image/png, image/jpeg"
                                    autocomplete="off"
                                    class="hidden"
                                    data-bwignore="true"
                                    type="file"
                                    @input="updateIcon((<HTMLInputElement>$event.target).files![0])"
                                />
                            </div>
                            <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>

                            <fieldset class="fieldset w-full">
                                <legend class="fieldset-legend">Server Name</legend>
                                <input
                                    v-model="form.name" autocomplete="off" class="input input-bordered w-full bg-base-100" data-bwignore="true"
                                    placeholder="Enter server name"
                                    type="text"/>
                                <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
                            </fieldset>

                            <fieldset class="fieldset w-full mt-4">
                                <legend class="fieldset-legend">Description (Optional)</legend>
                                <input
                                    v-model="form.description" autocomplete="off" class="input input-bordered w-full bg-base-100" data-bwignore="true"
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
                        <fieldset class="fieldset w-full mb-4">
                            <legend class="fieldset-legend">Server Invite Code</legend>
                            <input
                                id="join-code"
                                v-model="joinCodeInput"
                                autocomplete="off"
                                class="input input-bordered w-full bg-base-100"
                                data-bwignore="true"
                                name="code"
                                placeholder="Enter invite code"
                                type="text"
                                @input="checkServerCode"
                                @blur="checkServerCode"
                            />
                        </fieldset>

                        <!-- Server Preview Card -->
                        <div v-if="serverInfo" class="mb-4 p-3 bg-success/10 border border-success/30 rounded-lg flex items-center gap-3">
                            <img v-if="serverInfo.icon" :src="serverInfo.icon" class="size-10 rounded-full object-cover border border-base-300" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-success truncate">Joining: {{ serverInfo.name }}</p>
                                <p v-if="serverInfo.description" class="text-xs text-base-content/70 truncate">{{ serverInfo.description }}</p>
                                <div class="flex items-center gap-4 mt-1 text-xs text-base-content/80 font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-success"></span>
                                        {{ serverInfo.online_count ?? 0 }} Online
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-base-content/40"></span>
                                        {{ serverInfo.members_count ?? 0 }} Members
                                    </span>
                                </div>
                            </div>
                        </div>

                        <ErrorAlert v-if="checkError" :message="checkError" class="mb-4" />

                        <button
                            class="btn btn-primary w-full"
                            :disabled="!serverInfo || checkLoading"
                            @click="async () => { val = await joinServer(joinCodeInput); if (val[0] === 200) { serverModal?.close(); joinCodeInput = ''; serverInfo = null; } }">
                            <span v-if="checkLoading" class="loading loading-spinner loading-xs"></span>
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
            <form class="modal-backdrop" method="dialog">
                <button>close</button>
            </form>
        </dialog>
    </Teleport>
</template>
