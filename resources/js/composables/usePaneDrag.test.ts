import { describe, expect, it } from 'vitest';
import { usePaneDrag } from './usePaneDrag';

describe('usePaneDrag Composable', () => {
    it('initializes with default sidebar width and drag inactive', () => {
        const { isDragModeActive, sidebarWidth } = usePaneDrag();
        expect(sidebarWidth.value).toBe(240);
        expect(isDragModeActive.value).toBe(false);
    });

    it('toggles drag mode active state', () => {
        const { isDragModeActive, toggleDragMode } = usePaneDrag();
        const initial = isDragModeActive.value;
        toggleDragMode();
        expect(isDragModeActive.value).toBe(!initial);
        toggleDragMode();
        expect(isDragModeActive.value).toBe(initial);
    });

    it('responds to keydown Alt key events', () => {
        const { isDragModeActive } = usePaneDrag();
        
        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Alt' }));
        expect(isDragModeActive.value).toBe(true);

        window.dispatchEvent(new KeyboardEvent('keyup', { key: 'Alt' }));
        expect(isDragModeActive.value).toBe(false);
    });

    it('swaps pane order positions when dragging and dropping', () => {
        const { isDragModeActive, paneOrder, swapPanes, startPaneSwapDrag, dropOnPane } = usePaneDrag();
        
        // Enable drag mode
        isDragModeActive.value = true;
        expect(paneOrder.value).toEqual(['sidebar', 'main', 'whiteboard']);

        // Swap sidebar and main
        swapPanes('sidebar', 'main');
        expect(paneOrder.value).toEqual(['main', 'sidebar', 'whiteboard']);

        // Test startPaneSwapDrag and dropOnPane
        startPaneSwapDrag('sidebar');
        dropOnPane('whiteboard');
        expect(paneOrder.value).toEqual(['main', 'whiteboard', 'sidebar']);
    });
});
