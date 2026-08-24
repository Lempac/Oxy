import { onMounted, onUnmounted } from 'vue';
import pb from '@/pocketbase';

export function useServerEvents(
    serverId?: string | null,
    onServerUpdate?: () => void
) {
    let unsubServer: (() => void) | null = null;
    let unsubMembers: (() => void) | null = null;
    let unsubRoles: (() => void) | null = null;

    onMounted(async () => {
        if (!serverId) return;

        try {
            unsubServer = await pb.collection('servers').subscribe(serverId, () => {
                if (onServerUpdate) onServerUpdate();
            });

            unsubMembers = await pb.collection('members').subscribe('*', (e) => {
                if (e.record.server === serverId && onServerUpdate) {
                    onServerUpdate();
                }
            });

            unsubRoles = await pb.collection('roles').subscribe('*', (e) => {
                if (e.record.server === serverId && onServerUpdate) {
                    onServerUpdate();
                }
            });
        } catch (err) {
            console.error('Failed to subscribe to server realtime events:', err);
        }
    });

    onUnmounted(() => {
        if (unsubServer) unsubServer();
        if (unsubMembers) unsubMembers();
        if (unsubRoles) unsubRoles();
    });
}
