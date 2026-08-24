import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { useMessageEvents } from './useMessageEvents';
import { defineComponent } from 'vue';

const mockUnsubscribe = vi.fn();
const mockSubscribe = vi.fn().mockResolvedValue(mockUnsubscribe);

vi.mock('@/pocketbase', () => ({
    default: {
        collection: () => ({
            subscribe: mockSubscribe
        })
    }
}));

describe('useMessageEvents', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    const createWrapper = (channelId: string | null, onCreated = vi.fn(), onUpdated = vi.fn(), onDeleted = vi.fn()) => {
        return mount(defineComponent({
            setup() {
                useMessageEvents(channelId, onCreated, onUpdated, onDeleted);
                return () => {};
            }
        }));
    };

    it('does not subscribe if channelId is missing', () => {
        const wrapper = createWrapper(null);
        expect(mockSubscribe).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('subscribes to message events on mount', () => {
        const wrapper = createWrapper('456');
        expect(mockSubscribe).toHaveBeenCalledWith('*', expect.any(Function));
        wrapper.unmount();
    });

    it('unsubscribes from events on unmount', async () => {
        const wrapper = createWrapper('456');
        await new Promise(resolve => setTimeout(resolve, 10));
        wrapper.unmount();
        expect(mockUnsubscribe).toHaveBeenCalled();
    });

    it('triggers callbacks when realtime events are received', async () => {
        const onCreated = vi.fn();
        const onUpdated = vi.fn();
        const onDeleted = vi.fn();

        createWrapper('456', onCreated, onUpdated, onDeleted);
        await new Promise(resolve => setTimeout(resolve, 10));

        const callback = mockSubscribe.mock.calls[0][1];

        callback({ action: 'create', record: { channel: '456', id: 'm1', content: 'hello' } });
        expect(onCreated).toHaveBeenCalledWith({ channel: '456', id: 'm1', content: 'hello' });

        callback({ action: 'update', record: { channel: '456', id: 'm1', content: 'updated' } });
        expect(onUpdated).toHaveBeenCalledWith({ channel: '456', id: 'm1', content: 'updated' });

        callback({ action: 'delete', record: { channel: '456', id: 'm1' } });
        expect(onDeleted).toHaveBeenCalledWith('m1');
    });
});
