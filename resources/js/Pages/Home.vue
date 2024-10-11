<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';

</script>

<template>
    <Head title="Home"/>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between p-6 text-gray-900 dark:text-gray-100">
                        <span>Join a server!</span>
                        <div class="join flex">
                            <input class="input input-bordered join-item" placeholder="Search"/>
                            <button class="btn join-item ml-2">Join</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
