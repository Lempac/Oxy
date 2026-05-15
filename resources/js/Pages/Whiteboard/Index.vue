<script lang="ts" setup>
import { create, destroy, edit, show } from '@/routes/whiteboard';
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { Whiteboard } from "@/types";

const loading = ref(true);
const currentWhiteboards = ref<Whiteboard[]>([]);

const { whiteboards } = defineProps<{
    whiteboards: Whiteboard[];
}>()

onMounted(() => {
    if (whiteboards) {
        currentWhiteboards.value = whiteboards;
    }
    loading.value = false;
});

const createWhiteboard = () => {
    router.get(create.url());
};

const editWhiteboard = (id: number) => {
    router.get(edit.url(id));
};

const viewWhiteboard = (id: number) => {
    router.get(show.url(id));
};

const deleteWhiteboard = (id: number) => {
    router.delete(destroy.url(id), {
        onSuccess: () => {
            currentWhiteboards.value = currentWhiteboards.value.filter(w => w.id !== id);
        }
    });
};
</script>

<template>
    <div class="whiteboard-container">
        <h1 class="title">Whiteboards</h1>

        <div v-if="loading">Loading...</div>

        <template v-else>
            <div v-for="wb in currentWhiteboards" :key="wb.id" class="board-item">
                <button class="board-link" @click="viewWhiteboard(wb.id)">{{ wb.name }}</button>
                <div class="actions">
                    <button class="edit-btn" @click="editWhiteboard(wb.id)">Edit</button>
                    <button class="delete-btn" @click="deleteWhiteboard(wb.id)">Delete</button>
                </div>
            </div>

            <p v-if="currentWhiteboards.length === 0">No whiteboards available. Click the "+" button to create a new one!</p>
            <button class="add-board-btn" @click="createWhiteboard">+</button>
        </template>
    </div>
</template>

<style scoped>
.whiteboard-container {
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
    width: 400px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.board-link {
    text-decoration: none;
    color: inherit;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    font-size: 1.1rem;
}

.actions {
    display: flex;
    gap: 10px;
}

.edit-btn {
    background-color: #2196F3;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.delete-btn {
    background-color: #f44336;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
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
</style>
