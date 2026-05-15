<script lang="ts" setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import * as Y from 'yjs';
import { WebsocketProvider } from 'y-websocket';
import { Whiteboard as WhiteboardType } from '@/types';
import { save } from '@/routes/whiteboard';
import axios from 'axios';
import { addIcons } from "oh-vue-icons";
import {
    HiSolidPencil,
    BiEraser,
    MdRectangleOutlined,
    MdCircleOutlined,
    MdHorizontalrule,
    MdUndo,
    MdRedo,
    MdDeleteoutline,
    MdAdsclick,
    MdFormatcolorfillOutlined
} from "oh-vue-icons/icons";

addIcons(HiSolidPencil, BiEraser, MdRectangleOutlined, MdCircleOutlined, MdHorizontalrule, MdUndo, MdRedo, MdDeleteoutline, MdAdsclick, MdFormatcolorfillOutlined);

const props = defineProps<{
    whiteboard: WhiteboardType;
}>();

const stageConfig = ref({
    width: 0,
    height: 0,
});

const container = ref<HTMLDivElement | null>(null);
const shapes = ref<any[]>([]);
const isConnected = ref(false);
const tool = ref<'pencil' | 'eraser' | 'rect' | 'circle' | 'line' | 'select'>('pencil');
const color = ref('#000000');
const fillColor = ref('transparent');
const strokeWidth = ref(2);
const selectedShapeIds = ref<string[]>([]);

const transformerRef = ref<any>(null);
const stageRef = ref<any>(null);

// Yjs setup
const ydoc = new Y.Doc();
const yshapes = ydoc.getMap<any>('shapes');
const undoManager = new Y.UndoManager(yshapes);

const wsUrl = import.meta.env.VITE_YJS_WS_URL || 'ws://localhost:1234';
const provider = new WebsocketProvider(wsUrl, `whiteboard-${props.whiteboard.id}`, ydoc);

onMounted(() => {
    if (container.value) {
        stageConfig.value.width = container.value.offsetWidth;
        stageConfig.value.height = container.value.offsetHeight;
    }

    yshapes.observe(() => {
        shapes.value = Array.from(yshapes.values());
    });

    provider.on('status', (event: any) => {
        isConnected.value = event.status === 'connected';
    });

    if (props.whiteboard.state && yshapes.size === 0) {
        try {
            const initialState = JSON.parse(props.whiteboard.state);
            Object.entries(initialState).forEach(([id, shape]) => {
                yshapes.set(id, shape);
            });
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
    if (container.value) {
        stageConfig.value.width = container.value.offsetWidth;
        stageConfig.value.height = container.value.offsetHeight;
    }
};

let currentShapeId: string | null = null;

const handleMouseDown = (e: any) => {
    const clickedOnEmpty = e.target === e.target.getStage();

    if (tool.value === 'select') {
        if (clickedOnEmpty) {
            selectedShapeIds.value = [];
            updateTransformer();
        }
        return;
    }

    const pos = e.target.getStage().getPointerPosition();
    currentShapeId = Math.random().toString(36).substring(7);

    let newShape: any = {
        id: currentShapeId,
        tool: tool.value,
        color: tool.value === 'eraser' ? '#ffffff' : color.value,
        fill: tool.value === 'rect' || tool.value === 'circle' ? (fillColor.value === 'transparent' ? null : fillColor.value) : null,
        strokeWidth: strokeWidth.value,
        points: [pos.x, pos.y],
        x: 0,
        y: 0,
    };

    if (['rect', 'circle'].includes(tool.value)) {
        newShape.x = pos.x;
        newShape.y = pos.y;
        newShape.width = 0;
        newShape.height = 0;
    } else if (tool.value === 'line') {
        newShape.points = [pos.x, pos.y, pos.x, pos.y];
    }

    yshapes.set(currentShapeId, newShape);
};

const handleMouseMove = (e: any) => {
    if (!currentShapeId) return;

    const pos = e.target.getStage().getPointerPosition();
    const shape = yshapes.get(currentShapeId);
    if (!shape) return;

    const updatedShape = { ...shape };

    if (tool.value === 'pencil' || tool.value === 'eraser') {
        updatedShape.points = updatedShape.points.concat([pos.x, pos.y]);
    } else if (tool.value === 'rect' || tool.value === 'circle') {
        updatedShape.width = pos.x - updatedShape.x;
        updatedShape.height = pos.y - updatedShape.y;
    } else if (tool.value === 'line') {
        updatedShape.points = [updatedShape.points[0], updatedShape.points[1], pos.x, pos.y];
    }

    yshapes.set(currentShapeId, updatedShape);
};

const handleMouseUp = () => {
    currentShapeId = null;
    saveState();
};

const handleTransformEnd = (e: any) => {
    const node = e.target;
    const id = node.id();
    const shape = yshapes.get(id);
    if (!shape) return;

    yshapes.set(id, {
        ...shape,
        x: node.x(),
        y: node.y(),
        scaleX: node.scaleX(),
        scaleY: node.scaleY(),
        rotation: node.rotation(),
    });
    saveState();
};

const handleDragEnd = (e: any) => {
    const id = e.target.id();
    const shape = yshapes.get(id);
    if (!shape) return;

    yshapes.set(id, {
        ...shape,
        x: e.target.x(),
        y: e.target.y(),
    });
    saveState();
};

const selectShape = (e: any, id: string) => {
    if (tool.value !== 'select') return;

    const isSelectable = tool.value === 'select';
    if (!isSelectable) return;

    const metaPressed = e.evt.shiftKey || e.evt.ctrlKey || e.evt.metaKey;
    const isSelected = selectedShapeIds.value.includes(id);

    if (!metaPressed && !isSelected) {
        selectedShapeIds.value = [id];
    } else if (metaPressed && isSelected) {
        selectedShapeIds.value = selectedShapeIds.value.filter((i) => i !== id);
    } else if (metaPressed && !isSelected) {
        selectedShapeIds.value = [...selectedShapeIds.value, id];
    }

    updateTransformer();
};

const updateTransformer = () => {
    const transformerNode = transformerRef.value?.getNode();
    if (!transformerNode) return;

    const stage = stageRef.value?.getStage();
    const nodes = selectedShapeIds.value
        .map(id => stage.findOne('#' + id))
        .filter(node => node !== undefined);

    transformerNode.nodes(nodes);
    transformerNode.getLayer().batchDraw();
};

const saveState = async () => {
    const state = JSON.stringify(Object.fromEntries(yshapes.entries()));
    try {
        await axios.post(save.url(props.whiteboard.id), { state });
    } catch (e) {
        console.error("Failed to save whiteboard state", e);
    }
};

const undo = () => undoManager.undo();
const redo = () => undoManager.redo();
const clear = () => {
    yshapes.clear();
    saveState();
};
const deleteSelected = () => {
    if (selectedShapeIds.value.length > 0) {
        selectedShapeIds.value.forEach(id => {
            yshapes.delete(id);
        });
        selectedShapeIds.value = [];
        updateTransformer();
        saveState();
    }
};
</script>

<template>
    <div class="whiteboard-container flex flex-col h-full bg-white">
        <!-- Toolbar -->
        <div class="toolbar flex items-center gap-4 p-2 border-b bg-gray-50 overflow-x-auto text-base-content">
            <div class="flex border-r pr-4 gap-1">
                <button @click="tool = 'select'" :class="{'btn-active': tool === 'select'}" class="btn btn-sm btn-ghost" title="Select">
                    <v-icon name="md-adsclick" />
                </button>
                <button @click="tool = 'pencil'" :class="{'btn-active': tool === 'pencil'}" class="btn btn-sm btn-ghost" title="Pencil">
                    <v-icon name="hi-solid-pencil" />
                </button>
                <button @click="tool = 'line'" :class="{'btn-active': tool === 'line'}" class="btn btn-sm btn-ghost" title="Line">
                    <v-icon name="md-horizontalrule" />
                </button>
                <button @click="tool = 'rect'" :class="{'btn-active': tool === 'rect'}" class="btn btn-sm btn-ghost" title="Rectangle">
                    <v-icon name="md-rectangle-outlined" />
                </button>
                <button @click="tool = 'circle'" :class="{'btn-active': tool === 'circle'}" class="btn btn-sm btn-ghost" title="Circle">
                    <v-icon name="md-circle-outlined" />
                </button>
                <button @click="tool = 'eraser'" :class="{'btn-active': tool === 'eraser'}" class="btn btn-sm btn-ghost" title="Eraser">
                    <v-icon name="bi-eraser" />
                </button>
            </div>

            <div class="flex border-r pr-4 gap-2 items-center">
                <div class="flex flex-col items-center">
                    <span class="text-[10px] leading-none mb-1">Stroke</span>
                    <input type="color" v-model="color" class="w-8 h-8 cursor-pointer border-none p-0 bg-transparent" title="Stroke Color" />
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-[10px] leading-none mb-1">Fill</span>
                    <div class="flex items-center gap-1">
                        <input type="color" v-model="fillColor" :disabled="fillColor === 'transparent'" class="w-8 h-8 cursor-pointer border-none p-0 bg-transparent disabled:opacity-30" title="Fill Color" />
                        <button @click="fillColor = fillColor === 'transparent' ? '#ffffff' : 'transparent'" :class="{'btn-active': fillColor === 'transparent'}" class="btn btn-xs btn-ghost p-0 min-h-0 h-8 w-8" title="Toggle Transparency">
                             <v-icon name="md-formatcolorfill-outlined" scale="0.8" :class="{'text-error': fillColor === 'transparent'}" />
                        </button>
                    </div>
                </div>
                <select v-model="strokeWidth" class="select select-sm select-bordered ml-2" title="Stroke Width">
                    <option :value="1">1px</option>
                    <option :value="2">2px</option>
                    <option :value="5">5px</option>
                    <option :value="10">10px</option>
                </select>
            </div>

            <div class="flex border-r pr-4 gap-1">
                <button @click="undo" class="btn btn-sm btn-ghost" title="Undo">
                    <v-icon name="md-undo" />
                </button>
                <button @click="redo" class="btn btn-sm btn-ghost" title="Redo">
                    <v-icon name="md-redo" />
                </button>
            </div>

            <div class="flex gap-1">
                <button @click="deleteSelected" :disabled="selectedShapeIds.length === 0" class="btn btn-sm btn-ghost text-error" title="Delete Selected">
                    <v-icon name="md-deleteoutline" />
                </button>
                <button @click="clear" class="btn btn-sm btn-ghost text-error" title="Clear All">
                    Clear
                </button>
            </div>

            <div class="ml-auto flex items-center gap-2 pr-2">
                <span class="status-dot" :class="isConnected ? 'bg-success' : 'bg-warning'"></span>
                <span class="text-xs">{{ isConnected ? 'Live' : 'Connecting...' }}</span>
            </div>
        </div>

        <!-- Canvas Area -->
        <div ref="container" class="canvas-area flex-grow relative overflow-hidden cursor-crosshair" :class="{'cursor-default': tool === 'select'}">
            <v-stage
                ref="stageRef"
                :config="stageConfig"
                @mousedown="handleMouseDown"
                @mousemove="handleMouseMove"
                @mouseup="handleMouseUp"
            >
                <v-layer>
                    <template v-for="shape in shapes" :key="shape.id">
                        <!-- Line / Pencil -->
                        <v-line
                            v-if="shape.tool === 'pencil' || shape.tool === 'eraser' || shape.tool === 'line'"
                            :config="{
                                id: shape.id,
                                points: shape.points,
                                stroke: shape.color,
                                strokeWidth: shape.strokeWidth,
                                lineCap: 'round',
                                lineJoin: 'round',
                                tension: shape.tool === 'line' ? 0 : 0.5,
                                draggable: tool === 'select',
                                x: shape.x || 0,
                                y: shape.y || 0,
                                scaleX: shape.scaleX || 1,
                                scaleY: shape.scaleY || 1,
                                rotation: shape.rotation || 0,
                            }"
                            @click="selectShape($event, shape.id)"
                            @dragend="handleDragEnd"
                            @transformend="handleTransformEnd"
                        />
                        <!-- Rectangle -->
                        <v-rect
                            v-else-if="shape.tool === 'rect'"
                            :config="{
                                id: shape.id,
                                x: shape.x,
                                y: shape.y,
                                width: shape.width,
                                height: shape.height,
                                stroke: shape.color,
                                fill: shape.fill,
                                strokeWidth: shape.strokeWidth,
                                draggable: tool === 'select',
                                scaleX: shape.scaleX || 1,
                                scaleY: shape.scaleY || 1,
                                rotation: shape.rotation || 0,
                            }"
                            @click="selectShape($event, shape.id)"
                            @dragend="handleDragEnd"
                            @transformend="handleTransformEnd"
                        />
                        <!-- Circle -->
                        <v-circle
                            v-else-if="shape.tool === 'circle'"
                            :config="{
                                id: shape.id,
                                x: shape.x + (shape.width / 2),
                                y: shape.y + (shape.height / 2),
                                radius: Math.sqrt(Math.pow(shape.width / 2, 2) + Math.pow(shape.height / 2, 2)),
                                stroke: shape.color,
                                fill: shape.fill,
                                strokeWidth: shape.strokeWidth,
                                draggable: tool === 'select',
                                scaleX: shape.scaleX || 1,
                                scaleY: shape.scaleY || 1,
                                rotation: shape.rotation || 0,
                            }"
                            @click="selectShape($event, shape.id)"
                            @dragend="handleDragEnd"
                            @transformend="handleTransformEnd"
                        />
                    </template>

                    <!-- Transformer for selection -->
                    <v-transformer
                        ref="transformerRef"
                        :config="{
                            enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
                            rotateEnabled: true,
                        }"
                    />
                </v-layer>
            </v-stage>
        </div>
    </div>
</template>

<style scoped>
.whiteboard-container {
    user-select: none;
}
.btn-active {
    background-color: hsl(var(--bc) / 0.1);
}
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.canvas-area {
    background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
