<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'; // Import ref for reactivity

defineProps<{
    laravelVersion: string;
    phpVersion: string;
}>();

// Reactive state for showing/hiding the login popup
const showLoginPopup = ref(false);
const showRegisterPopup = ref(false);

const switchR = () => {
    closeLoginPopup();
    openRegisterPopup();
};

const switchL = () => {
    closeRegisterPopup();
    openLoginPopup();
};

// Function to open the login popup
const openLoginPopup = () => {
    form.clearErrors()
    showLoginPopup.value = true;
};

// Function to close the login popup
const closeLoginPopup = () => {
    showLoginPopup.value = false;
};

const openRegisterPopup = () => {
    form.clearErrors()
    showRegisterPopup.value = true;
};

const closeRegisterPopup = () => {
    showRegisterPopup.value = false;
};

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    remember: false,
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

        },
        onError: () => {
            // Handle errors (e.g., display them below the respective fields)
        }
    });
};
</script>

<template>
    <Head title="Welcome">
    </Head>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img
            id="background"
            class="absolute h-full w-full object-cover"
            src="/images/background.svg"
            alt=""
        />
        <div
            class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#285aff] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <!-- Header -->
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <a href="/welcome" class="block">
                            <svg
                                class="h-40 w-40 rounded-full lg:h-20 lg:w-20 object-cover"
                                viewBox="0 0 62 65"
                                fill="none"
                            >

                                <image href="/images/oxy.jpg"  x="-10" y="-19" height="110" width="110"/>
                            </svg>
                        </a>
                    </div>

                    <nav class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#285aff] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <!-- Login button that triggers the popup -->
                            <button
                                @click="openLoginPopup"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#285aff] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Log in
                            </button>

                            <!-- Register link -->
                            <button
                                @click="openRegisterPopup"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#285aff] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Register
                            </button>
                        </template>
                    </nav>
                </header>

                <!-- Main content -->
                <main class="mt-6">
                    <h1 style="color: aliceblue; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; font-size: 70px;">
                        Welcome to the future
                    </h1>
                    <h2 style="color: aliceblue; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; font-size: 30px; text-align: left; margin-bottom: 100px">
                        Scroll down to see what we offer
                    </h2>
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">

                        <a
                            id="docs-card"
                            class="mt-[100px] col-span-full gap-7 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#285aff] lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#285aff]"
                        >
                            <div id="screenshot-container" class="relative flex w-full flex-1 items-stretch">
                                <img
                                    src="/images/messages.jpg"
                                    alt="Message screenshot"
                                    class="aspect-video h-full w-full flex-1 rounded-[10px] object-left-top object-cover drop-shadow-[0px_4px_34px_rgba(0,0,0,0.06)] dark:hidden"
                                />
                                <img
                                    src="/images/messages.jpg"
                                    alt="Message screenshot"
                                    class="hidden aspect-video h-full w-full flex-1 rounded-[10px] object-left-top object-cover drop-shadow-[0px_4px_34px_rgba(0,0,0,0.25)] dark:block"
                                />
                            </div>

                            <div class="relative flex items-center gap-6 lg:items-end">
                                <div id="docs-card-content" class="flex items-start gap-6 lg:flex-col">
                                    <div
                                        class="flex size-12 shrink-0 items-center justify-center rounded-full bg-white sm:size-16">
                                        <svg
                                            class="size-5 sm:size-6"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            width="24"
                                            height="24"
                                        >
                                            <image href="/images/message.jpg"  width="24" height="24"/>
                                        </svg>
                                    </div>

                                    <div class="pt-3 sm:pt-5 lg:pt-0">
                                        <h2 class="text-xl font-semibold text-black dark:text-white">Messaging</h2>
                                        <p class="mt-4 text-sm/relaxed">
                                            CO2 lets users easily communicate with other users in a fast and convenient
                                            way
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <!-- End of messages -->

                        <a
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#285aff] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#285aff]"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-white sm:size-16"
                            >
                                <svg
                                    class="size-5 sm:size-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    width="24" height="24"
                                >
                                    <image href="/images/co2.jpg" width="24" height="24"/>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-black dark:text-white">Servers</h2>

                                <p class="mt-4 text-sm/relaxed">
                                    Creating serves lets you easy communicate with multiple people and work with several
                                    projects at the same time.
                                </p>
                            </div>
                        </a>

                        <div
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800"
                        >
                            <div
                                class="flex size-20 shrink-0 items-center justify-center rounded-full bg-white sm:size-16"
                            >
                                <svg
                                    class="size-5 sm:size-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    width="24" height="24"
                                >
                                    <image href="/images/kanban.jpg" width="24" height="24"/>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-black dark:text-white">Kanban board</h2>

                                <p class="mt-4 text-sm/relaxed">
                                    Our
                                    <a
                                        href="https://en.wikipedia.org/wiki/Kanban_(development)"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#285aff] dark:hover:text-white dark:focus-visible:ring-[#285aff]"
                                    >kanban board</a
                                    >,
                                    helps you take your projects in simple steps. The kanban board let's you seperate
                                    everything you are doing, done and need to do.
                                </p>
                            </div>
                        </div>
                    </div>
                </main>


                <!-- Footer -->
                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    © 2024 Oxygen
                </footer>
            </div>
        </div>
    </div>

    <!-- Login popup modal -->
    <Transition>
        <div v-if="showLoginPopup" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-8 rounded shadow-lg w-full max-w-md  dark:bg-slate-900">
                <h2 class="text-xl font-semibold mb-4 text-black dark:text-white">Log in</h2>
                <form @submit.prevent="submitLogin">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-black dark:text-white">Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            class="mt-1 block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black dark:bg-slate-700 dark:text-white"
                        />
                        <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                    </div>

                    <div class="mb-4">
                        <label for="password"
                               class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            class="mt-1 block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black  dark:bg-slate-700 dark:text-white"
                        />
                        <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input
                            id="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-white opacity-70 ">Remember
                            me</label>
                    </div>

                    <div class="flex justify-between items-center">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md ">
                            Log in
                        </button>

                        <button @click="closeLoginPopup" class="text-red-500">Cancel</button>
                    </div>
                </form>
                <button @click="switchR" class="text-black dark:text-white"
                        style="text-decoration: underline; font-size: 15px; padding-top:20px ;">Create an account?
                </button>
            </div>
        </div>
    </Transition>
    <!-- Register  -->
    <Transition>
        <div v-if="showRegisterPopup" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-8 rounded shadow-lg w-full max-w-md dark:bg-slate-900">
                <h2 class="text-xl font-semibold mb-4 text-black dark:text-white">Register</h2>

                <form @submit.prevent="submitRegister">
                    <!-- Name Input -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-white">Name</label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black  dark:bg-slate-700 dark:text-white"
                        />
                        <!-- Error for name -->
                        <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                    </div>

                    <!-- Email Input -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-white">Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black  dark:bg-slate-700 dark:text-white"
                        />
                        <!-- Error for email -->
                        <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-4">
                        <label for="password"
                               class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black  dark:bg-slate-700 dark:text-white"
                        />
                        <!-- Error for password -->
                        <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                    </div>

                    <!-- Password Confirmation Input -->
                    <div class="mb-4">
                        <label for="password_confirmation"
                               class="block text-sm font-medium text-gray-700 dark:text-white">Confirm Password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black  dark:bg-slate-700 dark:text-white"
                        />
                        <!-- Error for password_confirmation -->
                        <span v-if="form.errors.password_confirmation"
                              class="text-red-500 text-sm">{{ form.errors.password_confirmation }}</span>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="flex justify-between items-center">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md"
                                :disabled="form.processing">
                            Register
                        </button>
                        <button @click="closeRegisterPopup" type="button" class="text-red-500">
                            Cancel
                        </button>
                    </div>
                </form>
                <button @click="switchL" class="text-black dark:text-white"
                        style="text-decoration: underline; font-size: 15px; padding-top:20px ;">Already have an account?
                </button>
            </div>
        </div>
    </Transition>

</template>
