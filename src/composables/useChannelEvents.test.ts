import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { useChannelEvents } from './useChannelEvents';
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

describe('useChannelEvents', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    const createWrapper = (serverId: string | null, onChannelChange = vi.fn()) => {
        return mount(defineComponent({
            setup() {
                useChannelEvents(serverId, onChannelChange);
                return () => {};
            }
        }));
    };

    it('does not subscribe if serverId is missing', () => {
        const wrapper = createWrapper(null);
        expect(mockSubscribe).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('subscribes to channel events on mount', () => {
        createWrapper('123');
        expect(mockSubscribe).toHaveBeenCalledWith('*', expect.any(Function));
    });

    it('unsubscribes from events on unmount', async () => {
        const wrapper = createWrapper('123');
        await new Promise(resolve => setTimeout(resolve, 10));
        wrapper.unmount();
        expect(mockUnsubscribe).toHaveBeenCalled();
    });

    it('triggers callback when channel events are fired for the server', async () => {
        const onChannelChange = vi.fn();
        createWrapper('123', onChannelChange);
        await new Promise(resolve => setTimeout(resolve, 10));

        const callback = mockSubscribe.mock.calls[0][1];
        callback({ action: 'create', record: { server: '123', id: 'c1' } });

        expect(onChannelChange).toHaveBeenCalled();
    });
});
