<script lang="ts" setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdKey } from 'vue-icons-plus/md';

const router = useRouter();
const password = ref('');
const processing = ref(false);
const error = ref<string | null>(null);

const submit = async () => {
    processing.value = true;
    error.value = null;
    try {
        router.push('/home');
    } catch {
        error.value = 'Password confirmation failed.';
    } finally {
        processing.value = false;
        password.value = '';
    }
};
</script>

<template>
    <GuestLayout>
        <div class="mb-4 text-sm text-base-content/70">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit">
            <div>
                <label class="block font-medium text-sm text-base-content" for="password">
                    Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <MdKey class="h-4 w-4 opacity-70"/>
                    <input
                        id="password"
                        v-model="password"
                        autocomplete="current-password"
                        autofocus
                        class="mt-1 block w-full"
                        name="password"
                        required
                        type="password"
                    />
                </label>

                <ErrorAlert v-if="error" :message="error" class="mt-2"/>
            </div>

            <div class="flex justify-end mt-4">
                <button :class="{ 'opacity-25': processing }" :disabled="processing" class="btn btn-primary ms-4">
                    Confirm
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
