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
<!--  <Head title="Role Settings"></Head>-->
<!--  <body class="bg-gray-500 text-black dark:bg-gray-900 dark:text-white">-->
    <div class="flex flex-col items-center justify-center">
      <div class="w-full max-w-6xl p-6">

        <!-- Top Navigation Bar using DaisyUI Navbar -->
        <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
          <div class="flex-1">
            <h1 class="text-2xl">Server name</h1>
          </div>

          <!-- Buttons positioned towards the middle-right with hover expanding effect -->
          <div class="flex justify-end space-x-6 mr-16">
            <Link :href="route('settings.server')" class="text-lg transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg">
              Server
            </Link>
            <Link :href="route('settings.role')" class="text-lg transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg">
              Roles
            </Link>
          </div>
        </div>

        <!-- Save/Cancel Buttons -->
        <div class="flex justify-end mb-6 space-x-4">
          <button @click="handleSave" class="btn px-6">Save Changes</button>
        </div>

        <!-- Members Table Section -->
        <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
          <h1 class="text-3xl text-white mb-6">Role Settings</h1>

          <!-- Add Role Button -->
          <button @click="AddRole" class="btn mb-6">
            Add Role
          </button>

          <!-- Members Table -->
          <table class="min-w-full bg-white rounded-lg">
            <thead class="bg-gray-200">
              <tr>
                <th class="py-2 px-4 text-left">Member Name</th>
                <th class="py-2 px-4 text-left">Role</th>
                <th class="py-2 px-4 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="py-2 px-4">John Doe</td>
                <td class="py-2 px-4">Admin</td>
                <td class="py-2 px-4">
                  <button @click="KickMember" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                    Kick Member
                  </button>
                </td>
              </tr>
              <!-- Add more member rows here as needed -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
<!--  </body>-->
</template>
