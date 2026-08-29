import { ref, computed } from 'vue';
import pb from '@/pocketbase';
import { Server } from '@/types';

const servers = ref<Server[]>([]);
const loading = ref(false);
const initialized = ref(false);

export function useServerStore() {
    const fetchServers = async (force = false) => {
        if (!pb.authStore.model?.id) {
            servers.value = [];
            initialized.value = false;
            return;
        }

        if (initialized.value && !force) {
            return;
        }

        loading.value = true;
        try {
            const memberRecords = await pb.collection('members').getFullList({
                filter: `user = "${pb.authStore.model.id}"`,
                expand: 'server',
                requestKey: null
            });
            const list: Server[] = [];
            for (const m of memberRecords) {
                if (m.expand?.server) {
                    const s = m.expand.server;
                    list.push({
                        id: s.id,
                        name: s.name,
                        slug: s.slug,
                        description: s.description,
                        owner: s.owner,
                        enable_whiteboard: s.enable_whiteboard,
                        icon: s.icon,
                        route_key: s.id
                    } as unknown as Server);
                }
            }
            const owned = await pb.collection('servers').getFullList({
                filter: `owner = "${pb.authStore.model.id}"`,
                requestKey: null
            });
            for (const s of owned) {
                if (!list.some(existing => existing.id === s.id)) {
                    list.push({
                        id: s.id,
                        name: s.name,
                        slug: s.slug,
                        description: s.description,
                        owner: s.owner,
                        enable_whiteboard: s.enable_whiteboard,
                        icon: s.icon,
                        route_key: s.id
                    } as unknown as Server);
                }
            }
            servers.value = list;
            initialized.value = true;
        } catch (err) {
            console.error('[ServerStore] Failed to fetch user servers:', err);
        } finally {
            loading.value = false;
        }
    };

    const clearServers = () => {
        servers.value = [];
        initialized.value = false;
    };

    return {
        servers: computed(() => servers.value),
        loading: computed(() => loading.value),
        initialized: computed(() => initialized.value),
        fetchServers,
        clearServers
    };
}
