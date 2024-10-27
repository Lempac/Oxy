<template>
    <div class="kanban-container">
        <h1>Kanban Boards</h1>

        <div v-if="boards.length">
            <div v-for="board in boards" :key="board.id" class="board-item">
                <router-link :to="`/kanban/${board.id}`">{{ board.name }}</router-link>
            </div>
        </div>

        <p v-else>No boards available.</p>

        <!-- Button to add a new Kanban board -->
        <button @click="createBoard" class="add-board-btn">+</button>
    </div>
</template>

<script>
import {router, usePage} from '@inertiajs/vue3';

export default {
    setup() {
        const page = usePage();
        const boards = page.props.boards || [];

        // Method to navigate to the create board page
        const createBoard = () => {
            router.get('/kanban/create');
        };

        return { boards, createBoard };
    },
};
</script>

<style scoped>
.kanban-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    text-align: center;
}

.board-item {
    margin: 10px 0;
}

.add-board-btn {
    margin-top: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #4CAF50; /* Green background */
    color: white;
    font-size: 24px;
    border: none;
    cursor: pointer;
}

.add-board-btn:hover {
    background-color: #45a049; /* Darker green on hover */
}
</style>
