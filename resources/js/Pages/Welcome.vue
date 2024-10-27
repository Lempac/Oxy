<script setup lang="ts">
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref, onMounted, onUnmounted} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import backgroundImage from '../../../public/images/background.svg';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import echo from "@/echo";
import { CoChatBubble } from "oh-vue-icons/icons"; 
import { RiComputerFill } from "oh-vue-icons/icons";
import { addIcons } from "oh-vue-icons"; 
addIcons(CoChatBubble, RiComputerFill);

// Reactive state for showing/hiding the login popup
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
const targetDate = new Date('2024-11-01T08:30:00');
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
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
const submitRegister = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        }
    });
};

</script>

<template>
    <Head title="Welcome"></Head>
    <body class="bg-cover bg-center" :style="`background-image: url(${backgroundImage})`">
        <div class="card card-body">
            <header>
                <div class="navbar flex justify-between">
                    <img src="/images/oxy.png" class="block h-16 w-auto fill-current"/>
                    <div>
                        <Link v-if="$page.props.auth.user" :href="route('home')" class="btn btn-lg">
                            Home
                        </Link>
                        <template v-else>
                            <div class="grid gap-3 grid-flow-col">
                                <button @click="() => {form.clearErrors(); loginModel?.showModal()}" class="btn btn-lg">
                                    Log in
                                </button>
                                <button @click="() => {form.clearErrors(); registerModel?.showModal()}" class="btn btn-lg">
                                    Register
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </header>

            <main class="ml-20">
                <h1 class="text-7xl font-sans text-gray-400">Welcome to the future</h1>
                <div class="mt-5 w-1/4 p-4 rounded-lg">
                    <h2 class="text-3xl font-sans text-white text-left">Scroll down to see what we offer</h2>
                </div>

                <!-- Countdown Section --> 
                <div class="text-left flex my-4"> 
                    <div class="card shadow-lg bg-gray-500 text-white">
                        <div class="card-body p-2"> 
                            <h2 class="text-2xl font-bold">Countdown to next phase</h2>
                            <p class="text-xl">{{ countdown }}</p> 
                        </div>
                    </div>
                </div>

                <!-- Three-Column Feature Section -->
                <div class="flex justify-center mt-8 space-x-8">
                    <!-- Messaging Card -->
                    <div class="card bg-gray-500 text-white p-4 w-1/3 text-center">
                        <v-icon name="co-chat-bubble" scale="1" class="w-16 h-16 mx-auto mb-4"></v-icon>
                        <h2 class="text-2xl font-bold">Messaging</h2>
                        <p class="mt-2">Oxy lets users easily communicate with others quickly with channels and servers.</p>
                    </div>

                    <!-- Servers Card -->
                    <div class="card bg-gray-500 text-white p-4 w-1/3 text-center">
                        <v-icon name="ri-computer-fill" scale="1" class="w-16 h-16 mx-auto mb-4"></v-icon>
                        <h2 class="text-2xl font-bold">Servers</h2>
                        <p class="mt-2">Create servers to communicate with multiple people and work on projects simultaneously.</p>
                    </div>

                    <!-- Kanban Board Card -->
                    <div class="card bg-gray-500 text-white p-4 w-1/3 text-center">
                        <img src="/images/kanban.png" alt="Kanban" class="w-16 h-16 mx-auto mb-4"/>
                        <h2 class="text-2xl font-bold">Kanban Board</h2>
                        <p class="mt-2">Organize tasks into categories to manage projects with ease using our kanban board.</p>
                    </div>
                </div>

                <!-- Join Now Card -->
                <div class="card mt-10 bordered h-fit bg-white">
                    <h2 class="card-title text-black ml-5 mt-5">What are you waiting for?</h2>
                    <div class="flex justify-between items-center p-5"> 
                        <p class="text-black text-xl md:text-l ml-5">
                            Join now!!!
                        </p>
                        <div>
                            <Link v-if="$page.props.auth.user" :href="route('home')" class="btn btn-lg">
                                Home
                            </Link>

                            <template v-else>
                                <div class="grid gap-3 grid-flow-col">
                                    <!-- Login button that triggers the popup -->
                                    <button @click="() => {form.clearErrors(); loginModel?.showModal()}" class="btn btn-lg bg-red-500 text-white hover:text-black">
                                        Join
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="footer footer-center mt-10 text-white">
                <div class="rounded-full bg-base-300 p-4">
                    © {{ new Date().getFullYear() }} Oxy
                </div>
            </footer>
        </div>
        <dialog ref="loginModel" class="modal">
        <form @submit.prevent="submitLogin" class="modal-box">
            <h2 class="text-lg font-bold">Log in</h2>
            <div class="form-control">
                <label for="email" class="label">Email</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    class="input input-bordered grow"
                />
                <ErrorAlert v-if="form.errors.email" :message="form.errors.email" class="mt-2"/>
            </div>
            <div class="form-control">
                <label for="password" class="label">
                    Password
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    class="input input-bordered grow"
                />
                <ErrorAlert v-if="form.errors.password" :message="form.errors.password" class="mt-2"/>
            </div>
            <div class="form-control">
                <label for="remember" class="label place-content-start gap-3">
                    <input id="remember" type="checkbox" v-model="form.remember" class="checkbox"/>
                    Remember me
                </label>
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" class="btn btn-success">
                    Log in
                </button>

                <button @click="() => loginModel?.close()" class="btn btn-error">Cancel</button>
            </div>
            <button @click="() => {loginModel?.close(); registerModel?.showModal();}" class="btn btn-link">Create an
                account?
            </button>
        </form>
    </dialog>
    <dialog ref="registerModel" class="modal">
        <form @submit.prevent="submitRegister" class="modal-box">
            <h2 class="text-lg font-bold">Register</h2>
            <!-- Name Input -->
            <div class="form-control">
                <label class="label" for="name">Name</label>
                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    class="input input-bordered grow"
                />
                <!-- Error for name -->
                <ErrorAlert v-if="form.errors.name" :message="form.errors.name" class="mt-2"/>
            </div>
            <div class="form-control">
                <!-- Email Input -->
                <label class="label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    class="input input-bordered grow"
                />
                <!-- Error for email -->
                <ErrorAlert v-if="form.errors.email" :message="form.errors.email" class="mt-2"/>
            </div>
            <!-- Password Input -->
            <div class="form-control">
                <label class="label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    class="input input-bordered grow"
                />
                <!-- Error for password -->
                <ErrorAlert v-if="form.errors.password" :message="form.errors.password" class="mt-2"/>
            </div>
            <!-- Password Confirmation Input -->
            <div class="form-control">
                <label class="label" for="password_confirmation">Confirm Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    class="input input-bordered grow"
                />
                <!-- Error for password_confirmation -->
                <ErrorAlert v-if="form.errors.password_confirmation" :message="form.errors.password_confirmation"
                            class="mt-2"/>
            </div>
            <!-- Submit and Cancel Buttons -->
            <div class="justify-between modal-action">
                <button type="submit" class="btn btn-success"
                        :disabled="form.processing">
                    Register
                </button>
                <button @click="() => registerModel?.close()" type="button" class="btn btn-error">
                    Cancel
                </button>
            </div>
            <button @click="() => {registerModel?.close(); loginModel?.showModal();}" class="btn btn-link">Already have
                an account?
            </button>
        </form>
    </dialog>
    <!--    </Transition>-->
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
