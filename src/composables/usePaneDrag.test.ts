import { describe, expect, it, beforeEach } from 'vitest';
import { usePaneDrag } from './usePaneDrag';

describe('usePaneDrag Composable', () => {
    beforeEach(() => {
        const { resetPreferences } = usePaneDrag();
        resetPreferences();
    });

    it('initializes with default sidebar width and reactive state', () => {
        const { sidebarWidth, paneOrder } = usePaneDrag();
        expect(sidebarWidth.value).toBe(240);
        expect(paneOrder.value).toEqual(['sidebar', 'chat', 'whiteboard']);
    });

    it('toggles drag mode active state', () => {
        const { isDragModeActive, toggleDragMode } = usePaneDrag();
        const initial = isDragModeActive.value;
        toggleDragMode();
        expect(isDragModeActive.value).toBe(!initial);
        toggleDragMode();
        expect(isDragModeActive.value).toBe(initial);
    });

    it('swaps any pane order positions and saves to localStorage', () => {
        const { paneOrder, swapPanes, startPaneSwapDrag, dropOnPane, getOrderedPanes } = usePaneDrag();

        // 1. Initial default order: sidebar | chat | whiteboard
        expect(paneOrder.value).toEqual(['sidebar', 'chat', 'whiteboard']);

        // 2. Swap sidebar and chat -> chat | sidebar | whiteboard
        swapPanes('sidebar', 'chat');
        expect(paneOrder.value).toEqual(['chat', 'sidebar', 'whiteboard']);

        // 3. Swap chat and whiteboard -> whiteboard | sidebar | chat
        swapPanes('chat', 'whiteboard');
        expect(paneOrder.value).toEqual(['whiteboard', 'sidebar', 'chat']);

        // 4. Test startPaneSwapDrag and dropOnPane: swap sidebar and chat -> whiteboard | chat | sidebar
        startPaneSwapDrag('sidebar');
        dropOnPane('chat');
        expect(paneOrder.value).toEqual(['whiteboard', 'chat', 'sidebar']);

        // 5. Test getOrderedPanes with arbitrary available panes
        expect(getOrderedPanes(['sidebar', 'chat'])).toEqual(['chat', 'sidebar']);
        expect(getOrderedPanes(['whiteboard', 'chat', 'sidebar'])).toEqual(['whiteboard', 'chat', 'sidebar']);

        // Check persistence in localStorage
        const saved = JSON.parse(localStorage.getItem('oxy_layout_preferences_v2') || '{}');
        expect(saved.paneOrder).toEqual(['whiteboard', 'chat', 'sidebar']);
    });
});
