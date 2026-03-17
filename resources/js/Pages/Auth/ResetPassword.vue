<script lang="ts" setup>
import { store } from '@/routes/password';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import {HiMail, MdKey} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";
import ErrorAlert from "@/Components/ErrorAlert.vue";

addIcons(HiMail, MdKey);

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(store.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password"/>

        <form @submit.prevent="submit">
            <div>
                <label class="block font-medium text-sm text-base-content" for="email"> Email </label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon class="h-4 w-4 opacity-70" name="hi-mail"/>
                    <input
                        id="email"
                        v-model="form.email"
                        autocomplete="username"
                        autofocus
                        class="mt-1 block w-full"
                        required
                        type="email"
                    />
                </label>

                <ErrorAlert :message="form.errors.email" class="mt-2"/>
            </div>

            <div class="mt-4">
                <label class="block font-medium text-sm text-base-content" for="password">
                    Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon class="h-4 w-4 opacity-70" name="md-key"/>
                    <input
                        id="password"
                        v-model="form.password"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        required
                        type="password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password" class="mt-2"/>
            </div>

            <div class="mt-4">
                <label class="block font-medium text-sm text-base-content" for="password_confirmation">
                    Confirm Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon class="h-4 w-4 opacity-70" name="md-key"/>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        required
                        type="password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password_confirmation" class="mt-2"/>
            </div>

            <div class="flex items-center justify-end mt-4">
                <button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="btn">
                    Reset Password
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
