<script setup lang="ts">
import {ref} from 'vue';
import {Link} from "@inertiajs/vue3";

function AddRole() {
    alert('Add Role button clicked!');
}

function KickMember() {
}

function handleCancel() {

}

function handleSave() {
    alert('Settings saved!');
}

</script>

<template>

    <!-- navbar -->
    <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl text-white mb-6">Server Settings</h1> <!-- Server Icon and Name Section -->
        <div class="bg-gray-800 p-6 rounded-lg mb-8">
            <div class="flex items-center ">
                <label for="serverIcon" class="relative cursor-pointer">
                    <div
                        class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-600 flex justify-center items-center transition-all duration-300 ease-in-out hover:bg-transparent">
                        <span class="text-4xl text-gray-500">+</span>
                    </div>
                    <input
                        id="serverIcon"
                        type="file"
                        class="hidden"
                        accept="image/png, image/jpeg"
                    />
                </label>

                <div class="ml-auto">
                    <label for="serverName" class="text-white">Server Name</label>
                    <input
                        type="text"
                        id="serverName"
                        class="input input-bordered w-full mt-2 bg-gray-600 text-white"
                        placeholder="Enter your server name"
                    />
                </div>
            </div>
        </div>

        <!-- Other Settings Section -->
        <div class="bg-gray-800 p-6 rounded-lg mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xl text-white">Allow Attachments</span>
                    <p class="text-sm text-gray-300">Let's you send images and videos in chat.</p>
                </div>
                <input type="checkbox" class="toggle toggle-primary"/>
            </div>
        </div>
        <div class="bg-gray-800 p-6 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xl text-white">Other function</span>
                    <p class="text-sm text-gray-300">idk, add smth here...</p>
                </div>
                <input type="checkbox" class="toggle toggle-primary"/>
            </div>
        </div>
    </div>
</template>
