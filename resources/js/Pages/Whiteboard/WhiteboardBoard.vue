<script lang="ts" setup>
import {onMounted, onUnmounted, ref} from 'vue';
import * as Y from 'yjs';
import {WebsocketProvider} from 'y-websocket';
import {Whiteboard as WhiteboardType} from '@/types';
import {save} from '@/routes/whiteboard';
import { fetchJson } from '@/bootstrap';
import { MdAdsClick, MdHorizontalRule, MdOutlineCircle, MdOutlineDelete, MdOutlineFormatColorFill, MdOutlineRectangle, MdRedo, MdUndo } from 'vue-icons-plus/md';
import { BsEraser } from 'vue-icons-plus/bs';
import { HiPencil } from 'vue-icons-plus/hi';


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
const latency = ref<number | null>(null);
const tool = ref<'pencil' | 'eraser' | 'rect' | 'circle' | 'line' | 'select'>('pencil');
const color = ref('#000000');
const fillColor = ref('transparent');
const strokeWidth = ref(2);

const colorPresets = [
    {name: 'Primary', value: 'oklch(var(--p))', class: 'bg-primary'},
    {name: 'Secondary', value: 'oklch(var(--s))', class: 'bg-secondary'},
    {name: 'Accent', value: 'oklch(var(--a))', class: 'bg-accent'},
    {name: 'Neutral', value: 'oklch(var(--n))', class: 'bg-neutral'},
    {name: 'Info', value: 'oklch(var(--in))', class: 'bg-info'},
    {name: 'Success', value: 'oklch(var(--su))', class: 'bg-success'},
    {name: 'Warning', value: 'oklch(var(--wa))', class: 'bg-warning'},
    {name: 'Error', value: 'oklch(var(--er))', class: 'bg-error'},
];

const getColorValue = (preset: any) => {
    const el = document.createElement('div');
    el.className = preset.class;
    document.body.appendChild(el);
    const color = getComputedStyle(el).backgroundColor;
    document.body.removeChild(el);
    return color;
};

const setPresetColor = (preset: any, target: 'stroke' | 'fill') => {
    const val = getColorValue(preset);
    if (target === 'stroke') {
        color.value = rgbToHex(val);
    } else {
        fillColor.value = rgbToHex(val);
    }
};

const rgbToHex = (rgb: string) => {
    if (rgb.startsWith('#')) return rgb;
    const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!match) return rgb;
    const r = parseInt(match[1]);
    const g = parseInt(match[2]);
    const b = parseInt(match[3]);
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
};
const selectedShapeIds = ref<string[]>([]);

const transformerRef = ref<any>(null);
const stageRef = ref<any>(null);

// Yjs setup
const ydoc = new Y.Doc();
const yshapes = ydoc.getMap<any>('shapes');
const undoManager = new Y.UndoManager(yshapes);

const wsUrl = import.meta.env.VITE_YJS_WS_URL || 'ws://localhost:1234';
const provider = new WebsocketProvider(wsUrl, `whiteboard-${props.whiteboard.id}`, ydoc);

let heartbeatInterval: any = null;

const startHeartbeat = () => {
    heartbeatInterval = setInterval(async () => {
        if (!isConnected.value) {
            latency.value = null;
            return;
        }
        const start = Date.now();
        try {
            await fetchJson('/up');
            latency.value = Date.now() - start;
        } catch (e) {
            console.error("Heartbeat failed", e);
            latency.value = null;
        }
    }, 10000);
};

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
        if (isConnected.value && !heartbeatInterval) {
            startHeartbeat();
        }
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
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    if (heartbeatInterval) clearInterval(heartbeatInterval);
    provider.destroy();
    ydoc.destroy();
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('keydown', handleKeyDown);
});

const handleKeyDown = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
        if (e.shiftKey) {
            redo();
        } else {
            undo();
        }
    } else if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
        redo();
    } else if (e.key === 'Delete' || e.key === 'Backspace') {
        if (tool.value === 'select') {
            deleteSelected();
        }
    }
};

const handleResize = () => {
    if (container.value) {
        stageConfig.value.width = container.value.offsetWidth;
        stageConfig.value.height = container.value.offsetHeight;
    }
};

let currentShapeId: string | null = null;

const selectionRect = ref({
    visible: false,
    x: 0,
    y: 0,
    width: 0,
    height: 0,
});

const handleMouseDown = (e: any) => {
    const stage = e.target.getStage();
    const clickedOnEmpty = e.target === stage;
    const pos = stage.getPointerPosition();

    if (tool.value === 'select') {
        if (clickedOnEmpty) {
            selectedShapeIds.value = [];
            updateTransformer();

            // Start selection rectangle
            selectionRect.value = {
                visible: true,
                x: pos.x,
                y: pos.y,
                width: 0,
                height: 0,
            };
        }
        return;
    }

    if (tool.value === 'eraser' && !e.evt.shiftKey) {
        if (!clickedOnEmpty) {
            // Traverse up to find the group/node with an ID
            let target = e.target;
            while (target && target !== stage && !target.id()) {
                target = target.getParent();
            }

            const id = target?.id();
            if (id) {
                yshapes.delete(id);
                saveState();
            }
        }
        return;
    }

    currentShapeId = Math.random().toString(36).substring(7);

    const newShape: any = {
        id: currentShapeId,
        tool: tool.value,
        color: tool.value === 'eraser' ? '#ffffff' : color.value,
        fill: tool.value === 'rect' || tool.value === 'circle' ? (fillColor.value === 'transparent' ? 'rgba(0,0,0,0)' : fillColor.value) : null,
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
    const pos = e.target.getStage().getPointerPosition();

    if (selectionRect.value.visible) {
        selectionRect.value.width = pos.x - selectionRect.value.x;
        selectionRect.value.height = pos.y - selectionRect.value.y;
        return;
    }

    if (!currentShapeId) return;

    const shape = yshapes.get(currentShapeId);
    if (!shape) return;

    const updatedShape = {...shape};

    if (tool.value === 'pencil' || (tool.value === 'eraser' && e.evt.shiftKey)) {
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
    if (selectionRect.value.visible) {
        selectionRect.value.visible = false;
        const stage = stageRef.value.getStage();
        const box = {
            x1: Math.min(selectionRect.value.x, selectionRect.value.x + selectionRect.value.width),
            y1: Math.min(selectionRect.value.y, selectionRect.value.y + selectionRect.value.height),
            x2: Math.max(selectionRect.value.x, selectionRect.value.x + selectionRect.value.width),
            y2: Math.max(selectionRect.value.y, selectionRect.value.y + selectionRect.value.height),
        };

        selectedShapeIds.value = shapes.value.filter((shape) => {
            const node = stage.findOne('#' + shape.id);
            if (!node) return false;
            const r = node.getClientRect();
            return (
                r.x >= box.x1 &&
                r.y >= box.y1 &&
                r.x + r.width <= box.x2 &&
                r.y + r.height <= box.y2
            );
        }).map(s => s.id);
        updateTransformer();
    }

    currentShapeId = null;
    saveState();
};

const handleTransformEnd = (e: any) => {
    const node = e.target;
    const id = node.id();
    const shape = yshapes.get(id);
    if (!shape) return;

    let newX = node.x();
    let newY = node.y();

    // If it's a circle, we need to convert the center position back to the top-left "x" we store in state
    if (shape.tool === 'circle') {
        newX = newX - (shape.width * node.scaleX() / 2);
        newY = newY - (shape.height * node.scaleY() / 2);
    }

    yshapes.set(id, {
        ...shape,
        x: newX,
        y: newY,
        scaleX: node.scaleX(),
        scaleY: node.scaleY(),
        rotation: node.rotation(),
    });
    saveState();
};

const handleDragEnd = (e: any) => {
    const node = e.target;
    const id = node.id();
    const shape = yshapes.get(id);
    if (!shape) return;

    let newX = node.x();
    let newY = node.y();

    // If it's a circle, we need to convert the center position back to the top-left "x" we store in state
    if (shape.tool === 'circle') {
        newX = newX - (shape.width / 2);
        newY = newY - (shape.height / 2);
    }

    yshapes.set(id, {
        ...shape,
        x: newX,
        y: newY,
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
        await fetchJson(save.url(props.whiteboard.id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({state})
        });
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
    <div class="whiteboard-container flex flex-col h-full bg-base-100">
        <!-- Toolbar -->
        <div
            class="toolbar flex items-center justify-center gap-2 px-4 border-b bg-base-200 overflow-visible text-base-content h-16">
            <div class="flex items-center gap-1">
                <div class="tooltip tooltip-bottom" data-tip="Select Tool">
                    <button
                        :class="{'btn-active': tool === 'select'}" class="btn btn-sm btn-ghost"
                        @click="tool = 'select'">
                        <MdAdsClick/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Pencil Tool">
                    <button
                        :class="{'btn-active': tool === 'pencil'}" class="btn btn-sm btn-ghost"
                        @click="tool = 'pencil'">
                        <HiPencil/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Line Tool">
                    <button
                        :class="{'btn-active': tool === 'line'}" class="btn btn-sm btn-ghost"
                        @click="tool = 'line'">
                        <MdHorizontalRule/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Rectangle Tool">
                    <button
                        :class="{'btn-active': tool === 'rect'}" class="btn btn-sm btn-ghost"
                        @click="tool = 'rect'">
                        <MdOutlineRectangle/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Circle Tool">
                    <button
                        :class="{'btn-active': tool === 'circle'}" class="btn btn-sm btn-ghost"
                        @click="tool = 'circle'">
                        <MdOutlineCircle/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Eraser (Shift+Drag for brush style)">
                    <button
                        :class="{'btn-active': tool === 'eraser'}" class="btn btn-sm btn-ghost"
                        @click="tool = 'eraser'">
                        <BsEraser/>
                    </button>
                </div>
            </div>

            <div class="divider divider-horizontal h-10 my-auto mx-1"></div>

            <div class="flex items-center gap-6">
                <div class="flex flex-col items-center justify-center">
                    <span class="text-[10px] font-bold uppercase opacity-50 mb-1">Stroke</span>
                    <div class="flex items-center gap-1">
                        <div
                            v-for="preset in colorPresets" :key="preset.name"
                            :class="preset.class"
                            :title="preset.name"
                            class="w-3 h-3 rounded-full cursor-pointer border border-base-content/20 hover:scale-125 transition-transform"
                            @click="setPresetColor(preset, 'stroke')"
                        ></div>
                        <div class="tooltip tooltip-bottom flex ml-1" data-tip="Custom Color">
                            <input
                                v-model="color"
                                class="w-5 h-5 cursor-pointer border-none p-0 bg-transparent rounded overflow-hidden"
                                type="color"/>
                        </div>
                    </div>
                </div>
                <div v-if="['rect', 'circle'].includes(tool)" class="flex flex-col items-center justify-center">
                    <span class="text-[10px] font-bold uppercase opacity-50 mb-1">Fill</span>
                    <div class="flex items-center gap-1">
                        <div
                            v-for="preset in colorPresets" :key="'fill-' + preset.name"
                            :class="preset.class"
                            :title="'Fill ' + preset.name"
                            class="w-3 h-3 rounded-full cursor-pointer border border-base-content/20 hover:scale-125 transition-transform"
                            @click="setPresetColor(preset, 'fill')"
                        ></div>
                        <div class="tooltip tooltip-bottom flex ml-1" data-tip="Custom Fill">
                            <input
                                v-model="fillColor" :disabled="fillColor === 'transparent'"
                                class="w-5 h-5 cursor-pointer border-none p-0 bg-transparent rounded overflow-hidden disabled:opacity-30"
                                type="color"/>
                        </div>
                        <div class="tooltip tooltip-bottom flex" data-tip="No Fill">
                            <button
                                :class="{'btn-active bg-base-300': fillColor === 'transparent'}"
                                class="btn btn-xs btn-ghost p-0 min-h-0 h-5 w-5 ml-1 flex items-center justify-center"
                                @click="fillColor = fillColor === 'transparent' ? '#ffffff' : 'transparent'">
                                <MdOutlineFormatColorFill
                                    :class="{'text-error': fillColor === 'transparent'}"
                                    scale="0.6"/>
                            </button>
                        </div>
                    </div>
                </div>
                <select
                    v-model="strokeWidth" class="select select-sm select-bordered tooltip tooltip-bottom"
                    data-tip="Stroke Width">
                    <option :value="1">1px</option>
                    <option :value="2">2px</option>
                    <option :value="5">5px</option>
                    <option :value="10">10px</option>
                </select>
            </div>

            <div class="divider divider-horizontal h-10 my-auto mx-1"></div>

            <div class="flex items-center gap-1">
                <div class="tooltip tooltip-bottom" data-tip="Undo (Ctrl+Z)">
                    <button class="btn btn-sm btn-ghost" @click="undo">
                        <MdUndo/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Redo (Ctrl+Y)">
                    <button class="btn btn-sm btn-ghost" @click="redo">
                        <MdRedo/>
                    </button>
                </div>
            </div>

            <div class="divider divider-horizontal h-10 my-auto mx-1"></div>

            <div class="flex items-center gap-2">
                <div class="tooltip tooltip-bottom" data-tip="Delete Selected (Del)">
                    <button
                        :disabled="selectedShapeIds.length === 0"
                        class="btn btn-sm btn-ghost text-error hover:bg-error/10"
                        @click="deleteSelected">
                        <MdOutlineDelete/>
                    </button>
                </div>
                <div class="tooltip tooltip-bottom" data-tip="Clear All Canvas">
                    <button class="btn btn-sm btn-ghost text-error" @click="clear">
                        Clear
                    </button>
                </div>
            </div>

            <div class="ml-auto flex items-center gap-2 pr-2">
                <div
                    :data-tip="isConnected ? (latency !== null ? `Latency: ${latency}ms` : 'Connected') : 'Disconnected'"
                    class="tooltip tooltip-left flex items-center gap-2">
                    <span :class="isConnected ? 'bg-success' : 'bg-warning'" class="status-dot"></span>
                    <span class="text-xs">{{ isConnected ? 'Live' : 'Connecting...' }}</span>
                </div>
            </div>
        </div>

        <!-- Canvas Area -->
        <div
            ref="container" :class="{'cursor-default': tool === 'select'}"
            class="canvas-area grow relative overflow-hidden cursor-crosshair">
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
                                hitStrokeWidth: 20,
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
                                hitStrokeWidth: 20,
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
                                radius: Math.max(Math.abs(shape.width / 2), Math.abs(shape.height / 2)),
                                stroke: shape.color,
                                fill: shape.fill,
                                strokeWidth: shape.strokeWidth,
                                hitStrokeWidth: 20,
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
                            enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'top-center', 'bottom-center', 'middle-left', 'middle-right'],
                            rotateEnabled: true,
                        }"
                    />
                    <v-rect
                        v-if="selectionRect.visible"
                        :config="{
                            x: Math.min(selectionRect.x, selectionRect.x + selectionRect.width),
                            y: Math.min(selectionRect.y, selectionRect.y + selectionRect.height),
                            width: Math.abs(selectionRect.width),
                            height: Math.abs(selectionRect.height),
                            fill: 'rgba(0,0,255,0.1)',
                            stroke: 'blue',
                            strokeWidth: 1,
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
