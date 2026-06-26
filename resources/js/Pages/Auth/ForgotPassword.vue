<script lang="ts" setup>
import { email } from '@/routes/password';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { HiMail } from 'vue-icons-plus/hi';


defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(email.url());
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password"/>

        <div class="mb-4 text-sm text-base-content/70">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset
            link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-success">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <label class="block font-medium text-sm text-base-content" for="email"> Email </label>

                <label class="input input-bordered flex items-center gap-2">
                    <HiMail class="h-4 w-4 opacity-70"/>
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

            <div class="flex items-center justify-end mt-4">
                <button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="btn">
                    Email Password Reset Link
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
