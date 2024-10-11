<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

function AddRole() {
  alert('Add Role button clicked!');
}

function KickMember() {
}

function handleCancel() {
  window.location.href = '/dashboard';
}

function handleSave() {
  alert('Settings saved!');
}

function IconUpload() {
  }
</script>

<template>
  <Head title="Settings"></Head>
  <body>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
      <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#285aff] selection:text-white">
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
          <!-- Setting menu -->
          <div class="split left">
            <header class="text-center mt-20">
              <div id="navigation" class="flex flex-col lg:justify-center lg:col-start-2 ">
                <h1 class="text1">Server name</h1>
                <a href="/server-settings" class="rounded-sm hover:text-white hover:ml-10 hover:mr-10 focus:outline-none focus-visible:ring-1 focus-visible:ring-[#285aff] dark:hover:text-white dark:focus-visible:ring-[#285aff] text3">
                  Server
                </a>
                <a href="/role-settings" class="rounded-sm hover:text-white hover:ml-10 hover:mr-10 focus:outline-none focus-visible:ring-1 focus-visible:ring-[#285aff] dark:hover:text-white dark:focus-visible:ring-[#285aff] text3">
                  Roles
                </a>
              </div>
            </header>
          </div>

          <!-- Main (Settings) -->
          <div class="split right">
            <div class="absolute top-0 right-0 m-4 flex space-x-2">
              <PrimaryButton>
                Save Changes
              </PrimaryButton>
              <button @click="handleCancel" class="border border-white text-white px-4 py-2 rounded-full hover:bg-red-700 hover:border-red-700 hover:text-white focus:outline-none transition-colors duration-300 ease-in-out">
                X
              </button>
            </div>

            <main class="mt-6 flex justify-center items-start">
              <div id="settings-container" class="w-full max-w-4xl p-8 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)]">
                <h1 class="text2 text-1xl mb-6">Server Settings</h1>

                <!-- Server Icon and Name Section -->
                <div class="bg-gray-700 p-4 rounded-lg mb-4">
                  <div class="flex items-center mb-2 justify-between">
                    <!-- Server Icon Upload -->
                    <label for="serverIcon" class="relative cursor-pointer">
                      <div
                        class="flex justify-center items-center rounded-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700"
                        :style="{
                          width: '80px',
                          height: '80px',
                          backgroundImage: '',
                          backgroundSize: 'cover',
                          backgroundPosition: 'center',
                        }"
                      >
                        <!-- default -->
                        <span class="text-4xl text-gray-500">+</span>
                      </div>
                      <input
                        id="serverIcon"
                        type="file"
                        class="hidden"
                        accept="image/png, image/jpeg"
                        @change="IconUpload"
                      />
                    </label>

                    <!-- Server Name Input -->
                    <div class="mr-4">
                      <label for="serverName" class="text-white">Server Name</label>
                      <input
                        type="text"
                        id="serverName"
                        class="w-full mt-1 p-2 rounded bg-gray-700 text-white focus:outline-none focus:ring focus:ring-[#285aff]"
                        placeholder="Enter your server name"
                      />
                    </div>
                  </div>

                  <p class="text-white mb-5">Upload image</p>
                </div>
              <!-- Other Settings Section -->
              <div class="bg-gray-700 p-4 rounded-lg mb-4">
                <!-- Allow Attachments Section -->
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <span class="text-xl text-white">Allow Attachments</span>
                    <p class="text-sm text-gray-300">Let's you send images and videos in chat.</p>
                  </div>
                  <input type="checkbox" class="toggle toggle-primary" />
                </div>
              </div>

              <!-- Space between Settings -->

              <div class="bg-gray-700 p-4 rounded-lg mb-4">
                <!-- Other Function Section -->
                <div class="flex items-center justify-between">
                  <div>
                    <span class="text-xl text-white">Other function</span>
                    <p class="text-sm text-gray-300">idk, add smth here...</p>
                  </div>
                  <input type="checkbox" class="toggle toggle-primary" />
                </div>
              </div>

              </div>
            </main>

          </div>
        </div>
      </div>
    </div>
  </body>
</template>


<style scoped>
.split {
  height: 100%;
  position: fixed;
  z-index: 1;
  top: 0;
  overflow-x: hidden;
  padding-top: 20px;
}

.left {
  width: 20%;
  left: 0;
  background-color: #111;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  padding-top: 20px;
}

.right {
  width: 80%;
  right: 0;
  background-color: rgb(42, 42, 42);
}

#settings-container {
  width: 80%;
  max-width: 700px;
  margin-top: 20px;
  padding: 20px;
  background-color: gray;
  border-radius: 10px;
}

.text1 {
  color: lightblue;
  margin-top: 10px;
  text-decoration: underline;
  text-align: center;
}

.text2 {
  color: azure;
  text-align: left;
}

.text3 {
  color: aqua;
  margin-top: 5px;
  background-color: transparent;
  margin-left: 90px;
  margin-right: 90px;
  transition: margin 0.3s ease;
  text-align: center;
}

.text3:hover {
  margin-left: 10px;
  margin-right: 10px;
  background-color: rgb(50, 50, 50);
}
</style>
