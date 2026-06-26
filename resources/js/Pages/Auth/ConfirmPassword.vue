<script lang="ts" setup>
import { confirm } from '@/routes/password';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { MdKey } from 'vue-icons-plus/md';


const form = useForm({
    password: '',
});

const submit = () => {
    form.post(confirm.url(), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password"/>

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
                        v-model="form.password"
                        autocomplete="current-password"
                        autofocus
                        class="mt-1 block w-full"
                        required
                        type="password"
                    />
                </label>

                <ErrorAlert :message="form.errors.password" class="mt-2"/>
            </div>

            <div class="flex justify-end mt-4">
                <button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="btn ms-4">
                    Confirm
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
