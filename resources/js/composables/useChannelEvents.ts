import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import echo from '@/echo';

export function useChannelEvents(serverId?: string | null, onlyKeys: string[] = ['channels']) {
    const handleChannelChange = () => router.reload({ only: onlyKeys });

    onMounted(() => {
        if (!serverId) return;
        
        echo?.private(`channels.${serverId}`)
            .listen('.ChannelCreated', handleChannelChange)
            .listen('.ChannelEdited', handleChannelChange)
            .listen('.ChannelDeleted', handleChannelChange);
    });

    onUnmounted(() => {
        if (!serverId) return;
        
        echo?.private(`channels.${serverId}`)
            ?.stopListening('.ChannelCreated', handleChannelChange)
            ?.stopListening('.ChannelEdited', handleChannelChange)
            ?.stopListening('.ChannelDeleted', handleChannelChange);
    });
}
