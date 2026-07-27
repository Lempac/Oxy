import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { useChannelEvents } from './useChannelEvents';
import { router } from '@inertiajs/vue3';
import { defineComponent } from 'vue';

const mockListen = vi.fn().mockReturnThis();
const mockStopListening = vi.fn().mockReturnThis();
const mockPrivate = vi.fn(() => ({
    listen: mockListen,
    stopListening: mockStopListening
}));

vi.mock('@/echo', () => {
    return {
        default: {
            private: (...args: unknown[]) => mockPrivate(...args)
        }
    };
});

vi.mock('@inertiajs/vue3', () => ({
    router: {
        reload: vi.fn()
    }
}));

describe('useChannelEvents', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    const createWrapper = (serverId: number | null, onlyKeys?: string[]) => {
        return mount(defineComponent({
            setup() {
                useChannelEvents(serverId, onlyKeys);
                return () => {};
            }
        }));
    };

    it('does not subscribe if serverId is missing', () => {
        const wrapper = createWrapper(null);
        expect(mockPrivate).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('subscribes to channel events on mount', () => {
        createWrapper(123);
        
        expect(mockPrivate).toHaveBeenCalledWith('channels.123');
        
        expect(mockListen).toHaveBeenCalledWith('.ChannelCreated', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.ChannelEdited', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.ChannelDeleted', expect.any(Function));
    });

    it('unsubscribes from events on unmount', () => {
        const wrapper = createWrapper(123);
        wrapper.unmount();
        
        expect(mockStopListening).toHaveBeenCalledWith('.ChannelCreated', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.ChannelEdited', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.ChannelDeleted', expect.any(Function));
    });

    it('triggers router.reload with default onlyKeys', () => {
        createWrapper(123);
        
        const call = mockListen.mock.calls.find(call => call[0] === '.ChannelCreated');
        call[1]();
        
        expect(router.reload).toHaveBeenCalledWith({ only: ['channels'] });
    });

    it('triggers router.reload with custom onlyKeys', () => {
        createWrapper(123, ['channels', 'selected_channel']);
        
        const call = mockListen.mock.calls.find(call => call[0] === '.ChannelCreated');
        call[1]();
        
        expect(router.reload).toHaveBeenCalledWith({ only: ['channels', 'selected_channel'] });
    });
});
