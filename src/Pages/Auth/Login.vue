<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import pb from '@/pocketbase';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ErrorAlert from '@/Components/ErrorAlert.vue';

const router = useRouter();
const identity = ref('');
const password = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

const handleLogin = async () => {
    loading.value = true;
    error.value = null;
    try {
        await pb.collection('users').authWithPassword(identity.value, password.value);
        router.push('/home');
    } catch (err: unknown) {
        error.value = (err as { message?: string })?.message || 'Failed to sign in. Please check your credentials.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <GuestLayout>
        <div class="card bg-base-100 shadow-xl w-full max-w-md p-6">
            <h2 class="text-2xl font-bold text-center mb-6">Sign In to Oxy</h2>
            <ErrorAlert v-if="error" :message="error" class="mb-4" />
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="label"><span class="label-text">Email or Username</span></label>
                    <input v-model="identity" type="text" required class="input input-bordered w-full" placeholder="user@example.com" />
                </div>
                <div>
                    <label class="label"><span class="label-text">Password</span></label>
                    <input v-model="password" type="password" required class="input input-bordered w-full" placeholder="••••••••" />
                </div>
                <button type="submit" class="btn btn-primary w-full mt-4" :disabled="loading">
                    <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                    Sign In
                </button>
            </form>
            <div class="mt-4 text-center text-sm">
                Don't have an account?
                <router-link to="/register" class="link link-primary font-bold">Register</router-link>
            </div>
        </div>
    </GuestLayout>
</template>
