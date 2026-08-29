<script lang="ts" setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Server } from '@/types';
import pb from '@/pocketbase';

defineProps<{
    status?: string;
    servers?: Server[];
}>();

function exportTab() {
    if (!pb.authStore.model) return;
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(pb.authStore.model, null, 2));
    const downloadAnchor = document.createElement('a');
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", `oxy_user_data_${pb.authStore.model.name || 'user'}.json`);
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
}
</script>

<template>
    <div class="flex h-screen bg-base-100 overflow-hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-base-100">
            <div class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-4xl mx-auto space-y-8 pb-20">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-base-content">User Profile Settings</h1>
                        <div class="flex space-x-3">
                            <router-link class="btn btn-neutral px-6" to="/home">
                                ← Back to Home
                            </router-link>
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Profile
                                Information</h2>
                            <UpdateProfileInformationForm
                                :status="status"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Update
                                Password</h2>
                            <UpdatePasswordForm class="w-full"/>
                        </div>
                    </div>

                    <div class="card bg-base-200 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-base-300 pb-2 mb-4 text-base-content">Export
                                Data</h2>
                            <div
                                class="flex justify-between items-center bg-base-100 p-4 rounded-xl border border-base-300">
                                <div>
                                    <span class="font-semibold text-base-content">Export Account Data</span>
                                    <p class="text-sm text-base-content/70 mt-1">Data export includes server and user
                                        data for related user.</p>
                                </div>
                                <button class="btn px-6" @click="exportTab">
                                    Export data
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-error/10 shadow-sm border border-error/20">
                        <div class="card-body">
                            <h2 class="card-title text-xl border-b border-error/20 pb-2 mb-4 text-error">Danger
                                Zone</h2>
                            <DeleteUserForm class="w-full"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
