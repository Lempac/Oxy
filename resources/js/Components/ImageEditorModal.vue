<script lang="ts" setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import {
    MdRotateRight,
    MdRotateLeft,
    MdFlip,
    MdCrop,
    MdCropSquare,
    MdCrop169,
    MdCropFree,
    MdOutlineRectangle,
    MdOutlineCircle,
    MdHorizontalRule,
    MdArrowRightAlt,
    MdOutlineTextFields,
    MdUndo,
    MdRedo,
    MdRestartAlt,
    MdZoomIn,
    MdZoomOut,
    MdCheck,
    MdClose,
    MdOutlineFormatColorFill,
} from 'vue-icons-plus/md';
import { HiPencil } from 'vue-icons-plus/hi';
import { BsEraser } from 'vue-icons-plus/bs';
import { FaHighlighter } from 'vue-icons-plus/fa';

export interface ImageEditorProps {
    modelValue: boolean;
    imageSource: File | Blob | string | null;
    title?: string;
    aspectRatioLock?: number | null; // e.g. 1 for 1:1 square
    circleMask?: boolean;
}

const props = withDefaults(defineProps<ImageEditorProps>(), {
    title: 'Edit Image',
    aspectRatioLock: null,
    circleMask: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'close'): void;
    (e: 'save', file: File): void;
}>();

// Editor state
type ToolType = 'pencil' | 'highlighter' | 'rect' | 'circle' | 'line' | 'arrow' | 'text' | 'eraser' | 'crop';
type AspectPreset = 'free' | '1:1' | '4:3' | '16:9' | '3:2';

const activeTool = ref<ToolType>('pencil');
const strokeColor = ref('#3b82f6');
const fillColor = ref<string>('transparent');
const strokeWidth = ref<number>(4);
const fontSize = ref<number>(24);
const zoomLevel = ref<number>(1);

// Transforms
const rotation = ref<number>(0); // 0, 90, 180, 270
const flipH = ref<boolean>(false);
const flipV = ref<boolean>(false);

// Crop state
const cropAspect = ref<AspectPreset>(props.aspectRatioLock === 1 ? '1:1' : 'free');
const isCropping = computed(() => activeTool.value === 'crop');
interface CropBox {
    x: number;
    y: number;
    width: number;
    height: number;
}
const cropBox = ref<CropBox>({ x: 0, y: 0, width: 0, height: 0 });
const appliedCrop = ref<CropBox | null>(null);

// Vector drawing layer items
interface BaseItem {
    id: string;
}
interface StrokeItem extends BaseItem {
    type: 'pencil' | 'highlighter' | 'eraser';
    points: { x: number; y: number }[];
    color: string;
    width: number;
    opacity: number;
}
interface ShapeItem extends BaseItem {
    type: 'rect' | 'circle' | 'line' | 'arrow';
    x1: number;
    y1: number;
    x2: number;
    y2: number;
    color: string;
    fillColor: string;
    width: number;
}
interface TextItem extends BaseItem {
    type: 'text';
    x: number;
    y: number;
    text: string;
    color: string;
    fontSize: number;
}

type DrawItem = StrokeItem | ShapeItem | TextItem;

const drawItems = ref<DrawItem[]>([]);
const currentItem = ref<DrawItem | null>(null);

// History stack for Undo / Redo
interface HistoryState {
    rotation: number;
    flipH: boolean;
    flipV: boolean;
    appliedCrop: CropBox | null;
    drawItems: DrawItem[];
}
const undoStack = ref<HistoryState[]>([]);
const redoStack = ref<HistoryState[]>([]);

// Canvas & DOM refs
const containerRef = ref<HTMLDivElement | null>(null);
const baseCanvasRef = ref<HTMLCanvasElement | null>(null);
const annotationCanvasRef = ref<HTMLCanvasElement | null>(null);
const originalImage = ref<HTMLImageElement | null>(null);
const originalFile = ref<File | null>(null);
const imageLoaded = ref<boolean>(false);

// Color presets
const colorPresets = [
    '#ffffff',
    '#000000',
    '#ef4444', // Red
    '#f97316', // Orange
    '#eab308', // Yellow
    '#22c55e', // Green
    '#06b6d4', // Cyan
    '#3b82f6', // Blue
    '#8b5cf6', // Purple
    '#ec4899', // Pink
];

// Snapshot for undo
const pushHistoryState = () => {
    undoStack.value.push({
        rotation: rotation.value,
        flipH: flipH.value,
        flipV: flipV.value,
        appliedCrop: appliedCrop.value ? { ...appliedCrop.value } : null,
        drawItems: JSON.parse(JSON.stringify(drawItems.value)),
    });
    // Limit stack size
    if (undoStack.value.length > 30) {
        undoStack.value.shift();
    }
    redoStack.value = [];
};

const undo = () => {
    if (undoStack.value.length === 0) return;
    const current: HistoryState = {
        rotation: rotation.value,
        flipH: flipH.value,
        flipV: flipV.value,
        appliedCrop: appliedCrop.value ? { ...appliedCrop.value } : null,
        drawItems: JSON.parse(JSON.stringify(drawItems.value)),
    };
    redoStack.value.push(current);

    const prev = undoStack.value.pop()!;
    rotation.value = prev.rotation;
    flipH.value = prev.flipH;
    flipV.value = prev.flipV;
    appliedCrop.value = prev.appliedCrop ? { ...prev.appliedCrop } : null;
    drawItems.value = prev.drawItems;
    renderAll();
};

const redo = () => {
    if (redoStack.value.length === 0) return;
    const current: HistoryState = {
        rotation: rotation.value,
        flipH: flipH.value,
        flipV: flipV.value,
        appliedCrop: appliedCrop.value ? { ...appliedCrop.value } : null,
        drawItems: JSON.parse(JSON.stringify(drawItems.value)),
    };
    undoStack.value.push(current);

    const next = redoStack.value.pop()!;
    rotation.value = next.rotation;
    flipH.value = next.flipH;
    flipV.value = next.flipV;
    appliedCrop.value = next.appliedCrop ? { ...next.appliedCrop } : null;
    drawItems.value = next.drawItems;
    renderAll();
};

const resetToOriginal = () => {
    pushHistoryState();
    rotation.value = 0;
    flipH.value = false;
    flipV.value = false;
    appliedCrop.value = null;
    drawItems.value = [];
    if (props.aspectRatioLock === 1) {
        cropAspect.value = '1:1';
    } else {
        cropAspect.value = 'free';
    }
    renderAll();
};

// Compute current effective uncropped transformed image dimensions
const getTransformedDimensions = () => {
    if (!originalImage.value) return { width: 0, height: 0 };
    const isRotated = rotation.value === 90 || rotation.value === 270;
    return {
        width: isRotated ? originalImage.value.height : originalImage.value.width,
        height: isRotated ? originalImage.value.width : originalImage.value.height,
    };
};

// Compute active working dimensions (after crop if applied)
const getWorkingDimensions = () => {
    if (appliedCrop.value) {
        return {
            width: appliedCrop.value.width,
            height: appliedCrop.value.height,
        };
    }
    return getTransformedDimensions();
};

// Load image source
const loadImage = (src: File | Blob | string | null) => {
    if (!src) {
        originalImage.value = null;
        imageLoaded.value = false;
        return;
    }

    if (src instanceof File) {
        originalFile.value = src;
    }

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        originalImage.value = img;
        imageLoaded.value = true;
        rotation.value = 0;
        flipH.value = false;
        flipV.value = false;
        appliedCrop.value = null;
        drawItems.value = [];
        undoStack.value = [];
        redoStack.value = [];
        nextTick(() => {
            fitToContainer();
            renderAll();
            if (props.aspectRatioLock === 1) {
                cropAspect.value = '1:1';
            }
        });
    };

    if (typeof src === 'string') {
        img.src = src;
    } else {
        const reader = new FileReader();
        reader.onload = (e) => {
            if (typeof e.target?.result === 'string') {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(src);
    }
};

const fitToContainer = () => {
    if (!containerRef.value || !originalImage.value) return;
    const { width, height } = getWorkingDimensions();
    if (width === 0 || height === 0) return;

    const pad = 40;
    const availW = Math.max(100, containerRef.value.clientWidth - pad);
    const availH = Math.max(100, containerRef.value.clientHeight - pad);

    const scale = Math.min(availW / width, availH / height, 1);
    zoomLevel.value = Math.max(0.1, Number(scale.toFixed(2)));
};

// Render base canvas (Image transformed + cropped)
const renderBase = () => {
    const canvas = baseCanvasRef.value;
    const img = originalImage.value;
    if (!canvas || !img) return;

    const { width: workW, height: workH } = getWorkingDimensions();
    canvas.width = workW;
    canvas.height = workH;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    ctx.clearRect(0, 0, workW, workH);

    ctx.save();

    if (appliedCrop.value) {
        // If crop is applied, offset origin by crop coordinates
        ctx.translate(-appliedCrop.value.x, -appliedCrop.value.y);
    }

    const { width: transW, height: transH } = getTransformedDimensions();

    // Move to center of transformed bounds
    ctx.translate(transW / 2, transH / 2);

    // Apply rotation
    ctx.rotate((rotation.value * Math.PI) / 180);

    // Apply flips
    ctx.scale(flipH.value ? -1 : 1, flipV.value ? -1 : 1);

    // Draw original image centered
    ctx.drawImage(img, -img.width / 2, -img.height / 2, img.width, img.height);

    ctx.restore();
};

// Render annotation layer
const renderAnnotations = () => {
    const canvas = annotationCanvasRef.value;
    if (!canvas) return;

    const { width: workW, height: workH } = getWorkingDimensions();
    canvas.width = workW;
    canvas.height = workH;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    ctx.clearRect(0, 0, workW, workH);

    const allItems = [...drawItems.value];
    if (currentItem.value) {
        allItems.push(currentItem.value);
    }

    for (const item of allItems) {
        ctx.save();
        if (item.type === 'pencil' || item.type === 'highlighter') {
            if (item.points.length > 0) {
                ctx.beginPath();
                ctx.strokeStyle = item.color;
                ctx.lineWidth = item.width;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.globalAlpha = item.opacity || 1;

                ctx.moveTo(item.points[0].x, item.points[0].y);
                for (let i = 1; i < item.points.length; i++) {
                    ctx.lineTo(item.points[i].x, item.points[i].y);
                }
                ctx.stroke();
            }
        } else if (item.type === 'eraser') {
            if (item.points.length > 0) {
                // Erase ONLY annotations on this canvas, preserving the photo underneath!
                ctx.globalCompositeOperation = 'destination-out';
                ctx.beginPath();
                ctx.lineWidth = item.width;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.moveTo(item.points[0].x, item.points[0].y);
                for (let i = 1; i < item.points.length; i++) {
                    ctx.lineTo(item.points[i].x, item.points[i].y);
                }
                ctx.stroke();
            }
        } else if (item.type === 'rect') {
            const x = Math.min(item.x1, item.x2);
            const y = Math.min(item.y1, item.y2);
            const w = Math.abs(item.x2 - item.x1);
            const h = Math.abs(item.y2 - item.y1);

            if (item.fillColor && item.fillColor !== 'transparent') {
                ctx.fillStyle = item.fillColor;
                ctx.fillRect(x, y, w, h);
            }
            if (item.width > 0 && item.color) {
                ctx.strokeStyle = item.color;
                ctx.lineWidth = item.width;
                ctx.strokeRect(x, y, w, h);
            }
        } else if (item.type === 'circle') {
            const cx = (item.x1 + item.x2) / 2;
            const cy = (item.y1 + item.y2) / 2;
            const rx = Math.abs(item.x2 - item.x1) / 2;
            const ry = Math.abs(item.y2 - item.y1) / 2;

            ctx.beginPath();
            ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI * 2);
            if (item.fillColor && item.fillColor !== 'transparent') {
                ctx.fillStyle = item.fillColor;
                ctx.fill();
            }
            if (item.width > 0 && item.color) {
                ctx.strokeStyle = item.color;
                ctx.lineWidth = item.width;
                ctx.stroke();
            }
        } else if (item.type === 'line') {
            ctx.beginPath();
            ctx.strokeStyle = item.color;
            ctx.lineWidth = item.width;
            ctx.lineCap = 'round';
            ctx.moveTo(item.x1, item.y1);
            ctx.lineTo(item.x2, item.y2);
            ctx.stroke();
        } else if (item.type === 'arrow') {
            ctx.beginPath();
            ctx.strokeStyle = item.color;
            ctx.fillStyle = item.color;
            ctx.lineWidth = item.width;
            ctx.lineCap = 'round';

            const dx = item.x2 - item.x1;
            const dy = item.y2 - item.y1;
            const angle = Math.atan2(dy, dx);
            const headLength = Math.max(12, item.width * 3);

            ctx.moveTo(item.x1, item.y1);
            ctx.lineTo(item.x2, item.y2);
            ctx.stroke();

            // Arrow head
            ctx.beginPath();
            ctx.moveTo(item.x2, item.y2);
            ctx.lineTo(
                item.x2 - headLength * Math.cos(angle - Math.PI / 6),
                item.y2 - headLength * Math.sin(angle - Math.PI / 6)
            );
            ctx.lineTo(
                item.x2 - headLength * Math.cos(angle + Math.PI / 6),
                item.y2 - headLength * Math.sin(angle + Math.PI / 6)
            );
            ctx.closePath();
            ctx.fill();
        } else if (item.type === 'text') {
            ctx.font = `bold ${item.fontSize}px sans-serif`;
            ctx.fillStyle = item.color;
            ctx.textBaseline = 'top';
            ctx.fillText(item.text, item.x, item.y);
        }
        ctx.restore();
    }
};

const renderAll = () => {
    renderBase();
    renderAnnotations();
    if (isCropping.value) {
        initCropBox();
    }
};

// Transformations
const rotateCw = () => {
    pushHistoryState();
    rotation.value = (rotation.value + 90) % 360;
    appliedCrop.value = null; // reset crop coordinates when rotated
    renderAll();
};

const rotateCcw = () => {
    pushHistoryState();
    rotation.value = (rotation.value + 270) % 360;
    appliedCrop.value = null;
    renderAll();
};

const toggleFlipH = () => {
    pushHistoryState();
    flipH.value = !flipH.value;
    renderAll();
};

const toggleFlipV = () => {
    pushHistoryState();
    flipV.value = !flipV.value;
    renderAll();
};

// Crop handlers
const initCropBox = () => {
    const { width, height } = getWorkingDimensions();
    if (width === 0 || height === 0) return;

    let targetW = width * 0.85;
    let targetH = height * 0.85;

    let ratio: number | null = null;
    if (cropAspect.value === '1:1') ratio = 1;
    else if (cropAspect.value === '4:3') ratio = 4 / 3;
    else if (cropAspect.value === '16:9') ratio = 16 / 9;
    else if (cropAspect.value === '3:2') ratio = 3 / 2;

    if (ratio !== null) {
        if (targetW / targetH > ratio) {
            targetW = targetH * ratio;
        } else {
            targetH = targetW / ratio;
        }
    }

    cropBox.value = {
        x: Math.round((width - targetW) / 2),
        y: Math.round((height - targetH) / 2),
        width: Math.round(targetW),
        height: Math.round(targetH),
    };
};

const setCropAspect = (preset: AspectPreset) => {
    cropAspect.value = preset;
    initCropBox();
};

const applyCrop = () => {
    if (!isCropping.value) return;
    pushHistoryState();

    const curOffset = appliedCrop.value || { x: 0, y: 0 };
    appliedCrop.value = {
        x: curOffset.x + cropBox.value.x,
        y: curOffset.y + cropBox.value.y,
        width: cropBox.value.width,
        height: cropBox.value.height,
    };

    // Adjust annotation coordinates relative to cropped region
    const cropX = cropBox.value.x;
    const cropY = cropBox.value.y;

    drawItems.value = drawItems.value.map((item) => {
        if (item.type === 'pencil' || item.type === 'highlighter' || item.type === 'eraser') {
            return {
                ...item,
                points: item.points.map((p) => ({ x: p.x - cropX, y: p.y - cropY })),
            };
        } else if (item.type === 'rect' || item.type === 'circle' || item.type === 'line' || item.type === 'arrow') {
            return {
                ...item,
                x1: item.x1 - cropX,
                y1: item.y1 - cropY,
                x2: item.x2 - cropX,
                y2: item.y2 - cropY,
            };
        } else if (item.type === 'text') {
            return {
                ...item,
                x: item.x - cropX,
                y: item.y - cropY,
            };
        }
        return item;
    });

    activeTool.value = 'pencil';
    nextTick(() => {
        fitToContainer();
        renderAll();
    });
};

const cancelCrop = () => {
    activeTool.value = 'pencil';
    renderAll();
};

// Canvas Interaction (Pointer/Mouse events)
const isDrawing = ref(false);
const cropDragMode = ref<string | null>(null);
const dragStartPos = ref<{ x: number; y: number }>({ x: 0, y: 0 });
const initialCropState = ref<CropBox>({ x: 0, y: 0, width: 0, height: 0 });

// Text input popup
const activeTextInput = ref<{ x: number; y: number; text: string } | null>(null);

const getCanvasPoint = (e: MouseEvent | TouchEvent): { x: number; y: number } => {
    const canvas = annotationCanvasRef.value;
    if (!canvas) return { x: 0, y: 0 };
    const rect = canvas.getBoundingClientRect();
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;

    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    return {
        x: (clientX - rect.left) * scaleX,
        y: (clientY - rect.top) * scaleY,
    };
};

const onPointerDown = (e: MouseEvent | TouchEvent) => {
    if (!imageLoaded.value) return;

    if (isCropping.value) {
        return; // Crop dragging handled by crop handles
    }

    const pos = getCanvasPoint(e);

    if (activeTool.value === 'text') {
        activeTextInput.value = {
            x: Math.round(pos.x),
            y: Math.round(pos.y),
            text: '',
        };
        return;
    }

    isDrawing.value = true;
    const id = Math.random().toString(36).slice(2);

    if (activeTool.value === 'pencil' || activeTool.value === 'highlighter' || activeTool.value === 'eraser') {
        currentItem.value = {
            id,
            type: activeTool.value,
            points: [pos],
            color: activeTool.value === 'highlighter' ? strokeColor.value : strokeColor.value,
            width: strokeWidth.value * (activeTool.value === 'highlighter' ? 3 : activeTool.value === 'eraser' ? 3 : 1),
            opacity: activeTool.value === 'highlighter' ? 0.35 : 1,
        };
    } else if (['rect', 'circle', 'line', 'arrow'].includes(activeTool.value)) {
        currentItem.value = {
            id,
            type: activeTool.value as 'rect' | 'circle' | 'line' | 'arrow',
            x1: pos.x,
            y1: pos.y,
            x2: pos.x,
            y2: pos.y,
            color: strokeColor.value,
            fillColor: fillColor.value,
            width: strokeWidth.value,
        };
    }

    renderAnnotations();
};

const onPointerMove = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing.value || !currentItem.value) return;
    const pos = getCanvasPoint(e);

    if (currentItem.value.type === 'pencil' || currentItem.value.type === 'highlighter' || currentItem.value.type === 'eraser') {
        currentItem.value.points.push(pos);
    } else if (
        currentItem.value.type === 'rect' ||
        currentItem.value.type === 'circle' ||
        currentItem.value.type === 'line' ||
        currentItem.value.type === 'arrow'
    ) {
        currentItem.value.x2 = pos.x;
        currentItem.value.y2 = pos.y;
    }

    renderAnnotations();
};

const onPointerUp = () => {
    if (!isDrawing.value || !currentItem.value) {
        isDrawing.value = false;
        return;
    }

    pushHistoryState();
    drawItems.value.push(currentItem.value);
    currentItem.value = null;
    isDrawing.value = false;
    renderAnnotations();
};

const submitTextItem = () => {
    if (activeTextInput.value && activeTextInput.value.text.trim()) {
        pushHistoryState();
        drawItems.value.push({
            id: Math.random().toString(36).slice(2),
            type: 'text',
            x: activeTextInput.value.x,
            y: activeTextInput.value.y,
            text: activeTextInput.value.text.trim(),
            color: strokeColor.value,
            fontSize: fontSize.value,
        });
        renderAnnotations();
    }
    activeTextInput.value = null;
};

// Interactive Crop Box Dragging
const startCropDrag = (handle: string, e: MouseEvent | TouchEvent) => {
    cropDragMode.value = handle;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    dragStartPos.value = { x: clientX, y: clientY };
    initialCropState.value = { ...cropBox.value };

    window.addEventListener('mousemove', onCropDragMove);
    window.addEventListener('touchmove', onCropDragMove);
    window.addEventListener('mouseup', onCropDragEnd);
    window.addEventListener('touchend', onCropDragEnd);
};

const onCropDragMove = (e: MouseEvent | TouchEvent) => {
    if (!cropDragMode.value) return;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;

    const canvas = annotationCanvasRef.value;
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    const dx = (clientX - dragStartPos.value.x) * scaleX;
    const dy = (clientY - dragStartPos.value.y) * scaleY;

    const init = initialCropState.value;
    const { width: maxW, height: maxH } = getWorkingDimensions();

    let newX = init.x;
    let newY = init.y;
    let newW = init.width;
    let newH = init.height;

    const mode = cropDragMode.value;

    if (mode === 'move') {
        newX = Math.max(0, Math.min(maxW - init.width, init.x + dx));
        newY = Math.max(0, Math.min(maxH - init.height, init.y + dy));
    } else {
        if (mode.includes('e')) newW = Math.max(20, Math.min(maxW - init.x, init.width + dx));
        if (mode.includes('s')) newH = Math.max(20, Math.min(maxH - init.y, init.height + dy));
        if (mode.includes('w')) {
            const candidateW = init.width - dx;
            if (candidateW >= 20 && init.x + dx >= 0) {
                newX = init.x + dx;
                newW = candidateW;
            }
        }
        if (mode.includes('n')) {
            const candidateH = init.height - dy;
            if (candidateH >= 20 && init.y + dy >= 0) {
                newY = init.y + dy;
                newH = candidateH;
            }
        }

        // Apply aspect ratio constraints if active
        let ratio: number | null = null;
        if (cropAspect.value === '1:1') ratio = 1;
        else if (cropAspect.value === '4:3') ratio = 4 / 3;
        else if (cropAspect.value === '16:9') ratio = 16 / 9;
        else if (cropAspect.value === '3:2') ratio = 3 / 2;

        if (ratio !== null) {
            if (mode.includes('e') || mode.includes('w')) {
                newH = newW / ratio;
            } else {
                newW = newH * ratio;
            }
            if (newX + newW > maxW) newW = maxW - newX;
            if (newY + newH > maxH) newH = maxH - newY;
        }
    }

    cropBox.value = {
        x: Math.round(newX),
        y: Math.round(newY),
        width: Math.round(newW),
        height: Math.round(newH),
    };
};

const onCropDragEnd = () => {
    cropDragMode.value = null;
    window.removeEventListener('mousemove', onCropDragMove);
    window.removeEventListener('touchmove', onCropDragMove);
    window.removeEventListener('mouseup', onCropDragEnd);
    window.removeEventListener('touchend', onCropDragEnd);
};

// Export modified image as File
const saveAndExport = () => {
    if (!originalImage.value) return;

    const { width, height } = getWorkingDimensions();
    const finalCanvas = document.createElement('canvas');
    finalCanvas.width = width;
    finalCanvas.height = height;

    const ctx = finalCanvas.getContext('2d');
    if (!ctx) return;

    // 1. Draw base photo
    if (baseCanvasRef.value) {
        ctx.drawImage(baseCanvasRef.value, 0, 0);
    }
    // 2. Draw annotations on top
    if (annotationCanvasRef.value) {
        ctx.drawImage(annotationCanvasRef.value, 0, 0);
    }

    const mimeType = originalFile.value?.type || 'image/png';
    const originalName = originalFile.value?.name || 'edited-image.png';

    finalCanvas.toBlob((blob) => {
        if (!blob) return;
        const file = new File([blob], originalName, {
            type: mimeType,
            lastModified: Date.now(),
        });
        emit('save', file);
        emit('update:modelValue', false);
        emit('close');
    }, mimeType, 0.92);
};

const closeModal = () => {
    emit('update:modelValue', false);
    emit('close');
};

// Modal drag-and-drop state
const isEditorDraggingOver = ref(false);
const onEditorDropImage = (e: DragEvent) => {
    isEditorDraggingOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        loadImage(file);
    }
};

// Keyboard listener
const handleKeyDown = (e: KeyboardEvent) => {
    if (!props.modelValue) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
        e.preventDefault();
        if (e.shiftKey) redo();
        else undo();
    } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
        e.preventDefault();
        redo();
    } else if (e.key === 'Escape') {
        if (isCropping.value) cancelCrop();
        else closeModal();
    }
};

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            loadImage(props.imageSource);
            window.addEventListener('keydown', handleKeyDown);
        } else {
            window.removeEventListener('keydown', handleKeyDown);
        }
    }
);

watch(
    () => props.imageSource,
    (newSrc) => {
        if (props.modelValue && newSrc) {
            loadImage(newSrc);
        }
    }
);

watch(activeTool, (tool) => {
    if (tool === 'crop') {
        initCropBox();
    }
});

onMounted(() => {
    if (props.modelValue && props.imageSource) {
        loadImage(props.imageSource);
        window.addEventListener('keydown', handleKeyDown);
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    onCropDragEnd();
});
</script>

<template>
    <div v-if="modelValue" class="modal modal-open z-50 backdrop-blur-xs select-none">
        <div class="modal-box max-w-5xl w-11/12 h-[90vh] max-h-[850px] p-0 flex flex-col bg-base-100 border border-base-300 shadow-2xl rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-3.5 bg-base-200/80 border-b border-base-300 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-base text-base-content flex items-center gap-2">
                        <span>{{ title }}</span>
                    </h3>
                    <div class="badge badge-sm badge-neutral gap-1">
                        {{ getWorkingDimensions().width }} × {{ getWorkingDimensions().height }} px
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <!-- Reset to original -->
                    <button
                        class="btn btn-xs btn-ghost gap-1.5 text-error hover:bg-error/10"
                        title="Reset all edits to original image"
                        @click="resetToOriginal"
                    >
                        <MdRestartAlt class="size-4" />
                        <span class="hidden sm:inline">Reset</span>
                    </button>

                    <div class="divider divider-horizontal my-1 mx-1"></div>

                    <!-- Undo / Redo -->
                    <button
                        :disabled="undoStack.length === 0"
                        class="btn btn-xs btn-circle btn-ghost"
                        title="Undo (Ctrl+Z)"
                        @click="undo"
                    >
                        <MdUndo class="size-4" />
                    </button>
                    <button
                        :disabled="redoStack.length === 0"
                        class="btn btn-xs btn-circle btn-ghost"
                        title="Redo (Ctrl+Y)"
                        @click="redo"
                    >
                        <MdRedo class="size-4" />
                    </button>

                    <div class="divider divider-horizontal my-1 mx-1"></div>

                    <!-- Zoom Controls -->
                    <button
                        class="btn btn-xs btn-circle btn-ghost"
                        title="Zoom Out"
                        @click="zoomLevel = Math.max(0.2, Number((zoomLevel - 0.1).toFixed(1)))"
                    >
                        <MdZoomOut class="size-4" />
                    </button>
                    <span class="text-xs font-mono w-10 text-center">{{ Math.round(zoomLevel * 100) }}%</span>
                    <button
                        class="btn btn-xs btn-circle btn-ghost"
                        title="Zoom In"
                        @click="zoomLevel = Math.min(3, Number((zoomLevel + 0.1).toFixed(1)))"
                    >
                        <MdZoomIn class="size-4" />
                    </button>
                    <button
                        class="btn btn-xs btn-ghost text-xs ml-1"
                        title="Fit image to screen"
                        @click="fitToContainer"
                    >
                        Fit
                    </button>

                    <div class="divider divider-horizontal my-1 mx-1"></div>

                    <!-- Close Button -->
                    <button class="btn btn-xs btn-circle btn-ghost" @click="closeModal">
                        <MdClose class="size-4" />
                    </button>
                </div>
            </div>

            <!-- Toolbar (Tools + Tool Options) -->
            <div class="px-4 py-2 bg-base-200/40 border-b border-base-300 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <!-- Main Tools -->
                <div class="flex items-center gap-1 bg-base-100 p-1 rounded-xl border border-base-300 shadow-xs">
                    <button
                        :class="{'btn-primary': activeTool === 'pencil', 'btn-ghost': activeTool !== 'pencil'}"
                        class="btn btn-xs btn-square"
                        title="Pencil / Draw"
                        @click="activeTool = 'pencil'"
                    >
                        <HiPencil class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'highlighter', 'btn-ghost': activeTool !== 'highlighter'}"
                        class="btn btn-xs btn-square"
                        title="Highlighter"
                        @click="activeTool = 'highlighter'"
                    >
                        <FaHighlighter class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'rect', 'btn-ghost': activeTool !== 'rect'}"
                        class="btn btn-xs btn-square"
                        title="Rectangle"
                        @click="activeTool = 'rect'"
                    >
                        <MdOutlineRectangle class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'circle', 'btn-ghost': activeTool !== 'circle'}"
                        class="btn btn-xs btn-square"
                        title="Circle"
                        @click="activeTool = 'circle'"
                    >
                        <MdOutlineCircle class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'line', 'btn-ghost': activeTool !== 'line'}"
                        class="btn btn-xs btn-square"
                        title="Line"
                        @click="activeTool = 'line'"
                    >
                        <MdHorizontalRule class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'arrow', 'btn-ghost': activeTool !== 'arrow'}"
                        class="btn btn-xs btn-square"
                        title="Arrow"
                        @click="activeTool = 'arrow'"
                    >
                        <MdArrowRightAlt class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'text', 'btn-ghost': activeTool !== 'text'}"
                        class="btn btn-xs btn-square"
                        title="Add Text"
                        @click="activeTool = 'text'"
                    >
                        <MdOutlineTextFields class="size-3.5" />
                    </button>
                    <button
                        :class="{'btn-primary': activeTool === 'eraser', 'btn-ghost': activeTool !== 'eraser'}"
                        class="btn btn-xs btn-square"
                        title="Eraser (erases drawings only)"
                        @click="activeTool = 'eraser'"
                    >
                        <BsEraser class="size-3.5" />
                    </button>
                    <div class="divider divider-horizontal my-0.5 mx-0.5"></div>
                    <button
                        :class="{'btn-primary': activeTool === 'crop', 'btn-ghost': activeTool !== 'crop'}"
                        class="btn btn-xs btn-square"
                        title="Crop & Resize"
                        @click="activeTool = 'crop'"
                    >
                        <MdCrop class="size-3.5" />
                    </button>
                </div>

                <!-- Secondary Contextual Options Bar -->
                <div v-if="!isCropping" class="flex items-center gap-3 flex-wrap">
                    <!-- Palette -->
                    <div v-if="activeTool !== 'eraser'" class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold opacity-70">Color:</span>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="c in colorPresets"
                                :key="c"
                                :style="{ backgroundColor: c }"
                                :class="{'ring-2 ring-primary ring-offset-1 scale-110': strokeColor === c}"
                                class="size-5 rounded-full border border-base-300 shadow-xs transition-transform"
                                @click="strokeColor = c"
                            ></button>
                            <input
                                v-model="strokeColor"
                                type="color"
                                class="size-5 rounded cursor-pointer border-0 bg-transparent p-0"
                                title="Custom Color"
                            />
                        </div>
                    </div>

                    <!-- Fill Color (For Shapes) -->
                    <div v-if="['rect', 'circle'].includes(activeTool)" class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold opacity-70">Fill:</span>
                        <button
                            :class="{'btn-primary': fillColor === 'transparent', 'btn-ghost': fillColor !== 'transparent'}"
                            class="btn btn-xs text-xs px-2"
                            @click="fillColor = 'transparent'"
                        >
                            None
                        </button>
                        <button
                            :class="{'btn-primary': fillColor === strokeColor, 'btn-ghost': fillColor !== strokeColor}"
                            class="btn btn-xs text-xs px-2"
                            @click="fillColor = strokeColor"
                        >
                            Solid
                        </button>
                    </div>

                    <!-- Stroke Size / Font Size -->
                    <div v-if="activeTool !== 'text'" class="flex items-center gap-2">
                        <span class="text-xs font-semibold opacity-70">Size:</span>
                        <input
                            v-model.number="strokeWidth"
                            type="range"
                            min="1"
                            max="30"
                            class="range range-xs range-primary w-24"
                        />
                        <span class="text-xs font-mono w-4">{{ strokeWidth }}</span>
                    </div>

                    <div v-if="activeTool === 'text'" class="flex items-center gap-2">
                        <span class="text-xs font-semibold opacity-70">Font Size:</span>
                        <input
                            v-model.number="fontSize"
                            type="range"
                            min="12"
                            max="72"
                            class="range range-xs range-primary w-24"
                        />
                        <span class="text-xs font-mono w-6">{{ fontSize }}px</span>
                    </div>

                    <!-- Rotate & Flip Buttons -->
                    <div class="divider divider-horizontal my-0.5 mx-0.5"></div>
                    <div class="flex items-center gap-1">
                        <button class="btn btn-xs btn-ghost btn-square" title="Rotate Counter-Clockwise (-90°)" @click="rotateCcw">
                            <MdRotateLeft class="size-4" />
                        </button>
                        <button class="btn btn-xs btn-ghost btn-square" title="Rotate Clockwise (+90°)" @click="rotateCw">
                            <MdRotateRight class="size-4" />
                        </button>
                        <button class="btn btn-xs btn-ghost btn-square" title="Flip Horizontal" @click="toggleFlipH">
                            <MdFlip class="size-4" />
                        </button>
                        <button class="btn btn-xs btn-ghost btn-square" title="Flip Vertical" @click="toggleFlipV">
                            <MdFlip class="size-4 rotate-90" />
                        </button>
                    </div>
                </div>

                <!-- Crop Bar -->
                <div v-else class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-semibold opacity-70">Aspect:</span>
                    <div class="join">
                        <button
                            :class="{'btn-primary': cropAspect === 'free', 'btn-ghost': cropAspect !== 'free'}"
                            class="btn btn-xs join-item"
                            @click="setCropAspect('free')"
                        >
                            Free
                        </button>
                        <button
                            :class="{'btn-primary': cropAspect === '1:1', 'btn-ghost': cropAspect !== '1:1'}"
                            class="btn btn-xs join-item"
                            @click="setCropAspect('1:1')"
                        >
                            1:1 Square
                        </button>
                        <button
                            :class="{'btn-primary': cropAspect === '4:3', 'btn-ghost': cropAspect !== '4:3'}"
                            class="btn btn-xs join-item"
                            @click="setCropAspect('4:3')"
                        >
                            4:3
                        </button>
                        <button
                            :class="{'btn-primary': cropAspect === '16:9', 'btn-ghost': cropAspect !== '16:9'}"
                            class="btn btn-xs join-item"
                            @click="setCropAspect('16:9')"
                        >
                            16:9
                        </button>
                    </div>

                    <div class="divider divider-horizontal my-0.5 mx-1"></div>

                    <button class="btn btn-xs btn-success gap-1 text-white shadow-xs" @click="applyCrop">
                        <MdCheck class="size-3.5" />
                        Apply Crop
                    </button>
                    <button class="btn btn-xs btn-ghost gap-1" @click="cancelCrop">
                        <MdClose class="size-3.5" />
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Canvas Viewport -->
            <div
                ref="containerRef"
                class="flex-1 bg-neutral/10 dark:bg-neutral-900 overflow-auto relative flex items-center justify-center p-6"
                @mousedown="onPointerDown"
                @mousemove="onPointerMove"
                @mouseup="onPointerUp"
                @touchstart.passive="onPointerDown"
                @touchmove.passive="onPointerMove"
                @touchend="onPointerUp"
                @dragover.prevent="isEditorDraggingOver = true"
                @dragleave.prevent="isEditorDraggingOver = false"
                @drop.prevent="onEditorDropImage"
            >
                <div
                    v-if="imageLoaded"
                    :style="{
                        width: `${getWorkingDimensions().width * zoomLevel}px`,
                        height: `${getWorkingDimensions().height * zoomLevel}px`,
                    }"
                    class="relative shadow-2xl rounded-sm overflow-hidden bg-[repeating-conic-gradient(#00000010_0%_25%,transparent_0%_50%)] bg-[size:16px_16px]"
                >
                    <!-- Base Canvas (Image) -->
                    <canvas
                        ref="baseCanvasRef"
                        :style="{
                            width: `${getWorkingDimensions().width * zoomLevel}px`,
                            height: `${getWorkingDimensions().height * zoomLevel}px`,
                        }"
                        class="absolute inset-0 block pointer-events-none"
                    ></canvas>

                    <!-- Annotation Canvas (Drawings, shapes, text) -->
                    <canvas
                        ref="annotationCanvasRef"
                        :style="{
                            width: `${getWorkingDimensions().width * zoomLevel}px`,
                            height: `${getWorkingDimensions().height * zoomLevel}px`,
                        }"
                        class="absolute inset-0 block cursor-crosshair"
                    ></canvas>

                    <!-- Circle Avatar Outline Guide (optional) -->
                    <div
                        v-if="circleMask"
                        class="absolute inset-0 rounded-full border-2 border-primary/60 pointer-events-none shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]"
                    ></div>

                    <!-- Interactive Crop Overlay -->
                    <div v-if="isCropping" class="absolute inset-0 pointer-events-auto">
                        <!-- Darkened backdrop with clear window over cropBox -->
                        <div
                            :style="{
                                left: `${cropBox.x * zoomLevel}px`,
                                top: `${cropBox.y * zoomLevel}px`,
                                width: `${cropBox.width * zoomLevel}px`,
                                height: `${cropBox.height * zoomLevel}px`,
                            }"
                            class="absolute border-2 border-white shadow-[0_0_0_9999px_rgba(0,0,0,0.65)] cursor-move select-none"
                            @mousedown.stop="startCropDrag('move', $event)"
                            @touchstart.stop.passive="startCropDrag('move', $event)"
                        >
                            <!-- Grid lines (rule of thirds) -->
                            <div class="absolute inset-0 grid grid-cols-3 grid-rows-3 pointer-events-none opacity-40">
                                <div class="border-r border-b border-white/60"></div>
                                <div class="border-r border-b border-white/60"></div>
                                <div class="border-b border-white/60"></div>
                                <div class="border-r border-b border-white/60"></div>
                                <div class="border-r border-b border-white/60"></div>
                                <div class="border-b border-white/60"></div>
                                <div class="border-r border-white/60"></div>
                                <div class="border-r border-white/60"></div>
                                <div></div>
                            </div>

                            <!-- Crop Corner & Edge Handles -->
                            <div
                                class="absolute -top-1.5 -left-1.5 size-3 bg-white border border-black cursor-nwse-resize"
                                @mousedown.stop="startCropDrag('nw', $event)"
                            ></div>
                            <div
                                class="absolute -top-1.5 -right-1.5 size-3 bg-white border border-black cursor-nesw-resize"
                                @mousedown.stop="startCropDrag('ne', $event)"
                            ></div>
                            <div
                                class="absolute -bottom-1.5 -left-1.5 size-3 bg-white border border-black cursor-nesw-resize"
                                @mousedown.stop="startCropDrag('sw', $event)"
                            ></div>
                            <div
                                class="absolute -bottom-1.5 -right-1.5 size-3 bg-white border border-black cursor-nwse-resize"
                                @mousedown.stop="startCropDrag('se', $event)"
                            ></div>

                            <!-- Midpoint handles -->
                            <div
                                class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-4 h-2 bg-white border border-black cursor-ns-resize"
                                @mousedown.stop="startCropDrag('n', $event)"
                            ></div>
                            <div
                                class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-2 bg-white border border-black cursor-ns-resize"
                                @mousedown.stop="startCropDrag('s', $event)"
                            ></div>
                            <div
                                class="absolute top-1/2 -left-1.5 -translate-y-1/2 h-4 w-2 bg-white border border-black cursor-ew-resize"
                                @mousedown.stop="startCropDrag('w', $event)"
                            ></div>
                            <div
                                class="absolute top-1/2 -right-1.5 -translate-y-1/2 h-4 w-2 bg-white border border-black cursor-ew-resize"
                                @mousedown.stop="startCropDrag('e', $event)"
                            ></div>
                        </div>
                    </div>

                    <!-- Active Text Input Overlay -->
                    <div
                        v-if="activeTextInput"
                        :style="{
                            left: `${activeTextInput.x * zoomLevel}px`,
                            top: `${activeTextInput.y * zoomLevel}px`,
                        }"
                        class="absolute z-30 p-1 bg-base-100 rounded-lg shadow-xl border border-primary"
                        @mousedown.stop
                    >
                        <input
                            v-model="activeTextInput.text"
                            :style="{ color: strokeColor }"
                            class="input input-xs input-ghost font-bold focus:outline-none min-w-[150px]"
                            placeholder="Type text and press Enter..."
                            autofocus
                            @keydown.enter="submitTextItem"
                            @keydown.esc="activeTextInput = null"
                        />
                        <div class="flex justify-end gap-1 mt-1">
                            <button class="btn btn-xs btn-primary btn-square" @click="submitTextItem">
                                <MdCheck class="size-3" />
                            </button>
                            <button class="btn btn-xs btn-ghost btn-square" @click="activeTextInput = null">
                                <MdClose class="size-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center gap-3 text-base-content/50">
                    <span class="loading loading-spinner loading-lg"></span>
                    <span class="text-sm">Loading image...</span>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="px-5 py-3 bg-base-200/80 border-t border-base-300 flex items-center justify-between shrink-0">
                <div class="text-xs text-base-content/60">
                    <span class="hidden sm:inline">Tip: Hold Shift or drag handles to scale. Ctrl+Z to undo.</span>
                </div>

                <div class="flex items-center gap-2">
                    <button class="btn btn-sm btn-ghost" @click="closeModal">
                        Cancel
                    </button>
                    <button class="btn btn-sm btn-primary gap-1.5 shadow-md" @click="saveAndExport">
                        <MdCheck class="size-4" />
                        Save & Use Image
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
