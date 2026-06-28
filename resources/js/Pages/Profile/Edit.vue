<script lang="ts" setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {Head} from '@inertiajs/vue3';
import { exportMethod } from '@/routes/profile';

import { Server } from '@/types';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    servers?: Server[];
}>();

function exportTab() {
    window.open(exportMethod.url(), '_blank')
}
</script>

<template>
    <Head title="Profile"/>

    <AuthenticatedLayout :servers="servers">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl"/>
                </div>

                <div class="sm:p-8 bg-base-100 shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl"/>
                    <h2 class="text-lg font-medium text-base-content mt-2">Export data</h2>
                    <p class="text-sm text-base-content/70 my-2">
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
