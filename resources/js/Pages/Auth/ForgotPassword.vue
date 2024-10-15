<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { HiMail } from "oh-vue-icons/icons";
import { addIcons } from "oh-vue-icons";
addIcons(HiMail);

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset
            link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

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

            <div class="flex items-center justify-end mt-4">
                <button class="btn" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Email Password Reset Link
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
