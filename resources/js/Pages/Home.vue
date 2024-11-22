<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {joinServer} from "@/bootstrap";
import {ref} from "vue";
import {Server} from "@/types";
import ErrorAlert from "@/Components/ErrorAlert.vue";

const code = ref<HTMLInputElement>();

defineProps<{
    servers: Server[],
    selected_server?: Server,
    invite_code?: string
}>();

const val = ref<[number, string?] | undefined>();

</script>

<template>
    <Head title="Home"/>
    <AuthenticatedLayout :servers :selected_server :invite_code>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="card bg-white dark:bg-gray-800 mb-3 shadow-sm sm:rounded-lg" v-if="selected_server?.description">
                    <span class="card-body">{{ selected_server?.description }}</span>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between p-6 text-gray-900 dark:text-gray-100">
                        <span>Join a server!</span>
                        <div class="join flex">
                            <input ref="code" class="input input-bordered join-item" placeholder="Enter code"/>
                            <button @click="async () => val = await joinServer(code!.value)" class="btn join-item ml-2">
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
