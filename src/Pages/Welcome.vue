<script lang="ts" setup>
import { home, login, manual, register } from '@/routes';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdMessage, MdCall, MdScreenShare } from 'vue-icons-plus/md';
import { FaBook } from 'vue-icons-plus/fa';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ImageEditorModal from "@/Components/ImageEditorModal.vue";

const loginModel = ref<HTMLDialogElement>();
const registerModel = ref<HTMLDialogElement>();

const isEditorOpen = ref(false);
const editorImageSource = ref<File | null>(null);
const isDraggingOver = ref(false);

const onDropRegisterIcon = (e: DragEvent) => {
    isDraggingOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        onRegisterIconSelected(file);
    }
};

const loginForm = useForm({
    nickname: '',
    password: '',
    remember: false,
});

const iconPreview = ref<string | null>(null);
const serverInfo = ref<{ name: string; description: string; icon: string; members_count: number; online_count: number } | null>(null);

const registerForm = useForm<{
    server_code: string;
    nickname: string;
    password: string;
    password_confirmation: string;
    icon: File | null;
}>({
    server_code: '',
    nickname: '',
    password: '',
    password_confirmation: '',
    icon: null,
});

const checkServerCode = async () => {
    const code = registerForm.server_code.trim();
    if (!code) {
        serverInfo.value = null;
        return;
    }
    try {
        const res = await fetch(`/invites/${encodeURIComponent(code)}/check`);
        const data = await res.json();
        if (res.ok && data.valid) {
            serverInfo.value = data.server;
            registerForm.clearErrors('server_code');
        } else {
            serverInfo.value = null;
            registerForm.setError('server_code', data.message || 'The provided server code is invalid or has expired.');
        }
    } catch {
        serverInfo.value = null;
    }
};

const onRegisterIconSelected = (file: File | undefined) => {
    if (file) {
        editorImageSource.value = file;
        isEditorOpen.value = true;
    }
};

const handleEditorSave = (editedFile: File) => {
    registerForm.icon = editedFile;
    iconPreview.value = URL.createObjectURL(editedFile);
};

const submitLogin = () => {
    loginForm.post(login.url(), {
        onFinish: () => {
            loginForm.reset('password');
        },
    });
};

const submitRegister = () => {
    registerForm.post(register.url(), {
        onError: (errors) => {
            if (errors.password || errors.password_confirmation) {
                registerForm.reset('password', 'password_confirmation');
            }
        }
    });
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const invite = params.get('invite') || params.get('code');
        if (invite) {
            registerForm.server_code = invite;
            checkServerCode();
            registerModel.value?.showModal();
        }
    }
});

</script>

<template>
    <Head title="Welcome"/>
    <body class="bg-base-100 text-base-content min-h-screen">
    <div class="flex flex-col min-h-screen p-6">
        <header>
            <div class="navbar bg-base-200 rounded-box border border-base-300">
                <div class="navbar-start ml-5">
                    <img alt="" class="block h-16 w-auto" src="/images/oxy.png"/>
                </div>
                <ApplicationLogo class="navbar-center mb-1.5"/>
                <div class="navbar-end mr-5">
                    <Link v-if="$page.props.user" :href="home.url()" class="btn btn-lg btn-primary">
                        Home
                    </Link>
                    <template v-else>
                        <button
                            class="btn btn-lg btn-primary"
                            @click="() => {loginForm.clearErrors(); loginModel?.showModal()}">
                            Join
                        </button>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1 flex flex-col items-center justify-center">
            <!-- Info cards -->
            <div class="flex justify-center items-center space-x-8">
                <div class="card bg-base-200 text-base-content p-6 w-80 text-center border border-base-300">
                    <MdMessage class="w-16 h-16 mx-auto mb-4" />
                    <h2 class="text-2xl font-bold">Messaging</h2>
                    <p class="mt-2">Send instant messages to friends and teams. Enjoy real-time conversations with rich text, emojis, and file sharing across channels and direct messages.</p>
                </div>

                <div class="card bg-base-200 text-base-content p-6 w-80 text-center border border-base-300">
                    <MdCall class="w-16 h-16 mx-auto mb-4" />
                    <h2 class="text-2xl font-bold">Voice Calls</h2>
                    <p class="mt-2">Jump into crystal-clear voice calls anytime. Whether it's a quick one-on-one chat or a group discussion, stay connected with high-quality audio.</p>
                </div>

                <div class="card bg-base-200 text-base-content p-6 w-80 text-center border border-base-300">
                    <MdScreenShare class="w-16 h-16 mx-auto mb-4" />
                    <h2 class="text-2xl font-bold">Screen Share</h2>
                    <p class="mt-2">Share your screen effortlessly during calls. Present your work, collaborate in real time, or troubleshoot together with seamless screen sharing.</p>
                </div>
            </div>

            <div class="card mt-10 border border-base-300 bg-base-200 mx-auto w-full max-w-lg">
                <h2 class="card-title text-base-content justify-center mt-5">What are you waiting for?</h2>
                <div class="flex flex-col items-center gap-3 p-5">
                    <p class="text-base-content text-xl">
                        Join now!!!
                    </p>
                    <Link v-if="$page.props.user" :href="home.url()" class="btn btn-lg btn-primary">
                        Home
                    </Link>
                    <template v-else>
                        <button
                            class="btn btn-lg btn-primary"
                            @click="() => {loginForm.clearErrors(); loginModel?.showModal()}">
                            Join
                        </button>
                    </template>
                </div>
            </div>
        </main>

        <footer class="footer footer-center mt-auto py-4 text-base-content">
            <div class="rounded-full p-4">
                © {{ new Date().getFullYear() }} Oxy
            </div>
            <Link
                :href="manual.url()" class="left-2 mt-3 absolute btn btn-ghost tooltip tooltip-right"
                data-tip="FAQ">
                <button class="flex items-center justify-center h-10 w-5">
                    <FaBook class="w-8 h-8" />
                </button>
            </Link>
        </footer>
    </div>
    <dialog ref="loginModel" class="modal">
        <form class="modal-box bg-base-200 space-y-4 text-base-content" @submit.prevent="submitLogin">
            <h2 class="text-2xl font-bold border-b border-base-300 pb-2">Log in</h2>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nickname</legend>
                <input
                    id="login-nickname"
                    v-model="loginForm.nickname"
                    autocomplete="username"
                    class="input input-bordered w-full"
                    name="nickname"
                    required
                    type="text"
                />
                <ErrorAlert v-if="loginForm.errors.nickname" :message="loginForm.errors.nickname" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Password</legend>
                <input
                    id="login-password"
                    v-model="loginForm.password"
                    autocomplete="current-password"
                    class="input input-bordered w-full"
                    name="password"
                    required
                    type="password"
                />
                <ErrorAlert v-if="loginForm.errors.password" :message="loginForm.errors.password" />
            </fieldset>
            <fieldset class="fieldset p-0">
                <label class="fieldset-label cursor-pointer flex-row gap-3">
                    <input id="remember" v-model="loginForm.remember" autocomplete="off" class="checkbox" data-bwignore="true" name="remember" type="checkbox"/>
                    Remember me
                </label>
            </fieldset>
            <div class="modal-action mt-6 gap-2">
                <button class="btn btn-primary px-8" type="submit" :disabled="loginForm.processing">
                    Log in
                </button>
                <button class="btn btn-ghost" type="button" @click="() => loginModel?.close()">Cancel</button>
            </div>
            <div class="text-center mt-2">
                <button class="btn btn-link btn-sm" type="button" @click="() => {loginModel?.close(); registerForm.clearErrors(); registerModel?.showModal();}">
                    First time here? Enter Server Code to Join
                </button>
            </div>
        </form>
    </dialog>

    <dialog ref="registerModel" class="modal">
        <form class="modal-box bg-base-200 space-y-4 text-base-content" @submit.prevent="submitRegister">
            <h2 class="text-2xl font-bold border-b border-base-300 pb-2">Join with Server Code</h2>

            <!-- Server Code Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Server Code / Invite Code</legend>
                <input
                    id="register-server_code"
                    v-model="registerForm.server_code"
                    class="input input-bordered w-full"
                    name="server_code"
                    placeholder="Enter invite code"
                    required
                    type="text"
                    @blur="checkServerCode"
                />
                <div v-if="serverInfo" class="mt-2 p-3 bg-success/10 border border-success/30 rounded-lg flex items-center gap-3">
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
                <ErrorAlert v-if="registerForm.errors.server_code" :message="registerForm.errors.server_code" />
            </fieldset>

            <!-- Nickname Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nickname</legend>
                <input
                    id="register-nickname"
                    v-model="registerForm.nickname"
                    autocomplete="username"
                    class="input input-bordered w-full"
                    name="nickname"
                    required
                    type="text"
                />
                <ErrorAlert v-if="registerForm.errors.nickname" :message="registerForm.errors.nickname" />
            </fieldset>

            <!-- Profile Icon Input -->
            <fieldset
                class="fieldset transition-all"
                :class="{'ring-2 ring-primary ring-offset-2 rounded-xl p-1': isDraggingOver}"
                @dragover.prevent="isDraggingOver = true"
                @dragleave.prevent="isDraggingOver = false"
                @drop.prevent="onDropRegisterIcon"
            >
                <legend class="fieldset-legend">Profile Icon (Optional - Click or Drag Image)</legend>
                <div class="flex items-center gap-4">
                    <img v-if="iconPreview" :src="iconPreview" alt="Preview" class="size-12 rounded-full object-cover"/>
                    <input
                        id="register-icon"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        class="file-input file-input-bordered w-full"
                        name="icon"
                        type="file"
                        @change="onRegisterIconSelected((<HTMLInputElement>$event.target).files?.[0])"
                    />
                </div>
                <ErrorAlert v-if="registerForm.errors.icon" :message="registerForm.errors.icon" />
            </fieldset>

            <ImageEditorModal
                v-model="isEditorOpen"
                :image-source="editorImageSource"
                title="Edit Avatar"
                :aspect-ratio-lock="1"
                :circle-mask="true"
                @save="handleEditorSave"
            />

            <!-- Password Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Password</legend>
                <input
                    id="register-password"
                    v-model="registerForm.password"
                    autocomplete="new-password"
                    class="input input-bordered w-full"
                    name="password"
                    required
                    type="password"
                />
                <ErrorAlert v-if="registerForm.errors.password" :message="registerForm.errors.password" />
            </fieldset>

            <!-- Password Confirmation Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Confirm Password</legend>
                <input
                    id="register-password_confirmation"
                    v-model="registerForm.password_confirmation"
                    autocomplete="new-password"
                    class="input input-bordered w-full"
                    name="password_confirmation"
                    required
                    type="password"
                />
                <ErrorAlert v-if="registerForm.errors.password_confirmation" :message="registerForm.errors.password_confirmation" />
            </fieldset>

            <!-- Submit and Cancel Buttons -->
            <div class="modal-action mt-6 gap-2">
                <button
                    :disabled="registerForm.processing" class="btn btn-primary px-8"
                    type="submit">
                    Join & Register
                </button>
                <button class="btn btn-ghost" type="button" @click="() => registerModel?.close()">
                    Cancel
                </button>
            </div>
            <div class="text-center mt-2">
                <button class="btn btn-link btn-sm" type="button" @click="() => {registerModel?.close(); loginForm.clearErrors(); loginModel?.showModal();}">
                    Already have an account? Log in
                </button>
            </div>
        </form>
    </dialog>
    </body>
</template>

<style scoped>

body {
    animation: fadeIn ease-in-out 2s;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

</style>
