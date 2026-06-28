<script lang="ts" setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {joinServer} from "@/bootstrap";
import {ref} from "vue";
import {Server, Channel} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";

const code = ref<HTMLInputElement>();

defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    inviteCode?: string
}>();

const val = ref<[number, string?] | undefined>();

</script>

<template>
    <Head title="Home"/>
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    v-if="selectedServer?.description"
                    class="card bg-base-100 mb-3 shadow-sm sm:rounded-lg">
                    <span class="card-body">{{ selectedServer?.description }}</span>
                </div>
                <div class="bg-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between p-6 text-base-content">
                        <span>Join a server!</span>
                        <div class="join flex">
                            <input ref="code" class="input input-bordered join-item" placeholder="Enter code"/>
                            <button class="btn join-item ml-2" @click="async () => val = await joinServer(code!.value)">
                                Join
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="val && val[0] === 200" class="alert alert-success mt-3">
                    <span>{{ val[1] }}</span>
                </div>
                <ErrorAlert v-if="val && val[0] !== 200" :message="val[1]" class="mt-3"/>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
