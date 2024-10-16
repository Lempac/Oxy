<script setup lang="ts">
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue"; // Import ref for reactivity

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
<!-- Welcome page-->
<template>
    <Head title="Welcome"></Head>
    <body class="bg-[url('/images/background.svg')] bg-cover bg-center">
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

                                <!-- Register link -->
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
                    <h1 class="text-7xl font-sans text-white">
                        Welcome to the future
                    </h1>
                    <h2 class="text-3xl font-sans text-white mt-3">
                        Scroll down to see what we offer
                    </h2>
                </div>
                <div class=" p-4 mt-12 rounded-2xl shadow-lg">
                    <figure>
                        <img
                            src="/images/messages.jpg"
                            alt="Message screenshot"
                            class="rounded-3xl w-full"
                        />
                    </figure>
                    <div class="card bg-gray-700 card-side mt-6">
                        <figure>
                            <img src="/images/message.jpg" width="24" height="24" alt=""
                                 class="rounded-full size-20 m-4"/>
                        </figure>
                        <div class="card-body p-3">
                            <h2 class="card-title">Messaging</h2>
                            <p>
                                CO2 lets users easily communicate with other users in a fast and convenient
                                way
                            </p>
                        </div>
                    </div>
                    <!-- End of messages -->
                    <div class="grid grid-flow-col gap-8 mt-5 w-full">
                        <div class="card bg-gray-700 card-side">
                            <figure class="pl-2">
                                <img src="/images/co2.jpg" width="24" height="24" alt=""
                                     class="rounded-full size-20 m-4"/>
                            </figure>
                            <div class="card-body p-3">
                                <h2 class="card-title">Servers</h2>
                                <p>
                                    Creating serves lets you easy communicate with multiple people and work with several
                                    projects at the same time.
                                </p>
                            </div>
                        </div>
                        <div class="card bg-gray-700 card-side">
                            <figure class="pl-4">
                                <img src="/images/kanban.jpg" width="24" height="24" alt=""
                                     class="rounded-full size-20 m-4"/>
                            </figure>
                            <div class="card-body p-3">
                                <h2 class="card-title">Kanban board</h2>
                                <p>
                                    Our kanban board,
                                    helps you take your projects in simple steps. The kanban board let's you separate
                                    everything you are doing, done and need to do.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <!-- Footer -->
            <footer class="footer footer-center">
                <div class="max-w-fit rounded-full bg-base-300 p-4 mt-4">
                    © {{ new Date().getFullYear() }} Oxy
                </div>
            </footer>
        </div>
    </div>

    <!-- Login popup modal -->
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
    <!--    </Transition>-->
    <!-- Register  -->
    <!--    <Transition>-->
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
