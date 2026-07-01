<script lang="ts" setup>
import { update } from '@/routes/password';
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdKey } from 'vue-icons-plus/md';


const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
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
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        autocomplete="current-password"
                        class="mt-1 block w-full"
                        type="password"
                    />
                </label>

                <ErrorAlert :message="form.errors.current_password" class="mt-2"/>
            </div>

            <div>
                <label class="block font-medium text-sm text-base-content/90" for="password"> New
                    Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <MdKey class="h-4 w-4 opacity-70"/>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        type="password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password" class="mt-2"/>
            </div>

            <div>
                <label class="block font-medium text-sm text-base-content/90" for="password_confirmation">
                    Confirm Password </label>

                <label class="input input-bordered flex items-center gap-2">
                    <MdKey class="h-4 w-4 opacity-70"/>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        type="password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password_confirmation" class="mt-2"/>
            </div>

            <div class="flex items-center gap-4">
                <button :disabled="form.processing" class="btn">Save</button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-base-content/70">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
