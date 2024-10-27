<template>
    <div class="task-card">
        <h3>{{ task.title }}</h3>
        <p>{{ task.description }}</p>
        <!-- Add actions for editing/deleting here -->
    </div>
</template>

<script>
export default {
    props: {
        task: Object,
    },
};
</script>
