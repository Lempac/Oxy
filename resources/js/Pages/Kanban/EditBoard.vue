<script lang="ts" setup>
import {router} from '@inertiajs/vue3';
import {ref} from 'vue';
import {Board} from "@/types";

const {board} = defineProps<{
    board: Board
}>();

const boardName = ref(board.name);
const boardBio = ref(board.bio || "");

const handleEditBoard = () => {
    router.put(route('kanban.show', {board: board.id}), {name: boardName.value, bio: boardBio.value}, {
        onSuccess: () => {
            router.get(route('kanban.index'));
        }
    });
};

const goBack = () => {
    router.get(route('kanban.index'));
};
</script>

<template>
    <div class="edit-board-container">
        <h1 class="title">Edit Kanban Board</h1>

        <form class="form-container" @submit.prevent="handleEditBoard">
            <div class="form-group">
                <label class="form-label" for="board-name">Board Name:</label>
                <input id="board-name" v-model="boardName" required type="text"/>
            </div>

            <div class="form-group">
                <label class="form-label" for="board-bio">Board's Bio:</label>
                <textarea id="board-bio" v-model="boardBio" rows="4"></textarea>
            </div>

            <button class="submit-btn" type="submit">Save Changes</button>
        </form>

        <button class="back-btn" @click="goBack">Back to Boards</button>
    </div>
</template>


<style scoped>
.edit-board-container {
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

input[type="text"], textarea {
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
    background-color: #c30000;
}
</style>
