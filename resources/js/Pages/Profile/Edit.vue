<script lang="ts" setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {Head, Link} from '@inertiajs/vue3';
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

    <div class="flex h-screen bg-base-100 overflow-hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <div class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-4xl mx-auto space-y-8 pb-20">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-base-content">User Profile Settings</h1>
                        <div class="flex space-x-3">
                            <Link href="/home" class="btn btn-neutral px-6">
                                ← Back to Home
                            </Link>
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Profile Information</h2>
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Update Password</h2>
                            <UpdatePasswordForm class="w-full"/>
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Export Data</h2>
                            <div class="flex justify-between items-center bg-base-100 p-4 rounded-xl border border-base-300">
                                <div>
                                    <span class="font-semibold text-base-content">Export Account Data</span>
                                    <p class="text-sm text-base-content/70 mt-1">Data export includes server and user data for related user.</p>
                                </div>
                                <button class="btn px-6" @click="exportTab">
                                    Export data
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-error/10 shadow-sm border border-error/20">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-error/20 pb-2 mb-4 text-error">Danger Zone</h2>
                            <DeleteUserForm class="w-full"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
