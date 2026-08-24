import { onMounted, onUnmounted } from 'vue';
import pb from '@/pocketbase';
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';

export function useChannelEvents(
    serverId?: string | null,
    onChannelChange?: () => void
) {
    const voiceState = useVoiceCallStateMachine();
    let unsubscribe: (() => void) | null = null;

    onMounted(async () => {
        if (!serverId) return;

        try {
            unsubscribe = await pb.collection('channels').subscribe('*', (e) => {
                if (e.record.server === serverId && onChannelChange) {
                    onChannelChange();
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
