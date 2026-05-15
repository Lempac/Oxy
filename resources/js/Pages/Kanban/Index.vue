<script lang="ts" setup>
import { create, destroy, edit, show } from '@/routes/kanban';
import {router} from '@inertiajs/vue3';
import {onMounted, ref} from 'vue';
import {Board, BoardColumn} from "@/types";


const loading = ref(true);
const currentBoards = ref<Board[]>([]);
const selectedBoard = ref<Board>();
const columns = ref<BoardColumn[]>([]);

const {boards} = defineProps<{
    boards: Board[];
    board: Board;
}>()

onMounted(() => {
    if (boards) {
        currentBoards.value = boards;
    }
    loading.value = false;
});

const createBoard = () => {
    router.get(create.url());
};

const editBoard = (boardId: number) => {
    router.get(edit.url(boardId));
};

const viewBoard = (board: Board) => {
    router.get(show.url(board.id));
};


const deleteBoard = (boardId: number) => {
    router.delete(destroy.url(boardId), {
        onSuccess: () => {
            currentBoards.value = currentBoards.value.filter(board => board.id !== boardId);
        }
    });
};

const selectBoard = (board: Board) => {
    selectedBoard.value = board;
    loading.value = true;

    router.get(show.url(board.id), {}, {
        onSuccess: () => {
            columns.value = board.columns.map(column => {
                return {
                    ...column,
                    tasks: column.tasks || []
                };
            });
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        }
    });
};

const goBack = () => {
    selectedBoard.value = undefined;
};
</script>

<template>
    <div class="kanban-container">
        <h1 class="title">Kanban Boards</h1>

        <div v-if="loading">Loading...</div>

        <template v-else-if="!selectedBoard">
            <div v-for="board in boards" :key="board.id" class="board-item">
                <button class="board-link" @click="selectBoard(board)">{{ board.name }}</button>
                <button class="edit-btn" @click="editBoard(board.id)">Edit</button>
                <button class="delete-btn" @click="deleteBoard(board.id)">Delete</button>
            </div>

            <p v-if="boards.length === 0">No boards available. Click the "+" button to create a new board!</p>
            <button class="add-board-btn" @click="createBoard">+</button>
        </template>

        <template v-else>
            <h2>{{ selectedBoard.name }}</h2>
            <button class="back-btn" @click="goBack">Back to Boards</button>

            <div class="columns-container">
                <div v-for="column in columns" :key="column.id" class="column-item">
                    <h3>{{ column.name }}</h3>
                    <p v-if="column.tasks && column.tasks.length === 0">No tasks yet.</p>
                    <div v-else>
                        <div v-for="task in column.tasks" :key="task.id" class="task-item">
                            {{ task.title }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>


<style scoped>
.kanban-container {
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


.board-item {
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 300px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.board-link {
    text-decoration: none;
    color: #ffffff;
    font-weight: bold;
    cursor: pointer;
}

.board-item button {
    margin-left: 10px;
    padding: 5px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.edit-btn {
    background-color: #2196F3;
    color: white;
}

.edit-btn:hover {
    background-color: #1e88e5;
}

.delete-btn {
    background-color: #f44336;
    color: white;
}

.delete-btn:hover {
    background-color: #e53935;
}

.add-board-btn {
    margin-top: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #4CAF50;
    color: white;
    font-size: 24px;
    border: none;
    cursor: pointer;
}

.add-board-btn:hover {
    background-color: #45a049;
}
</style>
