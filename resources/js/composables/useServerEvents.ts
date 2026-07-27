import {onMounted, onUnmounted} from 'vue';
import {router} from '@inertiajs/vue3';
import echo from '@/echo';

export function useServerEvents(serverId?: number | null) {
    const handleServerJoinedOrLeft = () => router.reload({only: ['selected_server']});
    const handleServerEdited = () => router.reload({only: ['servers', 'selected_server']});
    const handleRoleEditedOrDeleted = () => router.reload({only: ['selected_server']});

    onMounted(() => {
        if (!serverId) return;

        echo?.private(`servers.${serverId}`)
            .listen('.ServerJoined', handleServerJoinedOrLeft)
            .listen('.ServerLeft', handleServerJoinedOrLeft)
            .listen('.ServerEdited', handleServerEdited);

        echo?.private(`roles.${serverId}`)
            .listen('.RoleDeleted', handleRoleEditedOrDeleted)
            .listen('.RoleEdited', handleRoleEditedOrDeleted);
    });

    onUnmounted(() => {
        if (!serverId) return;

        echo?.private(`servers.${serverId}`)
            ?.stopListening('.ServerJoined', handleServerJoinedOrLeft)
            ?.stopListening('.ServerLeft', handleServerJoinedOrLeft)
            ?.stopListening('.ServerEdited', handleServerEdited);

        echo?.private(`roles.${serverId}`)
            ?.stopListening('.RoleDeleted', handleRoleEditedOrDeleted)
            ?.stopListening('.RoleEdited', handleRoleEditedOrDeleted);
    });
}
