<script lang="ts" setup>
import { home, login, manual, register } from '@/routes';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {onMounted, onUnmounted, ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import backgroundImage from '../../../public/images/background.svg';
import {CoChatBubble, FaBook, RiComputerFill} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

addIcons(CoChatBubble, RiComputerFill, FaBook);

const loginModel = ref<HTMLDialogElement>();
const registerModel = ref<HTMLDialogElement>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    remember: false,
});

// Countdown state
const countdown = ref('');
const targetDate = new Date('2024-12-02T08:30:00');
let countdownInterval: number | undefined; // Store the interval ID as a number

const calculateTimeLeft = () => {
    const now = new Date();
    const difference = targetDate.getTime() - now.getTime();

    if (difference <= 0) {
        countdown.value = "The time has come!";
        return;
    }

    const days = Math.floor(difference / (1000 * 60 * 60 * 24));
    const hours = Math.floor((difference / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((difference / 1000 / 60) % 60);
    const seconds = Math.floor((difference / 1000) % 60);

    countdown.value = `${days}d ${hours}h ${minutes}m ${seconds}s`;
};

// Function to start the countdown
const startCountdown = () => {
    calculateTimeLeft();
    countdownInterval = window.setInterval(() => {
        calculateTimeLeft();
        if (targetDate.getTime() <= new Date().getTime()) {
            clearInterval(countdownInterval);
        }
    }, 1000);
};

// Start the countdown when the component is mounted
onMounted(() => {
    startCountdown();
});

// Clean up the interval when the component is unmounted
onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
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
    <body :style="`background-image: url(${backgroundImage})`" class="bg-cover bg-center min-h-screen">
    <div class="card card-body">
        <header>
            <div class="navbar mx-0 px-0">
                <div class="navbar-start">
                    <img alt="" class="block h-16 w-auto fill-current" src="/images/oxy.png"/>
                </div>
                <ApplicationLogo class="navbar-center mb-1.5"/>
                <div class="navbar-end">
                    <Link v-if="$page.props.user" :href="home.url()" class="btn btn-lg">
                        Home
                    </Link>
                    <template v-else>
                        <div class="grid gap-3 grid-flow-col">
                            <button class="btn btn-lg" @click="() => {form.clearErrors(); loginModel?.showModal()}">
                                Log in
                            </button>
                            <button class="btn btn-lg" @click="() => {form.clearErrors(); registerModel?.showModal()}">
                                Register
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </header>

        <main>
            <h1 class="text-7xl font-sans text-base-content/70">Welcome to the future</h1>

            <!-- Countdown Section -->
            <!--            <div class="text-left flex my-4 mb-10 mt-10 mr-8">-->
            <!--                <div class="card shadow-lg bg-base-300 text-base-content">-->
            <!--                    <div class="card-body p-2">-->
            <!--                        <h2 class="text-2xl font-bold">Heat death of the universe</h2>-->
            <!--                        <p class="text-xl">{{ countdown }}</p>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->

            <!-- Info cards -->
            <div class="flex justify-center mt-8 space-x-8">
                <div class="card bg-neutral text-neutral-content p-4 w-1/3 text-center">
                    <v-icon class="w-16 h-16 mx-auto mb-4" name="co-chat-bubble" scale="1"/>
                    <h2 class="text-2xl font-bold">Messaging</h2>
                    <p class="mt-2">Oxy lets users easily communicate with others quickly with channels and servers.</p>
                </div>

                <div class="card bg-neutral text-neutral-content p-4 w-1/3 text-center">
                    <v-icon class="w-16 h-16 mx-auto mb-4" name="ri-computer-fill" scale="1"/>
                    <h2 class="text-2xl font-bold">Servers</h2>
                    <p class="mt-2">Create servers to communicate with multiple people and work on projects
                        simultaneously.</p>
                </div>

                <div class="card bg-neutral text-neutral-content p-4 w-1/3 text-center">
                    <img alt="Kanban" class="w-16 h-16 mx-auto mb-4" src="/images/kanban.png"/>
                    <h2 class="text-2xl font-bold">Kanban Board</h2>
                    <p class="mt-2">Organize tasks into categories to manage projects with ease using our kanban
                        board.</p>
                </div>
            </div>

            <div class="card mt-10 bordered h-fit bg-base-100">
                <h2 class="card-title text-base-content ml-5 mt-5">What are you waiting for?</h2>
                <div class="flex justify-between items-center p-5">
                    <p class="text-base-content text-xl md:text-l ml-5">
                        Join now!!!
                    </p>
                    <div>
                        <Link v-if="$page.props.user" :href="home.url()" class="btn btn-lg">
                            Home
                        </Link>
                        <template v-else>
                            <div class="grid gap-3 grid-flow-col">
                                <!-- Login button that triggers the popup -->
                                <button
                                    class="btn btn-lg btn-error text-error-content hover:text-base-content"
                                    @click="() => {form.clearErrors(); loginModel?.showModal()}">
                                    Join
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="collapse bg-base-200 text-center mt-2">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium underline">See more</div>
                <div class="collapse-content">
                    <div class="font-medium text-2xl">About us</div>
                    <ul class="timeline timeline-snap-icon max-md:timeline-compact timeline-vertical">
                        <li>
                            <div class="timeline-middle">
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        clip-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        fill-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="timeline-start mb-10 md:text-end">
                                <div class="text-lg font-black text-center">Server</div>
                                Within our program you will be able to create a server, which allows you to control your
                                own server and
                                manage it's members. This means you can control who will be allowed in the server as
                                well as what role
                                they have. By creating a server you are also able to create text channels, voice
                                channels and gain access
                                to the kanban board.
                            </div>
                            <hr/>
                        </li>
                        <li>
                            <hr/>
                            <div class="timeline-middle">
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        clip-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        fill-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="timeline-end mb-10">
                                <div class="text-lg font-black">Roles</div>
                                Within each server, you can be assigned a role. It could be something simple like
                                "programmer" or "manager".
                                These roles will be visible by all members and help them communicate better.
                            </div>
                            <hr/>
                        </li>
                        <li>
                            <hr/>
                            <div class="timeline-middle">
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        clip-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        fill-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="timeline-start mb-10 md:text-end">
                                <div class="text-lg font-black text-center">temp</div>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Incidunt maiores amet a
                                quaerat eum, quisquam adipisci ratione ea corrupti! Repellat numquam recusandae neque
                                esse nisi porro expedita possimus maxime accusamus.
                            </div>
                            <hr/>
                        </li>
                    </ul>
                </div>
            </div>

        </main>

        <footer class="footer footer-center mt-10 text-base-content">
            <div class="rounded-full p-4 bg-neutral text-neutral-content">
                © {{ new Date().getFullYear() }} Oxy
            </div>
            <Link
                :href="manual.url()" class="left-2 mt-3 absolute btn btn-ghost tooltip tooltip-right"
                data-tip="FAQ">
                <button class="flex items-center justify-center h-10 w-5">
                    <v-icon animation="pulse" name="fa-book" scale="2"/>
                </button>
            </Link>
        </footer>
    </div>
    <dialog ref="loginModel" class="modal">
        <form class="modal-box space-y-4 text-base-content" @submit.prevent="submitLogin">
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
                <button class="btn btn-success px-8" type="submit">
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
        <form class="modal-box space-y-4 text-base-content" @submit.prevent="submitRegister">
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
                    :disabled="form.processing" class="btn btn-success px-8"
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
