<script lang="ts" setup>
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import {MdKey} from "oh-vue-icons/icons";
import {addIcons} from "oh-vue-icons";

addIcons(MdKey);

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
    form.delete(route('profile.destroy'), {
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
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Account</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting
                your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <button class="btn btn-error" @click="toggleModal('open')">Delete Account</button>
        <dialog id="my_modal_2" ref="modalRef" class="modal">
            <div class="modal-box">
                <form class="modal-backdrop" method="dialog">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Are you sure you want to delete your account?
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Once your account is deleted, all of its resources and data will be permanently deleted.
                            Please
                            enter your password to confirm you would like to permanently delete your account.
                        </p>

                        <div class="mt-6">
                            <label class="block font-medium text-sm text-gray-600 dark:text-gray-400" for="password">Password</label>

                            <label class="input input-bordered flex items-center gap-2">
                                <v-icon class="h-4 w-4 opacity-70" name="md-key"/>
                                <input
                                    id="password"
                                    ref="passwordInput"
                                    v-model="form.password"
                                    class="mt-1 block w-3/4 text-white"
                                    placeholder="Password"
                                    type="password"
                                    @keyup.enter="deleteUser"
                                />
                            </label>

                            <ErrorAlert :message="form.errors.password" class="mt-2"/>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button class="btn" type="button" @click="toggleModal('close')">Cancel</button>

                            <button
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                class="btn btn-error ms-3"
                                type="button"
                                @click="deleteUser"
                            >
                                Delete Account
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </dialog>
    </section>
</template>
