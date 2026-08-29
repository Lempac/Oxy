<script lang="ts" setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import pb from '@/pocketbase';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdKey } from 'vue-icons-plus/md';

const router = useRouter();
const passwordInput = ref<HTMLInputElement | null>(null);
const password = ref('');
const processing = ref(false);
const error = ref<string | null>(null);
const modalRef = ref<HTMLDialogElement | null>(null);

const toggleModal = (action: 'open' | 'close') => {
    if (modalRef.value) {
        if (action === 'open') modalRef.value.showModal();
        else modalRef.value.close();
    }
};

const deleteUser = async () => {
    if (!pb.authStore.model?.id) return;
    processing.value = true;
    error.value = null;
    try {
        await pb.collection('users').delete(pb.authStore.model.id, { requestKey: null });
        pb.authStore.clear();
        toggleModal('close');
        router.push('/');
    } catch (err: unknown) {
        error.value = (err as { message?: string })?.message || 'Failed to delete account.';
        passwordInput.value?.focus();
    } finally {
        processing.value = false;
        password.value = '';
    }
};
</script>

<template>
    <section class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <span class="font-semibold text-base-content">Delete Account</span>
                <p class="text-sm text-base-content/70 mt-1">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            </div>
            <button class="btn btn-error px-6" @click="toggleModal('open')">Delete Account</button>
        </div>
        <dialog id="my_modal_2" ref="modalRef" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold text-base-content">
                    Are you sure you want to delete your account?
                </h3>

                <p class="mt-2 text-sm text-base-content/70">
                    Once your account is deleted, all of its resources and data will be permanently deleted.
                    Please enter your password to confirm you would like to permanently delete your account.
                </p>

                <div class="mt-6">
                    <label class="label"><span class="label-text font-medium text-base-content">Password</span></label>
                    <label class="input input-bordered flex items-center gap-2 w-full">
                        <MdKey class="h-4 w-4 opacity-70"/>
                        <input
                            id="delete-password"
                            ref="passwordInput"
                            v-model="password"
                            autocomplete="current-password"
                            class="grow text-base-content"
                            name="password"
                            placeholder="Enter your password"
                            type="password"
                            @keyup.enter="deleteUser"
                        />
                    </label>
                    <ErrorAlert v-if="error" :message="error" class="mt-2"/>
                </div>

                <div class="modal-action mt-6">
                    <button class="btn" type="button" @click="toggleModal('close')">Cancel</button>

                    <button
                        :class="{ 'opacity-25': processing }" :disabled="processing"
                        class="btn btn-error"
                        type="button"
                        @click="deleteUser"
                    >
                        Delete Account
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="toggleModal('close')">close</button>
            </form>
        </dialog>
    </section>
</template>
