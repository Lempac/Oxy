<script setup lang="ts">
import { ref } from 'vue';
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
    <div class="flex flex-col items-center justify-center">
      <div class="w-full max-w-6xl p-6">
        <!-- navbar -->
        <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
          <div class="flex-1">
            <h1 class="text-2xl">Server name</h1>
          </div>
          <div class="flex space-x-6">
          <Link :href="route('settings.server')" class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
            Server
          </Link>
          <Link :href="route('settings.role')" class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
            Roles
          </Link>
        </div>
        </div>

        <div class="flex justify-end mb-6 space-x-4">
          <button @click="handleSave" class="btn px-6">Save Changes</button>
        </div>

        <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
          <h1 class="text-3xl text-white mb-6">Server Settings</h1> <!-- Server Icon and Name Section -->
          <div class="bg-gray-800 p-6 rounded-lg mb-8">
            <div class="flex items-center ">
              <label for="serverIcon" class="relative cursor-pointer">
                <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-600 flex justify-center items-center transition-all duration-300 ease-in-out hover:bg-transparent">
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
              <input type="checkbox" class="toggle toggle-primary" />
            </div>
          </div>
          <div class="bg-gray-800 p-6 rounded-lg">
            <div class="flex justify-between items-center">
              <div>
                <span class="text-xl text-white">Other function</span>
                <p class="text-sm text-gray-300">idk, add smth here...</p>
              </div>
              <input type="checkbox" class="toggle toggle-primary" />
            </div>
          </div>
        </div>
      </div>
    </div>
</template>
