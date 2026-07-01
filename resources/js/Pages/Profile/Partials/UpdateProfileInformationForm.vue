<script lang="ts" setup>
import { update } from '@/routes/profile';
import { send } from '@/routes/verification';
import {Link, useForm, usePage} from '@inertiajs/vue3';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {ref} from "vue";
import {baseUrl} from "@/bootstrap";
import {Themes, ThemeType} from "@/types";
import { Io5AddOutline } from 'vue-icons-plus/io5';
import { HiMail } from 'vue-icons-plus/hi';
import { RiUser3Line } from 'vue-icons-plus/ri';


defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = usePage().props.user!;

const icon = ref<string | null>(user.icon ? baseUrl + user.icon : null);
const inputFile = ref<File | null>();

const form = useForm<{ name: string, email: string, icon: File | null, theme: ThemeType }>({
    name: user.name,
    email: user.email,
    icon: inputFile.value!,
    theme: user.theme || Themes.OXY,
});


const updateIcon = (val: File) => {
    inputFile.value = val;
    form.icon = inputFile.value;
    icon.value = URL.createObjectURL(inputFile.value);
}

</script>

<template>
    <section>
        <p class="mt-1 text-sm text-base-content/70">
            Update your account's profile information and email address.
        </p>

        <form class="mt-6 space-y-6" @submit.prevent="form.post(update.url(), {method: 'put'})">
            <!-- Profile Picture Upload -->
            <div class="form-control flex flex-row items-center gap-4 group">
                <label
                    class="cursor-pointer rounded-full bg-base-200 transition-all duration-300 ease-in-out hover:bg-transparent group-hover:bg-transparent"
                    for="profilePicture">
                    <img v-if="icon !== null" :src="icon" alt="" class="size-16 rounded-full"/>
                    <Io5AddOutline v-else scale="3.333"/>
                </label>
                <label class="cursor-pointer" for="profilePicture">Upload profile picture</label>
                <input
                    id="profilePicture"
                    ref="inputFile"
                    accept="image/png, image/jpeg"
                    class="hidden"
                    type="file"
                    @input="updateIcon((<HTMLInputElement>$event.target).files![0])"
                />
            </div>
            <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>

            <div class="form-control">
                <label class="block font-medium text-sm text-base-content/90" for="name">Name</label>
                <label class="input input-bordered flex items-center gap-2">
                    <RiUser3Line class="h-4 w-4 opacity-70"/>
                    <input
                        id="name"
                        v-model="form.name"
                        autocomplete="name"
                        autofocus
                        class="mt-1 block w-full"
                        required
                        type="text"
                    />
                </label>
                <ErrorAlert :message="form.errors.name" class="mt-2"/>
            </div>

            <div>
                <label class="block font-medium text-sm text-base-content/90" for="email">Email</label>

                <label class="input input-bordered flex items-center gap-2">
                    <HiMail class="h-4 w-4 opacity-70"/>
                    <input
                        id="email"
                        v-model="form.email"
                        autocomplete="username"
                        class="mt-1 block w-full"
                        required
                        type="email"
                    />
                </label>

                <ErrorAlert :message="form.errors.email" class="mt-2"/>
            </div>

            <div class="form-control">
                <label class="block font-medium text-sm text-base-content/90" for="theme">Theme</label>
                <select
                    id="theme"
                    v-model="form.theme"
                    class="select select-bordered mt-1 block w-full"
                >
                    <option v-for="theme in Themes" :key="theme" :value="theme">
                        {{ theme.charAt(0).toUpperCase() + theme.slice(1) }}
                    </option>
                </select>
                <ErrorAlert :message="form.errors.theme" class="mt-2"/>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-base-content">
                    Your email address is unverified.
                    <Link
                        :href="send.url()"
                        class="underline text-sm text-base-content/70 hover:text-base-content rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        method="post"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-success"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button :disabled="form.processing" class="btn">Save</button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-base-content/70">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
