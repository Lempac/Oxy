<script setup lang="ts">
defineProps<{
    title: String,
    description: String,
    cancel: (event: MouseEvent) => void,
    confirm: (event: MouseEvent) => void
}>();
</script>

<template>
    <!-- Overlay Background -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <!-- Dialog Box -->
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4" v-text="title"></h2>
            <p class="text-gray-700 mb-6" v-text="description"></p>
            <div class="flex justify-end space-x-4">
                <button @click="cancel" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button @click="confirm" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Yes
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Full-screen overlay */
.fixed {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Dialog box styling */
.bg-white {
    background-color: white;
}
</style>
