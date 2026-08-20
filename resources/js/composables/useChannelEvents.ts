import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import echo from '@/echo';
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';

export function useChannelEvents(serverId?: string | null, onlyKeys: string[] = ['channels']) {
    const handleChannelChange = () => router.reload({ only: onlyKeys });
    const voiceState = useVoiceCallStateMachine();

    const handleVoiceStateChange = (event: { channelId: string | number; user: any; action: 'joined' | 'left' }) => {
        if (!event || !event.channelId) return;
        const currentUsers = voiceState.getChannelUsers(event.channelId);
        if (event.action === 'joined') {
            if (!currentUsers.some(u => String(u.id) === String(event.user.id))) {
                voiceState.setChannelUsers(event.channelId, [...currentUsers, event.user]);
            }
        } else if (event.action === 'left') {
            voiceState.setChannelUsers(event.channelId, currentUsers.filter(u => String(u.id) !== String(event.user.id)));
        }
    };

    onMounted(() => {
        if (!serverId) return;
        
        echo?.private(`channels.${serverId}`)
            .listen('.ChannelCreated', handleChannelChange)
            .listen('.ChannelEdited', handleChannelChange)
            .listen('.ChannelDeleted', handleChannelChange)
            .listen('.VoiceStateChanged', handleVoiceStateChange);
    });

    onUnmounted(() => {
        if (!serverId) return;
        
        echo?.private(`channels.${serverId}`)
            ?.stopListening('.ChannelCreated', handleChannelChange)
            ?.stopListening('.ChannelEdited', handleChannelChange)
            ?.stopListening('.ChannelDeleted', handleChannelChange)
            ?.stopListening('.VoiceStateChanged', handleVoiceStateChange);
    });
}
