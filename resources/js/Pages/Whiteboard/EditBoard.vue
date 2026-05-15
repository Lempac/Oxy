<script lang="ts" setup>
import { useForm, router } from '@inertiajs/vue3';
import { update, index } from '@/routes/whiteboard';
import { Whiteboard } from '@/types';

const props = defineProps<{
    whiteboard: Whiteboard;
}>();

const form = useForm({
    name: props.whiteboard.name,
});

const submit = () => {
    form.put(update.url(props.whiteboard.id));
};

const cancel = () => {
    router.get(index.url());
};
</script>

<template>
    <div class="edit-container">
        <h1>Edit Whiteboard</h1>
        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" v-model="form.name" required type="text" />
                <div v-if="form.errors.name" class="error">{{ form.errors.name }}</div>
            </div>
            <div class="actions">
                <button :disabled="form.processing" type="submit">Update</button>
                <button type="button" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.edit-container {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}
.form-group {
    margin-bottom: 15px;
}
input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}
.actions {
    display: flex;
    justify-content: space-between;
}
.error {
    color: red;
    font-size: 0.8rem;
}
button[type="submit"] {
    background-color: #2196F3;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button[type="button"] {
    background-color: #ccc;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>
