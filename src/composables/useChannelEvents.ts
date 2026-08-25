import { onMounted, onUnmounted } from 'vue';
import pb from '@/pocketbase';

export function useChannelEvents(
    serverId?: string | null,
    onChannelChangeOrOnlyKeys?: string[] | (() => void)
) {
    let unsubscribe: (() => void) | null = null;

    onMounted(async () => {
        if (!serverId) return;

        try {
            unsubscribe = await pb.collection('channels').subscribe('*', (e) => {
                if (e.record.server === serverId && typeof onChannelChangeOrOnlyKeys === 'function') {
                    onChannelChangeOrOnlyKeys();
                }
            });
        } catch (err) {
            console.error('Failed to subscribe to channel realtime events:', err);
        }
    });

    onUnmounted(() => {
        if (unsubscribe) {
            unsubscribe();
        }
    });
}
