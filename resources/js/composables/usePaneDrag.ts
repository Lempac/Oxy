import { getCurrentInstance, onMounted, onUnmounted, ref, Ref } from 'vue';

export interface LayoutPreferences {
    sidebarWidth: number;
    chatPaneWidth: number;
    paneOrder: string[];
}

export interface UsePaneDragReturn {
    isDragModeActive: Ref<boolean>;
    isResizingSidebar: Ref<boolean>;
    isResizingSplit: Ref<boolean>;
    sidebarWidth: Ref<number>;
    chatPaneWidth: Ref<number>;
    paneOrder: Ref<string[]>;
    draggedPaneId: Ref<string | null>;
    toggleDragMode: () => void;
    swapPanes: (pane1: string, pane2: string) => void;
    startPaneSwapDrag: (paneId: string) => void;
    dropOnPane: (targetPaneId: string) => void;
    startSidebarResize: (event: PointerEvent) => void;
    startSplitResize: (event: PointerEvent, containerWidth: number) => void;
    resetPreferences: () => void;
}

const STORAGE_KEY = 'oxy_layout_preferences_v1';

const isDragModeActive = ref<boolean>(false);
const sidebarWidth = ref<number>(240);
const chatPaneWidth = ref<number>(50); // percentage in split view
const paneOrder = ref<string[]>(['sidebar', 'main', 'whiteboard']);
const draggedPaneId = ref<string | null>(null);

let isInitialized = false;

function loadPreferences(): void {
    if (typeof window === 'undefined') return;
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const data: Partial<LayoutPreferences> = JSON.parse(saved);
            if (typeof data.sidebarWidth === 'number') sidebarWidth.value = data.sidebarWidth;
            if (typeof data.chatPaneWidth === 'number') chatPaneWidth.value = data.chatPaneWidth;
            if (Array.isArray(data.paneOrder) && data.paneOrder.length > 0) paneOrder.value = data.paneOrder;
        }
    } catch {
        // Ignore JSON parse errors silently
    }
}

function savePreferences(): void {
    if (typeof window === 'undefined') return;
    try {
        const payload: LayoutPreferences = {
            sidebarWidth: sidebarWidth.value,
            chatPaneWidth: chatPaneWidth.value,
            paneOrder: paneOrder.value
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch {
        // Ignore localStorage write errors silently
    }
}

const handleKeyDown = (event: KeyboardEvent): void => {
    if (event.key === 'Alt') {
        isDragModeActive.value = true;
    }
};

const handleKeyUp = (event: KeyboardEvent): void => {
    if (event.key === 'Alt') {
        isDragModeActive.value = false;
    }
};

export function usePaneDrag(): UsePaneDragReturn {
    const isResizingSidebar = ref<boolean>(false);
    const isResizingSplit = ref<boolean>(false);
    let startX = 0;
    let initialWidth = 0;
    let initialChatWidth = 0;

    if (!isInitialized && typeof window !== 'undefined') {
        loadPreferences();
        isInitialized = true;
    }

    const toggleDragMode = (): void => {
        isDragModeActive.value = !isDragModeActive.value;
    };

    const swapPanes = (pane1: string, pane2: string): void => {
        const idx1 = paneOrder.value.indexOf(pane1);
        const idx2 = paneOrder.value.indexOf(pane2);
        if (idx1 !== -1 && idx2 !== -1 && idx1 !== idx2) {
            const updated = [...paneOrder.value];
            const temp = updated[idx1];
            updated[idx1] = updated[idx2];
            updated[idx2] = temp;
            paneOrder.value = updated;
            savePreferences();
        }
    };

    const startPaneSwapDrag = (paneId: string): void => {
        draggedPaneId.value = paneId;
    };

    const dropOnPane = (targetPaneId: string): void => {
        if (!draggedPaneId.value) return;
        if (draggedPaneId.value !== targetPaneId) {
            swapPanes(draggedPaneId.value, targetPaneId);
        }
        draggedPaneId.value = null;
    };

    // Border Gutter Resizer: Sidebar Width
    const startSidebarResize = (event: PointerEvent): void => {
        isResizingSidebar.value = true;
        startX = event.clientX;
        initialWidth = sidebarWidth.value;

        const onMove = (e: PointerEvent) => {
            if (!isResizingSidebar.value) return;
            const delta = e.clientX - startX;
            const newWidth = Math.max(160, Math.min(420, initialWidth + delta));
            sidebarWidth.value = newWidth;
        };

        const onEnd = () => {
            isResizingSidebar.value = false;
            savePreferences();
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup', onEnd);
        };

        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup', onEnd);
    };

    // Border Gutter Resizer: Split Chat/Whiteboard ratio
    const startSplitResize = (event: PointerEvent, containerWidth: number): void => {
        if (containerWidth <= 0) return;
        isResizingSplit.value = true;
        startX = event.clientX;
        initialChatWidth = chatPaneWidth.value;

        const onMove = (e: PointerEvent) => {
            if (!isResizingSplit.value) return;
            const delta = e.clientX - startX;
            const deltaPct = (delta / containerWidth) * 100;
            const newPct = Math.max(20, Math.min(80, initialChatWidth + deltaPct));
            chatPaneWidth.value = newPct;
        };

        const onEnd = () => {
            isResizingSplit.value = false;
            savePreferences();
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup', onEnd);
        };

        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup', onEnd);
    };

    const resetPreferences = (): void => {
        sidebarWidth.value = 240;
        chatPaneWidth.value = 50;
        paneOrder.value = ['sidebar', 'main', 'whiteboard'];
        savePreferences();
    };

    // Attach listeners
    if (typeof window !== 'undefined') {
        window.addEventListener('keydown', handleKeyDown);
        window.addEventListener('keyup', handleKeyUp);
    }

    if (getCurrentInstance()) {
        onMounted(() => {
            if (typeof window !== 'undefined') {
                window.addEventListener('keydown', handleKeyDown);
                window.addEventListener('keyup', handleKeyUp);
            }
        });

        onUnmounted(() => {
            if (typeof window !== 'undefined') {
                window.removeEventListener('keydown', handleKeyDown);
                window.removeEventListener('keyup', handleKeyUp);
            }
        });
    }

    return {
        isDragModeActive,
        isResizingSidebar,
        isResizingSplit,
        sidebarWidth,
        chatPaneWidth,
        paneOrder,
        draggedPaneId,
        toggleDragMode,
        swapPanes,
        startPaneSwapDrag,
        dropOnPane,
        startSidebarResize,
        startSplitResize,
        resetPreferences
    };
}
