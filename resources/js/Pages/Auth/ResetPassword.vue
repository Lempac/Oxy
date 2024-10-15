<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { HiMail, MdKey } from "oh-vue-icons/icons";
import { addIcons } from "oh-vue-icons";
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
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <form @submit.prevent="submit">
            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="email"> Email </label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon name="hi-mail" class="h-4 w-4 opacity-70"/>
                    <input id="email"
                           type="email"
                           class="mt-1 block w-full"
                           v-model="form.email"
                           required
                           autofocus
                           autocomplete="username"
                    />
                </label>

                <ErrorAlert :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="password"> Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon name="md-key" class="h-4 w-4 opacity-70"/>
                    <input id="password"
                           type="password"
                           class="mt-1 block w-full"
                           v-model="form.password"
                           required
                           autocomplete="new-password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="password_confirmation"> Confirm Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <v-icon name="md-key" class="h-4 w-4 opacity-70"/>
                    <input id="password_confirmation"
                           type="password"
                           class="mt-1 block w-full"
                           v-model="form.password_confirmation"
                           required
                           autocomplete="new-password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button class="btn" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reset Password
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
