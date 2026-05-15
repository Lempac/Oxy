<script lang="ts" setup>
import { onMounted, onUnmounted, ref } from 'vue';
import * as Y from 'yjs';
import { WebsocketProvider } from 'y-websocket';
import { Whiteboard as WhiteboardType } from '@/types';
import { save } from '@/routes/whiteboard';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { index } from '@/routes/whiteboard';

const props = defineProps<{
    whiteboard: WhiteboardType;
}>();

const stageConfig = ref({
    width: window.innerWidth,
    height: window.innerHeight - 100,
});

const lines = ref<any[]>([]);
const isDrawing = ref(false);
const isConnected = ref(false);

// Yjs setup
const ydoc = new Y.Doc();
const ylines = ydoc.getArray<any>('lines');
const provider = new WebsocketProvider('ws://localhost:1234', `whiteboard-${props.whiteboard.id}`, ydoc);

onMounted(() => {
    // Sync Yjs array with local ref
    ylines.observe(() => {
        lines.value = ylines.toArray();
    });

    provider.on('status', (event: any) => {
        isConnected.value = event.status === 'connected';
    });

    // Handle initial state if provided by server
    if (props.whiteboard.state && ylines.length === 0) {
        try {
            const initialState = JSON.parse(props.whiteboard.state);
            ylines.push(initialState);
        } catch (e) {
            console.error("Failed to parse initial state", e);
        }
    }

    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    provider.destroy();
    ydoc.destroy();
    window.removeEventListener('resize', handleResize);
});

const handleResize = () => {
    stageConfig.value.width = window.innerWidth;
    stageConfig.value.height = window.innerHeight - 100;
};

const handleMouseDown = (e: any) => {
    isDrawing.value = true;
    const pos = e.target.getStage().getPointerPosition();
    ylines.push([{
        tool: 'brush',
        points: [pos.x, pos.y],
        stroke: '#df4b26',
        strokeWidth: 5,
    }]);
};

const handleMouseMove = (e: any) => {
    if (!isDrawing.value) return;

    const stage = e.target.getStage();
    const point = stage.getPointerPosition();

    // Update the last line
    const lastLine = ylines.get(ylines.length - 1) as any;
    if (!lastLine) return;

    const newPoints = lastLine.points.concat([point.x, point.y]);

    ylines.delete(ylines.length - 1);
    ylines.push([{
        ...lastLine,
        points: newPoints,
    }]);
};

const handleMouseUp = () => {
    isDrawing.value = false;
    saveState();
};

const saveState = async () => {
    const state = JSON.stringify(ylines.toArray());
    try {
        await axios.post(save.url(props.whiteboard.id), { state });
    } catch (e) {
        console.error("Failed to save whiteboard state", e);
    }
};

const goBack = () => {
    router.get(index.url());
};

const clearBoard = () => {
    ylines.delete(0, ylines.length);
    saveState();
};
</script>

<template>
    <div class="whiteboard-page">
        <div class="toolbar">
            <h1>{{ whiteboard.name }}</h1>
            <button class="btn" @click="goBack">Back</button>
            <button class="btn btn-warning" @click="clearBoard">Clear</button>
            <span class="status" :class="{ 'connected': isConnected }">
                {{ isConnected ? 'Connected' : 'Connecting...' }}
            </span>
        </div>

        <div class="canvas-container">
            <v-stage
                :config="stageConfig"
                @mousedown="handleMouseDown"
                @mousemove="handleMouseMove"
                @mouseup="handleMouseUp"
            >
                <v-layer>
                    <v-line
                        v-for="(line, idx) in lines"
                        :key="idx"
                        :config="{
                            stroke: line.stroke,
                            strokeWidth: line.strokeWidth,
                            points: line.points,
                            lineCap: 'round',
                            lineJoin: 'round',
                            tension: 0.5
                        }"
                    />
                </v-layer>
            </v-stage>
        </div>
    </div>
</template>

<style scoped>
.whiteboard-page {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

.toolbar {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 10px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.toolbar h1 {
    margin: 0;
    font-size: 1.5rem;
}

.canvas-container {
    flex-grow: 1;
    background-color: white;
    cursor: crosshair;
}

.btn {
    padding: 5px 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
    cursor: pointer;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
}

.status {
    font-size: 0.8rem;
    color: #666;
}

.status.connected {
    color: green;
}

.connected::before {
    content: '● ';
}
</style>
