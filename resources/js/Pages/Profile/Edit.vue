<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {Head} from '@inertiajs/vue3';
defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

function exportTab(){
    window.open(route('profile.export', '_blank'))
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-2">Export data</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 my-2">
                        Data export includes server and user data for related user.
                    </p>
                    <button class="btn" @click="exportTab">
                        Export data
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
