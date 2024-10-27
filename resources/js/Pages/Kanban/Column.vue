<template>
    <div>
        <h2>{{ column.name }}</h2>
        <Task v-for="task in column.tasks" :key="task.id" :task="task" />
        <CreateTask :columnId="column.id" />
    </div>
</template>

<script>
import Task from '../Tasks/Task.vue';
import CreateTask from '../Tasks/CreateTask.vue';

export default {
    props: {
        column: Object,
    },
    components: { Task, CreateTask },
};
</script>
