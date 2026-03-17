<script lang="ts" setup>
import { index, store } from '@/routes/kanban';
import {router} from '@inertiajs/vue3';
import {ref} from 'vue';

const boardName = ref("");
const handleCreateBoard = () => {
    router.post(store.url(), {name: boardName.value}, {
        onSuccess: () => {
            router.get(index.url());
        }
    });
};

const goBack = () => {
    router.get(index.url());
};
</script>

<template>
    <div class="create-board-container">
        <h1 class="title">Create a New Kanban Board</h1>

        <form class="form-container" @submit.prevent="handleCreateBoard">
            <div class="form-group">
                <label class="form-label" for="board-name">Board Name:</label>
                <input id="board-name" v-model="boardName" required type="text"/>
            </div>

            <button class="submit-btn" type="submit">Create Board</button>
        </form>

        <button class="back-btn" @click="goBack">Back to Boards</button>
    </div>
</template>


<style scoped>
.create-board-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    text-align: center;
    padding: 20px;
}

.title {
    margin-bottom: 30px;
    margin-top: -40px;
}

.form-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

.form-label {
    margin-bottom: 10px;
    display: block;
}

input[type="text"] {
    padding: 10px;
    width: 300px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.submit-btn {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.submit-btn:hover {
    background-color: #45a049;
}

.back-btn {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #c00000;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.back-btn:hover {
    background-color: #b30000;
}
</style>
