<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import pb from '@/pocketbase';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ErrorAlert from '@/Components/ErrorAlert.vue';

const router = useRouter();
const username = ref('');
const password = ref('');
const passwordConfirm = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

const handleRegister = async () => {
    if (password.value !== passwordConfirm.value) {
        error.value = 'Passwords do not match.';
        return;
    }

    loading.value = true;
    error.value = null;
    try {
        const nameVal = username.value.trim();
        await pb.collection('users').create({
            name: nameVal,
            password: password.value,
            passwordConfirm: passwordConfirm.value,
        });
        await pb.collection('users').authWithPassword(nameVal, password.value);
        router.push('/home');
    } catch (err: unknown) {
        error.value = (err as { message?: string })?.message || 'Registration failed.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <GuestLayout>
        <div class="card bg-base-100 shadow-xl w-full max-w-md p-6">
            <h2 class="text-2xl font-bold text-center mb-6">Create an Account</h2>
            <ErrorAlert v-if="error" :message="error" class="mb-4" />
            <form @submit.prevent="handleRegister" class="space-y-4">
                <div>
                    <label class="label"><span class="label-text">Username</span></label>
                    <input v-model="username" type="text" required class="input input-bordered w-full" placeholder="username" />
                </div>
                <div>
                    <label class="label"><span class="label-text">Password</span></label>
                    <input v-model="password" type="password" required class="input input-bordered w-full" placeholder="••••••••" />
                </div>
                <div>
                    <label class="label"><span class="label-text">Confirm Password</span></label>
                    <input v-model="passwordConfirm" type="password" required class="input input-bordered w-full" placeholder="••••••••" />
                </div>
                <button type="submit" class="btn btn-primary w-full mt-4" :disabled="loading">
                    <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                    Create Account
                </button>
            </form>
            <div class="mt-4 text-center text-sm">
                Already have an account?
                <router-link to="/login" class="link link-primary font-bold">Sign In</router-link>
            </div>
        </div>
    </GuestLayout>
</template>
