<script lang="ts" setup>
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import {computed} from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(send.url());
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification"/>

        <div class="mb-4 text-sm text-base-content/70">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link
            we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-success">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="btn">
                    Resend Verification Email
                </button>

                <Link
                    :href="logout.url()"
                    class="underline text-sm text-base-content/70 hover:text-base-content rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    method="post"
                >Log Out
                </Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
