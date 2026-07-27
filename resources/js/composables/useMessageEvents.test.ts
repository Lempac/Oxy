import {beforeEach, describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import {useMessageEvents} from './useMessageEvents';
import {router} from '@inertiajs/vue3';
import {defineComponent} from 'vue';

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

describe('useMessageEvents', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    const createWrapper = (channelId: number | null) => {
        return mount(defineComponent({
            setup() {
                useMessageEvents(channelId);
                return () => {
                };
            }
        }));
    };

    it('does not subscribe if channelId is missing', () => {
        const wrapper = createWrapper(null);
        expect(mockPrivate).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('subscribes to message events on mount', () => {
        createWrapper(456);

        expect(mockPrivate).toHaveBeenCalledWith('messages.456');

        expect(mockListen).toHaveBeenCalledWith('.MessageCreated', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.MessageDeleted', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.MessageEdited', expect.any(Function));
    });

    it('unsubscribes from events on unmount', () => {
        const wrapper = createWrapper(456);
        wrapper.unmount();

        expect(mockStopListening).toHaveBeenCalledWith('.MessageCreated', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.MessageDeleted', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.MessageEdited', expect.any(Function));
    });
    
    it('triggers router.reload when events are fired', () => {
        createWrapper(456);

        const call = mockListen.mock.calls.find(call => call[0] === '.MessageCreated');
        call[1]();

        expect(router.reload).toHaveBeenCalledWith({only: ['messages']});
    });
});
