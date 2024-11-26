<script setup lang="ts">
import {router} from '@inertiajs/vue3';
import {ref} from 'vue';

defineProps<{
    board: Object
}>();

const boardName = ref(props.board.name);
const boardBio = ref(props.board.bio || "");

const handleEditBoard = () => {
    router.put(route('kanban.show', {board: props.board.id}), {name: boardName.value, bio: boardBio.value}, {
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

        <form @submit.prevent="handleEditBoard" class="form-container">
            <div class="form-group">
                <label for="board-name" class="form-label">Board Name:</label>
                <input type="text" v-model="boardName" id="board-name" required/>
            </div>

            <div class="form-group">
                <label for="board-bio" class="form-label">Board's Bio:</label>
                <textarea v-model="boardBio" id="board-bio" rows="4"></textarea>
            </div>

            <button type="submit" class="submit-btn">Save Changes</button>
        </form>

        <button @click="goBack" class="back-btn">Back to Boards</button>
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
