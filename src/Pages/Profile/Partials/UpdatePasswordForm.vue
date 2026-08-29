<script lang="ts" setup>
import { ref } from 'vue';
import pb from '@/pocketbase';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdKey } from 'vue-icons-plus/md';

const oldPassword = ref('');
const password = ref('');
const passwordConfirm = ref('');
const processing = ref(false);
const recentlySuccessful = ref(false);
const error = ref<string | null>(null);

const updatePassword = async () => {
    if (!pb.authStore.model?.id) return;
    if (password.value !== passwordConfirm.value) {
        error.value = 'Passwords do not match.';
        return;
    }

    processing.value = true;
    error.value = null;
    try {
        await pb.collection('users').update(pb.authStore.model.id, {
            oldPassword: oldPassword.value,
            password: password.value,
            passwordConfirm: passwordConfirm.value,
        }, { requestKey: null });
        recentlySuccessful.value = true;
        oldPassword.value = '';
        password.value = '';
        passwordConfirm.value = '';
        setTimeout(() => { recentlySuccessful.value = false; }, 3000);
    } catch (err: unknown) {
        error.value = (err as { message?: string })?.message || 'Failed to update password.';
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <section>
        <p class="mt-1 text-sm text-base-content/70">
            Ensure your account is using a long, random password to stay secure.
        </p>

        <form class="mt-6 space-y-6" @submit.prevent="updatePassword">
            <div>
                <label class="block font-medium text-sm text-base-content/90" for="current_password">
                    Current Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <MdKey class="h-4 w-4 opacity-70"/>
                    <input
                        id="current_password"
                        v-model="oldPassword"
                        autocomplete="current-password"
                        class="mt-1 block w-full"
                        name="current_password"
                        type="password"
                        required
                    />
                </label>
            </div>

            <div>
                <label class="block font-medium text-sm text-base-content/90" for="password"> New
                    Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <MdKey class="h-4 w-4 opacity-70"/>
                    <input
                        id="update-password"
                        v-model="password"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        name="password"
                        type="password"
                        required
                    />
                </label>
            </div>

            <div>
                <label class="block font-medium text-sm text-base-content/90" for="password_confirmation">
                    Confirm Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <MdKey class="h-4 w-4 opacity-70"/>
                    <input
                        id="update-password_confirmation"
                        v-model="passwordConfirm"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        name="password_confirmation"
                        type="password"
                        required
                    />
                </label>
            </div>

            <ErrorAlert v-if="error" :message="error" class="mt-2"/>

            <div class="flex items-center gap-4">
                <button :disabled="processing" class="btn btn-primary">Save</button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="recentlySuccessful" class="text-sm text-success font-bold">Saved successfully.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
