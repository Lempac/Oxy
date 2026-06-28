<script lang="ts" setup>
import { home, login, manual, register } from '@/routes';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdMessage, MdCall, MdScreenShare } from 'vue-icons-plus/md';
import { FaBook } from 'vue-icons-plus/fa';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const loginModel = ref<HTMLDialogElement>();
const registerModel = ref<HTMLDialogElement>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    remember: false,
});

const submitLogin = () => {
    form.post(login.url(), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
const submitRegister = () => {
    form.post(register.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        }
    });
};

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
                            @click="() => {form.clearErrors(); loginModel?.showModal()}">
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
                            @click="() => {form.clearErrors(); loginModel?.showModal()}">
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
                <legend class="fieldset-legend">Email</legend>
                <input
                    id="email"
                    v-model="form.email"
                    class="input input-bordered w-full"
                    required
                    type="email"
                />
                <ErrorAlert v-if="form.errors.email" :message="form.errors.email" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Password</legend>
                <input
                    id="password"
                    v-model="form.password"
                    class="input input-bordered w-full"
                    required
                    type="password"
                />
                <ErrorAlert v-if="form.errors.password" :message="form.errors.password" />
            </fieldset>
            <fieldset class="fieldset p-0">
                <label class="fieldset-label cursor-pointer flex-row gap-3">
                    <input id="remember" v-model="form.remember" class="checkbox" type="checkbox"/>
                    Remember me
                </label>
            </fieldset>
            <div class="modal-action mt-6 gap-2">
                <button class="btn btn-primary px-8" type="submit">
                    Log in
                </button>
                <button class="btn btn-ghost" type="button" @click="() => loginModel?.close()">Cancel</button>
            </div>
            <div class="text-center mt-2">
                <button class="btn btn-link btn-sm" type="button" @click="() => {loginModel?.close(); registerModel?.showModal();}">
                    Create an account?
                </button>
            </div>
        </form>
    </dialog>
    <dialog ref="registerModel" class="modal">
        <form class="modal-box bg-base-200 space-y-4 text-base-content" @submit.prevent="submitRegister">
            <h2 class="text-2xl font-bold border-b border-base-300 pb-2">Register</h2>
            <!-- Name Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Name</legend>
                <input
                    id="name"
                    v-model="form.name"
                    class="input input-bordered w-full"
                    required
                    type="text"
                />
                <ErrorAlert v-if="form.errors.name" :message="form.errors.name" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Email</legend>
                <input
                    id="email"
                    v-model="form.email"
                    class="input input-bordered w-full"
                    required
                    type="email"
                />
                <ErrorAlert v-if="form.errors.email" :message="form.errors.email" />
            </fieldset>
            <!-- Password Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Password</legend>
                <input
                    id="password"
                    v-model="form.password"
                    class="input input-bordered w-full"
                    required
                    type="password"
                />
                <ErrorAlert v-if="form.errors.password" :message="form.errors.password" />
            </fieldset>
            <!-- Password Confirmation Input -->
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Confirm Password</legend>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    class="input input-bordered w-full"
                    required
                    type="password"
                />
                <ErrorAlert v-if="form.errors.password_confirmation" :message="form.errors.password_confirmation" />
            </fieldset>
            <!-- Submit and Cancel Buttons -->
            <div class="modal-action mt-6 gap-2">
                <button
                    :disabled="form.processing" class="btn btn-primary px-8"
                    type="submit">
                    Register
                </button>
                <button class="btn btn-ghost" type="button" @click="() => registerModel?.close()">
                    Cancel
                </button>
            </div>
            <div class="text-center mt-2">
                <button class="btn btn-link btn-sm" type="button" @click="() => {registerModel?.close(); loginModel?.showModal();}">
                    Already have an account?
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
