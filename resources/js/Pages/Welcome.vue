<script setup lang="ts">
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref, onMounted, onUnmounted} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import backgroundImage from '../../../public/images/background.svg';

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
const targetDate = new Date('2024-10-25T08:30:00'); // Target date and time (October 25, 8:30 AM)
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
            <div>
                <header>
                    <div class="navbar flex justify-between">
                        <img src="/images/oxy.jpg" alt="Oxy" class="rounded-full size-24 w-24"/>
                        <div>
                            <Link v-if="$page.props.auth.user" :href="route('home')" class="btn btn-lg">
                                Home
                            </Link>

                            <template v-else>
                                <div class="grid gap-3 grid-flow-col">
                                    <!-- Login button that triggers the popup -->
                                    <button @click="() => {form.clearErrors(); loginModel?.showModal()}" class="btn btn-lg">
                                        Log in
                                    </button>
                                    <button @click="() => {form.clearErrors(); registerModel?.showModal()}"
                                            class="btn btn-lg">
                                        Register
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </header>
                <!-- Main content -->
                <main>
                    <div class="ml-20">
                    <h1 class="text-7xl font-sans text-gray-500">
                        Welcome to the future
                    </h1>
                    <div class="mt-5 w-1/4 p-4 rounded-lg">
                        <h2 class="text-3xl font-sans text-white text-left">
                            Scroll down to see what we offer
                        </h2>
                    </div>
                </div>

                    <div class="text-left flex my-8">
                        <div class="card shadow-lg bg-gray-500 text-white">
                            <div class="card-body">
                                <h2 class="text-4xl font-bold">Countdown to Death</h2>
                                <p class="text-2xl">{{ countdown }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-body bg-gray-500 mt-6">
                    <div class="flex flex-row items-center">
                        <!-- Image on the left, aligned with the text -->
                        <img src="/images/message.jpg" width="64" height="64" alt="Messaging"
                            class="rounded-full size-20 m-4"/>
                        
                        <!-- Text next to the image -->
                        <div class="ml-4">
                            <h2 class="card-title text-white">Messaging</h2>
                            <p class="text-white text-2xl">
                                CO2 lets users easily communicate with other users in a fast and convenient
                                way, with different channels and servers.
                            </p>
                        </div>
                    </div>
                </div>
                <img
                    src="/images/messages.jpg"
                    alt="Message screenshot"
                    class="rounded-3xl w-full"
                />

                <div class="grid grid-flow-col gap-8 mt-5 w-full">
                <!-- Servers Card -->
                <div class="card card-body bg-gray-500 mt-6"> <!-- Set width as needed -->
                    <div class="flex flex-col items-center text-center">
                        <!-- Text on top of the image -->
                        <h2 class="card-title text-white">Servers</h2>
                        <p class="text-white text-xl md:text-2xl">
                            Creating servers lets you easily communicate with multiple people and work on several projects at the same time.
                        </p>
                        <!-- Image under the text -->
                        <img src="/images/servers.png" alt="Servers" class="m-4 size-fit rounded-3xl "/>
                    </div>
                </div>

                <div class="card card-body bg-gray-500 mt-6 h-fit">
                    <div class="flex flex-col items-center text-center">
                        <h2 class="card-title text-white">Kanban Board</h2>
                        <p class="text-white text-xl md:text-2xl">
                            Our kanban board helps you manage projects in simple steps by organizing tasks into categories.
                        </p>
                        <img src="/images/kanban.png" alt="Kanban Board" class="m-4"/>
                    </div>
                </div>
            </div>
            <div class="card mt-10 bordered h-fit bg-white">
            <h2 class="card-title text-black ml-5 mt-5">What are you waiting for?</h2>
            <div class="flex justify-between items-center p-5"> <!-- Use flex to align items -->
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

                <!-- Footer -->
                <footer class="footer footer-center">
                    <div class="max-w-fit rounded-full bg-base-300 p-4 mt-4 bg-transparent text-white">
                        © {{ new Date().getFullYear() }} Oxy
                    </div>
                </footer>
            </div>
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
