<template>
    <div class="kanban-board-container">
      <h1>Kanban Board</h1>
      
      <div class="kanban-column" v-for="(column, index) in columns" :key="index">
        <h2>{{ column.name }}</h2>
        <div class="kanban-tasks">
          <div v-for="task in getTasksForColumn(column.id)" :key="task.id" class="kanban-task">
            <p>{{ task.title }}</p>
            <small>{{ task.description }}</small>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed } from 'vue';
  
  export default {
    setup() {
      const columns = ref([
        { id: 'todo', name: 'To Do' },
        { id: 'doing', name: 'Doing' },
        { id: 'done', name: 'Done' }
      ]);
  
      const tasks = ref([
        { id: 1, title: 'Task 1', description: 'Description 1', column: 'todo' },
        { id: 2, title: 'Task 2', description: 'Description 2', column: 'doing' },
        { id: 3, title: 'Task 3', description: 'Description 3', column: 'done' },
      ]);
  
      const getTasksForColumn = (columnId) => {
        return tasks.value.filter(task => task.column === columnId);
      };
  
      return {
        columns,
        getTasksForColumn,
      };
    }
  }
  </script>
  
  <style scoped>
    .kanban-board-container {
      padding: 20px;
      text-align: center;
    }
    .kanban-column {
      display: inline-block;
      width: 300px;
      margin: 0 15px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .kanban-tasks {
      margin-top: 10px;
    }
    .kanban-task {
      background-color: #f4f4f4;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 5px;
    }
  </style>
  