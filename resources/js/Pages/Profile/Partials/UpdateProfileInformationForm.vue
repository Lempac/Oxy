<script setup lang="ts">
import {Link, useForm, usePage} from '@inertiajs/vue3';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {RiUser3Line, HiMail} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";
import {computed} from "vue";

addIcons(RiUser3Line, HiMail);

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;


const form = useForm<{ name: string, email: string, icon: File | null }>({
    name: user.name,
    email: user.email,
    icon: null,
});
const baseUrl = window.location.origin;

console.log(usePage().props.auth.user.icon);

const url = computed(() => usePage().props.auth.user.icon ? baseUrl+usePage().props.auth.user.icon : (form.icon ? URL.createObjectURL(form.icon) : ""));
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Information</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="form.post(route('profile.update'), {method: 'put'})" class="mt-6 space-y-6">
            <!-- Profile Picture Upload -->
            <div class="form-control">
                <label class="text-gray-600 dark:text-gray-400 pl-3 flex items-center flex-row gap-4"
                       for="profilePicture">
                    <img
                        class="size-24 place-content-center cursor-pointer text-center rounded-full bg-gray-200 dark:bg-gray-600 transition-all duration-300 ease-in-out hover:bg-transparent"
                        :src="url" alt="+"/>
                    Upload profile picture
                </label>
                <input
                    id="profilePicture"
                    type="file"
                    class="hidden"
                    accept="image/png, image/jpeg"
                    @input="form.icon = (<HTMLInputElement>$event.target).files![0]"
                />
            </div>

            <div class="form-control">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="name">Name</label>
                <label class="input input-bordered flex items-center gap-2">
                    <v-icon name="ri-user-3-line" class="h-4 w-4 opacity-70"/>
                    <input id="name"
                           type="text"
                           class="mt-1 block w-full"
                           v-model="form.name"
                           required
                           autofocus
                           autocomplete="name"
                    />
                </label>
                <ErrorAlert class="mt-2" :message="form.errors.name"/>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="email">Email</label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon name="hi-mail" class="h-4 w-4 opacity-70"/>
                    <input id="email"
                           type="email"
                           class="mt-1 block w-full"
                           v-model="form.email"
                           required
                           autocomplete="username"
                    />
                </label>

                <ErrorAlert class="mt-2" :message="form.errors.email"/>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button class="btn" :disabled="form.processing">Save</button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
