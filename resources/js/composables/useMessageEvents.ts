import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import echo from '@/echo';

export function useMessageEvents(channelId?: number | null) {
    const handleMessageChange = () => router.reload({ only: ['messages'] });

    onMounted(() => {
        if (!channelId) return;
        
        echo?.private(`messages.${channelId}`)
            .listen('.MessageCreated', handleMessageChange)
            .listen('.MessageDeleted', handleMessageChange)
            .listen('.MessageEdited', handleMessageChange);
    });

    onUnmounted(() => {
        if (!channelId) return;
        
        echo?.private(`messages.${channelId}`)
            ?.stopListening('.MessageCreated', handleMessageChange)
            ?.stopListening('.MessageDeleted', handleMessageChange)
            ?.stopListening('.MessageEdited', handleMessageChange);
    });
}
