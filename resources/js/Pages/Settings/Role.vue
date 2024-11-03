<script setup lang="ts">
import { defineProps, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

// Define the role type
interface Role {
  id: number;
  name: string;
  color: string;
  importance: number;
}

const props = defineProps<{
  server: {
    id: number;
    name: string;
  };
}>();

const roles = ref<Role[]>([]);
const newRoleName = ref('');
const newRoleColor = ref('#ffffff');
const newRoleImportance = ref(1);
const editingRole = ref<Role | null>(null);
const isModalOpen = ref(false);

const fetchRoles = async () => {
  try {
    const response = await axios.get(`/api/servers/${props.server.id}/roles`);
    roles.value = response.data.roles || []; // Default to empty array if undefined

    // Sort roles by importance
    roles.value.sort((a, b) => a.importance - b.importance);
  } catch (error) {
    console.error('Error fetching roles:', error);
  }
};

const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  newRoleName.value = '';
  newRoleColor.value = '#ffffff'; // Reset color
  newRoleImportance.value = 1; // Reset importance
};

const editRole = (role: Role) => {
  editingRole.value = role;
  newRoleName.value = role.name;
  newRoleColor.value = role.color;
  newRoleImportance.value = role.importance; // Set importance for editing
};

const updateRole = async () => {
  const currentEditingRole = editingRole.value;

  if (currentEditingRole) {
    const response = await axios.put(`/api/servers/${props.server.id}/roles/${currentEditingRole.id}`, {
      name: newRoleName.value,
      color: newRoleColor.value,
      importance: newRoleImportance.value, // Send updated importance
    });

    const index = roles.value.findIndex(r => r.id === currentEditingRole.id);
    roles.value[index] = response.data.role;

    editingRole.value = null; 
    closeModal(); // Reset modal state
  }
};

const addRole = async () => {
  try {
    const response = await axios.post(`/api/servers/${props.server.id}/roles`, {
      name: newRoleName.value,
      color: newRoleColor.value,
      perms: Math.floor(Math.random() * 100),
      importance: newRoleImportance.value,
    });
    closeModal();
    await fetchRoles(); // Refresh the roles list after adding
  } catch (error) {
    console.error('Error adding role:', error);
  }
};

const deleteRole = async (role: Role) => {
  try {
    await axios.delete(`/api/servers/${props.server.id}/roles/${role.id}`);
    // Remove the role from the local state after deletion
    roles.value = roles.value.filter(r => r.id !== role.id);
  } catch (error) {
    console.error('Error deleting role:', error);
  }
};


const updateColor = (event: Event) => {
  const target = event.target as HTMLInputElement;
  newRoleColor.value = target.value;
};
const openColorPicker = () => {
  const colorInput = document.getElementById('newRoleColor') as HTMLInputElement;
  colorInput.click();
};
// Fetch roles when component mounts
fetchRoles();
</script>

<template>
  <div class="flex flex-col items-center justify-center">
    <div class="w-full max-w-6xl p-6">
      <!-- Navbar for Navigation -->
      <div class="navbar bg-gray-800 text-white rounded-lg mb-6 py-4 px-6">
        <div class="flex-1">
          <h1 class="text-2xl truncate" :title="server.name" style="max-width: 50%;">{{ server.name }}</h1>
        </div>
        <div class="flex space-x-6">
          <Link :href="route('settings.server', { serverId: server.id })" class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
              Server
          </Link>
          <Link :href="route('settings.role', { id: server.id })" class="text-lg text-white transition-all duration-300 ease-in-out hover:bg-gray-700 hover:pl-6 hover:pr-6 p-2 rounded-lg btn btn-neutral">
              Roles
          </Link>
        </div>
      </div>

      <div class="flex justify-end mb-6 space-x-4">
        <Link :href="route('home.server', { server: props.server.id })" class="btn btn-circle bg-red-500">X</Link>
      </div>

      <!-- Role Settings Content Section -->
      <div class="bg-gray-700 p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl text-white mb-6">Role Settings</h1>

        <!-- Button to Open Modal -->
        <button @click="openModal" class="btn mb-6">Add Role</button>

        <!-- Roles Table -->
        <table class="min-w-full bg-gray-100 rounded-lg">
          <thead class="bg-gray-500">
            <tr>
              <th class="py-2 px-4 text-left text-white">Role Name</th>
              <th class="py-2 px-4 text-left text-white" v-if="editingRole">Color</th>
              <th class="py-2 px-4 text-left text-white">Importance</th>
              <th class="py-2 px-4 text-white text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles" :key="role.id">
              <td class="py-2 px-4" :style="{ color: editingRole === role ? newRoleColor : role.color }">
                <span v-if="editingRole !== role">{{ role.name }}</span>
                <input v-if="editingRole === role" v-model="newRoleName" type="text" class="p-1 text-black bg-white" />
              </td>
              <td v-if="editingRole === role" class="py-2 px-4">
                <input v-model="newRoleColor" type="color" class="ml-2 bg-transparent" />
              </td>
              <td class="py-2 px-4 text-black">
                <span v-if="editingRole !== role">{{ role.importance }}</span>
                <input v-if="editingRole === role" v-model="newRoleImportance" type="number" min="1" max="100" class="p-1 text-black" />
              </td>
              <td class="py-2 px-4 text-right">
                <div class="flex justify-end space-x-2">
                  <button v-if="editingRole !== role" @click="editRole(role)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</button>
                  <button v-if="editingRole === role" @click="updateRole" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">Save</button>
                  <button @click="deleteRole(role)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Modal for Adding Role -->
       <div v-if="isModalOpen" class="modal modal-open">
          <div class="modal-box">
            <h2 class="text-lg font-bold">Add New Role</h2>
            <div class="form-control">
              <label class="label" for="newRoleName">Role Name</label>
              <input
                id="newRoleName"
                type="text"
                v-model="newRoleName"
                required
                class="input input-bordered grow"
              />
            </div>
            <div class="form-control h-auto w-auto">
            <label class="label" for="newRoleColor">Color</label>
            <div 
              class="inline-block h-10 w-10 rounded-full border border-gray-300 cursor-pointer" 
              :style="{ backgroundColor: newRoleColor }"
              @click="openColorPicker"
            ></div>
            <input
              id="newRoleColor"
              type="color"
              v-model="newRoleColor"
              class="hidden" 
              @input="updateColor"
            />
          </div>
            <div class="form-control">
              <label class="label" for="newRoleImportance">Importance</label>
              <input
                id="newRoleImportance"
                type="number"
                v-model="newRoleImportance"
                min="1"
                max="100"
                required
                class="input input-bordered grow"
              />
            </div>
            <div class="modal-action">
              <button @click="addRole" class="btn btn-success">Add Role</button>
              <button @click="closeModal" class="btn btn-error">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>