import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { useServerEvents } from './useServerEvents';
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

describe('useServerEvents', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    const createWrapper = (serverId: number | null) => {
        return mount(defineComponent({
            setup() {
                useServerEvents(serverId);
                return () => {};
            }
        }));
    };

    it('does not subscribe if serverId is missing', () => {
        const wrapper = createWrapper(null);
        expect(mockPrivate).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('subscribes to server and role events on mount', () => {
        createWrapper(123);
        
        expect(mockPrivate).toHaveBeenCalledWith('servers.123');
        expect(mockPrivate).toHaveBeenCalledWith('roles.123');
        
        // Servers
        expect(mockListen).toHaveBeenCalledWith('.ServerJoined', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.ServerLeft', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.ServerEdited', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.UserStatusUpdated', expect.any(Function));
        
        // Roles
        expect(mockListen).toHaveBeenCalledWith('.RoleDeleted', expect.any(Function));
        expect(mockListen).toHaveBeenCalledWith('.RoleEdited', expect.any(Function));
    });

    it('unsubscribes from events on unmount', () => {
        const wrapper = createWrapper(123);
        wrapper.unmount();
        
        // Check stopListening was called for each event
        expect(mockStopListening).toHaveBeenCalledWith('.ServerJoined', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.ServerLeft', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.ServerEdited', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.UserStatusUpdated', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.RoleDeleted', expect.any(Function));
        expect(mockStopListening).toHaveBeenCalledWith('.RoleEdited', expect.any(Function));
    });

    it('triggers router.reload when events are fired', () => {
        createWrapper(123);
        
        // Find the callback for ServerJoined
        const serverJoinedCall = mockListen.mock.calls.find(call => call[0] === '.ServerJoined');
        expect(serverJoinedCall).toBeDefined();
        
        // Execute the callback
        serverJoinedCall[1]();
        
        expect(router.reload).toHaveBeenCalledWith({ only: ['selected_server'] });
        
        // Find the callback for ServerEdited
        const serverEditedCall = mockListen.mock.calls.find(call => call[0] === '.ServerEdited');
        
        // Execute the callback
        serverEditedCall[1]();
        
        expect(router.reload).toHaveBeenCalledWith({ only: ['servers', 'selected_server'] });
    });
});
