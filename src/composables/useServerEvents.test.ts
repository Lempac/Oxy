import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { useServerEvents } from './useServerEvents';
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

describe('useServerEvents', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    const createWrapper = (serverId: string | null, onUpdate = vi.fn()) => {
        return mount(defineComponent({
            setup() {
                useServerEvents(serverId, onUpdate);
                return () => {};
            }
        }));
    };

    it('does not subscribe if serverId is missing', () => {
        const wrapper = createWrapper(null);
        expect(mockSubscribe).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('subscribes to server and role events on mount', () => {
        createWrapper('123');
        expect(mockSubscribe).toHaveBeenCalled();
    });

    it('unsubscribes from events on unmount', async () => {
        const wrapper = createWrapper('123');
        await new Promise(resolve => setTimeout(resolve, 10));
        wrapper.unmount();
        expect(mockUnsubscribe).toHaveBeenCalled();
    });

    it('triggers callback when server events occur', async () => {
        const onUpdate = vi.fn();
        createWrapper('123', onUpdate);
        await new Promise(resolve => setTimeout(resolve, 10));

        const callback = mockSubscribe.mock.calls[0][1];
        if (typeof callback === 'function') callback({ action: 'update', record: { id: '123' } });

        expect(onUpdate).toHaveBeenCalled();
    });
});
