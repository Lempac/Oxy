import { onMounted, onUnmounted } from 'vue';
import pb from '@/pocketbase';

export function useMessageEvents(
    channelId?: string | null,
    onMessageCreated?: (message: Record<string, unknown>) => void,
    onMessageUpdated?: (message: Record<string, unknown>) => void,
    onMessageDeleted?: (messageId: string) => void
) {
    let unsubscribe: (() => void) | null = null;

    onMounted(async () => {
        if (!channelId) return;

        try {
            unsubscribe = await pb.collection('messages').subscribe('*', (e) => {
                if (e.record.channel !== channelId) return;

                if (e.action === 'create' && onMessageCreated) {
                    onMessageCreated(e.record);
                } else if (e.action === 'update' && onMessageUpdated) {
                    onMessageUpdated(e.record);
                } else if (e.action === 'delete' && onMessageDeleted) {
                    onMessageDeleted(e.record.id);
                }
            });
        } catch (err) {
            console.error('Failed to subscribe to message realtime events:', err);
        }
    });

    onUnmounted(() => {
        if (unsubscribe) {
            unsubscribe();
        }
    });
}
