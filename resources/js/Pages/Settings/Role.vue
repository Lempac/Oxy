<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
              <div id="navigation" class="flex flex-col lg:justify-center lg:col-start-2">
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

            <!-- Display Members Table -->
            <main class="mt-6 flex justify-center items-start">
              <div id="members-container" class="w-full max-w-4xl p-8 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)]">
                <h1 class="text2 text-1xl mb-6">Role settings</h1>
                <<PrimaryButton @click="AddRole">
                  Add Role
                </PrimaryButton>
                <table class="min-w-full bg-white rounded-lg">
                  <thead class="bg-gray-200">
                    <tr>
                      <th class="py-2 px-4 text-left">Member Name</th>
                      <th class="py-2 px-4 text-left">Role</th>
                      <th class="py-2 px-4 text-left">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr> <!-- Display members here-->
                      <td class="py-2 px-4">
                        <button @click="KickMember" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                          Kick Member
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
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
  justify-content: flex-start; /* Align items to the top */
  align-items: center; /* Center horizontally */
  padding-top: 20px; /* Optional: Adds some spacing from the top */
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

.toggle {
  --toggle-background-color: transparent;
}

.text1 {
  color: lightblue;
  margin-top: 10px;
  text-decoration: underline;
  text-align: center; /* Ensures the text is centered */
}

.text2 {
  color: azure;
  text-align: left;
}

.text3 {
  color: aqua;
  margin-top: 5px;
  background-color: rgb(50, 50, 50);
  margin-left: 90px;
  margin-right: 90px;
  transition: margin 0.3s ease;
  text-align: center; /* Center links in smaller screens */
}

.text3:hover {
  margin-left: 10px;
  margin-right: 10px;
}

  </style>
