<script lang="ts" setup>
import { destroy } from '@/routes/profile';
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdKey } from 'vue-icons-plus/md';


const passwordInput = ref<HTMLInputElement | null>(null);
const form = useForm({
    password: '',
});

const modalRef = ref<HTMLDialogElement | null>(null);

const toggleModal = (action: 'open' | 'close') => {
    if (modalRef.value) {
        if (action === 'open') modalRef.value.showModal();
        else modalRef.value.close();
    }
};

const deleteUser = () => {
    form.delete(destroy.url(), {
        preserveScroll: true,
        onSuccess: () => toggleModal('close'),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
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
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            class="grow text-base-content"
                            placeholder="Enter your password"
                            type="password"
                            @keyup.enter="deleteUser"
                        />
                    </label>
                    <ErrorAlert v-if="form.errors.password" :message="form.errors.password" class="mt-2"/>
                </div>

                <div class="modal-action mt-6">
                    <button class="btn" type="button" @click="toggleModal('close')">Cancel</button>

                    <button
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
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
