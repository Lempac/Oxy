import { computed, ComputedRef, ref, Ref } from 'vue';

export interface LayoutPreferences {
    paneOrder: string[];
    paneWidths: Record<string, number>;
}

export interface UsePaneDragReturn {
    isDragModeActive: Ref<boolean>;
    sidebarWidth: ComputedRef<number>;
    chatPaneWidth: ComputedRef<number>;
    paneOrder: Ref<string[]>;
    paneWidths: Ref<Record<string, number>>;
    draggedPaneId: Ref<string | null>;
    dragHoverPaneId: Ref<string | null>;
    getOrderedPanes: (availablePanes: string[]) => string[];
    getPaneStyle: (paneId: string, activePanes: string[]) => Record<string, string>;
    toggleDragMode: () => void;
    swapPanes: (pane1: string, pane2: string) => void;
    startPaneSwapDrag: (paneId: string) => void;
    endPaneSwapDrag: () => void;
    setDragHoverPane: (paneId: string | null) => void;
    dropOnPane: (targetPaneId: string) => void;
    startGutterResize: (event: PointerEvent, leftPaneId: string, rightPaneId: string, activePanes: string[]) => void;
    resetPreferences: () => void;
}

const STORAGE_KEY = 'oxy_layout_preferences_v2';

const DEFAULT_PANES = ['sidebar', 'chat', 'whiteboard'];
const DEFAULT_WIDTHS: Record<string, number> = {
    sidebar: 240,
    chat: 380,
    whiteboard: 550,
    main: 380,
};

const paneOrder = ref<string[]>([...DEFAULT_PANES]);
const paneWidths = ref<Record<string, number>>({ ...DEFAULT_WIDTHS });
const draggedPaneId = ref<string | null>(null);
const dragHoverPaneId = ref<string | null>(null);

let isInitialized = false;

function loadPreferences(): void {
    if (typeof window === 'undefined') return;
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const data: Partial<LayoutPreferences> = JSON.parse(saved);
            if (Array.isArray(data.paneOrder) && data.paneOrder.length > 0) {
                const merged: string[] = [];
                for (const p of data.paneOrder) {
                    const norm = p === 'main' ? 'chat' : p;
                    if (DEFAULT_PANES.includes(norm) && !merged.includes(norm)) {
                        merged.push(norm);
                    }
                }
                for (const p of DEFAULT_PANES) {
                    if (!merged.includes(p)) merged.push(p);
                }
                paneOrder.value = merged;
            }
            if (data.paneWidths && typeof data.paneWidths === 'object') {
                paneWidths.value = { ...DEFAULT_WIDTHS, ...data.paneWidths };
            }
        }
    } catch {
        // Ignore JSON parse errors silently
    }
}

function savePreferences(): void {
    if (typeof window === 'undefined') return;
    try {
        const payload: LayoutPreferences = {
            paneOrder: paneOrder.value,
            paneWidths: paneWidths.value,
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch {
        // Ignore localStorage write errors silently
    }
}

export function usePaneDrag(): UsePaneDragReturn {
    let isResizing = false;

    if (!isInitialized && typeof window !== 'undefined') {
        loadPreferences();
        isInitialized = true;
    }

    const isDragModeActive = ref<boolean>(false);
    const sidebarWidth = computed(() => paneWidths.value.sidebar || DEFAULT_WIDTHS.sidebar);
    const chatPaneWidth = computed(() => paneWidths.value.chat || DEFAULT_WIDTHS.chat);

    const toggleDragMode = (): void => {
        isDragModeActive.value = !isDragModeActive.value;
    };

    const getOrderedPanes = (availablePanes: string[]): string[] => {
        const normalizedAvailable = availablePanes.map(p => p === 'main' ? 'chat' : p);
        const result: string[] = [];

        // First add from stored paneOrder if in available
        for (const p of paneOrder.value) {
            if (normalizedAvailable.includes(p) && !result.includes(p)) {
                result.push(p);
            }
        }

        // Add any missing available panes
        for (const p of normalizedAvailable) {
            if (!result.includes(p)) {
                result.push(p);
            }
        }

        return result;
    };

    const getPaneStyle = (paneId: string, activePanes: string[]): Record<string, string> => {
        const norm = paneId === 'main' ? 'chat' : paneId;

        // Single pane on screen (e.g. maximized whiteboard or unauthenticated)
        if (activePanes.length === 1) {
            return {
                flex: '1 1 0%',
                width: '100%',
            };
        }

        // In 2-pane view (sidebar + chat/main):
        if (activePanes.length === 2) {
            if (norm === 'sidebar') {
                const width = paneWidths.value.sidebar || DEFAULT_WIDTHS.sidebar;
                return {
                    width: `${width}px`,
                    flexShrink: '0',
                    flexGrow: '0',
                    minWidth: '160px',
                    maxWidth: '460px',
                };
            }
            // The other pane (chat or main) takes all remaining space
            return {
                flex: '1 1 0%',
                minWidth: '220px',
            };
        }

        // In 3-pane view (sidebar + chat + whiteboard):
        if (norm === 'sidebar') {
            const width = paneWidths.value.sidebar || DEFAULT_WIDTHS.sidebar;
            return {
                width: `${width}px`,
                flexShrink: '0',
                flexGrow: '0',
                minWidth: '160px',
                maxWidth: '460px',
            };
        }

        if (norm === 'chat') {
            const width = paneWidths.value.chat || DEFAULT_WIDTHS.chat;
            return {
                width: `${width}px`,
                flexShrink: '0',
                flexGrow: '0',
                minWidth: '220px',
                maxWidth: '750px',
            };
        }

        // Whiteboard takes all remaining flex space
        return {
            flex: '1 1 0%',
            minWidth: '250px',
        };
    };

    const swapPanes = (pane1: string, pane2: string): void => {
        const k1 = pane1 === 'main' ? 'chat' : pane1;
        const k2 = pane2 === 'main' ? 'chat' : pane2;

        const idx1 = paneOrder.value.indexOf(k1);
        const idx2 = paneOrder.value.indexOf(k2);

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
        draggedPaneId.value = paneId === 'main' ? 'chat' : paneId;
    };

    const endPaneSwapDrag = (): void => {
        draggedPaneId.value = null;
        dragHoverPaneId.value = null;
    };

    const setDragHoverPane = (paneId: string | null): void => {
        dragHoverPaneId.value = paneId ? (paneId === 'main' ? 'chat' : paneId) : null;
    };

    const dropOnPane = (targetPaneId: string): void => {
        const target = targetPaneId === 'main' ? 'chat' : targetPaneId;
        if (draggedPaneId.value && draggedPaneId.value !== target) {
            swapPanes(draggedPaneId.value, target);
        }
        draggedPaneId.value = null;
        dragHoverPaneId.value = null;
    };

    const startGutterResize = (
        event: PointerEvent,
        leftPaneId: string,
        rightPaneId: string,
        activePanes: string[]
    ): void => {
        if (isResizing) return;
        isResizing = true;

        const normLeft = leftPaneId === 'main' ? 'chat' : leftPaneId;
        const normRight = rightPaneId === 'main' ? 'chat' : rightPaneId;

        const startX = event.clientX;
        const initialSidebarWidth = paneWidths.value.sidebar || DEFAULT_WIDTHS.sidebar;
        const initialChatWidth = paneWidths.value.chat || DEFAULT_WIDTHS.chat;

        const minSidebar = 160;
        const maxSidebar = 460;
        const minChat = 220;
        const maxChat = 750;

        const onMove = (e: PointerEvent) => {
            if (!isResizing) return;
            const delta = e.clientX - startX;

            // 2-pane view (sidebar + chat/main)
            if (activePanes.length === 2) {
                if (normLeft === 'sidebar') {
                    // sidebar | chat -> dragging right expands sidebar
                    const newW = Math.max(minSidebar, Math.min(maxSidebar, initialSidebarWidth + delta));
                    paneWidths.value.sidebar = newW;
                } else if (normRight === 'sidebar') {
                    // chat | sidebar -> dragging right shrinks sidebar, dragging left expands sidebar
                    const newW = Math.max(minSidebar, Math.min(maxSidebar, initialSidebarWidth - delta));
                    paneWidths.value.sidebar = newW;
                }
                return;
            }

            // 3-pane view (sidebar, chat, whiteboard)
            if (normLeft === 'sidebar' && normRight === 'chat') {
                const newW = Math.max(minSidebar, Math.min(maxSidebar, initialSidebarWidth + delta));
                paneWidths.value.sidebar = newW;
            } else if (normLeft === 'chat' && normRight === 'sidebar') {
                const newW = Math.max(minChat, Math.min(maxChat, initialChatWidth + delta));
                paneWidths.value.chat = newW;
            } else if (normLeft === 'chat' && normRight === 'whiteboard') {
                const newW = Math.max(minChat, Math.min(maxChat, initialChatWidth + delta));
                paneWidths.value.chat = newW;
            } else if (normLeft === 'whiteboard' && normRight === 'chat') {
                const newW = Math.max(minChat, Math.min(maxChat, initialChatWidth - delta));
                paneWidths.value.chat = newW;
            } else if (normLeft === 'sidebar' && normRight === 'whiteboard') {
                const newW = Math.max(minSidebar, Math.min(maxSidebar, initialSidebarWidth + delta));
                paneWidths.value.sidebar = newW;
            } else if (normLeft === 'whiteboard' && normRight === 'sidebar') {
                const newW = Math.max(minSidebar, Math.min(maxSidebar, initialSidebarWidth - delta));
                paneWidths.value.sidebar = newW;
            }
        };

        const onEnd = () => {
            isResizing = false;
            savePreferences();
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup', onEnd);
        };

        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup', onEnd);
    };

    const resetPreferences = (): void => {
        paneOrder.value = [...DEFAULT_PANES];
        paneWidths.value = { ...DEFAULT_WIDTHS };
        draggedPaneId.value = null;
        dragHoverPaneId.value = null;
        savePreferences();
    };

    return {
        isDragModeActive,
        sidebarWidth,
        chatPaneWidth,
        paneOrder,
        paneWidths,
        draggedPaneId,
        dragHoverPaneId,
        getOrderedPanes,
        getPaneStyle,
        toggleDragMode,
        swapPanes,
        startPaneSwapDrag,
        endPaneSwapDrag,
        setDragHoverPane,
        dropOnPane,
        startGutterResize,
        resetPreferences,
    };
}
