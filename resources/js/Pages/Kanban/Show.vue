<template>
    <div>
        <h1>{{ board.name }}</h1>
        <Column v-for="column in board.columns" :key="column.id" :column="column" />
        <CreateColumn :boardId="board.id" />
    </div>
</template>

<script>
import { ref } from 'vue';
import Column from '../Columns/Column.vue';
import CreateColumn from '../Columns/CreateColumn.vue';
import { usePage } from '@inertiajs/vue3';

export default {
    components: { Column, CreateColumn },
    setup() {
        const { props } = usePage();
        const board = ref(props.value.board);

        return { board };
    },
};
</script>
