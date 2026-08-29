import { onMounted, onUnmounted, ref } from 'vue';
import pb from '@/pocketbase';
import { UserStatus } from '@/types';
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';

export function useUserPresence(enabled: boolean = true) {
    const voiceState = useVoiceCallStateMachine();
    const currentStatus = ref<string | null>(null);
    let idleTimer: ReturnType<typeof setTimeout> | null = null;
    let activityDebounceTimer: ReturnType<typeof setTimeout> | null = null;
    const IDLE_TIMEOUT_MS = 2 * 60 * 1000; // 2 minutes of inactivity

    const sendStatus = async (status: string) => {
        if (!enabled || currentStatus.value === status) return;
        if (!pb.authStore.model?.id) return;

        currentStatus.value = status;
        try {
            await pb.collection('users').update(pb.authStore.model.id, { status }, { requestKey: null });
        } catch (err: unknown) {
            const error = err as { status?: number };
            if (error?.status === 404) {
                // Stale auth token from re-seeded database; clear session
                pb.authStore.clear();
            }
        }
    };

    const resetIdleTimer = () => {
        if (idleTimer) clearTimeout(idleTimer);

        if (voiceState.isAfk.value) return;

        const authUserStatus = pb.authStore.model?.status;
        if (authUserStatus && authUserStatus !== UserStatus.Online && authUserStatus !== UserStatus.Idle) {
            return;
        }

        if (document.visibilityState === 'visible' && document.hasFocus()) {
            if (currentStatus.value !== UserStatus.Online) {
                sendStatus(UserStatus.Online);
            }
            idleTimer = setTimeout(() => {
                if (!voiceState.isAfk.value) {
                    sendStatus(UserStatus.Idle);
                }
            }, IDLE_TIMEOUT_MS);
        }
    };

    const onUserActivity = () => {
        if (activityDebounceTimer) return;
        activityDebounceTimer = setTimeout(() => {
            activityDebounceTimer = null;
            resetIdleTimer();
        }, 500);
    };

    const handleFocus = () => {
        resetIdleTimer();
    };

    const handleBlur = () => {
        if (idleTimer) clearTimeout(idleTimer);
        if (currentStatus.value === UserStatus.Online) {
            sendStatus(UserStatus.Idle);
        }
    };

    const handleVisibilityChange = () => {
        if (document.visibilityState === 'hidden') {
            if (idleTimer) clearTimeout(idleTimer);
            if (currentStatus.value === UserStatus.Online) {
                sendStatus(UserStatus.Idle);
            }
        } else {
            resetIdleTimer();
        }
    };

    onMounted(() => {
        if (!enabled) return;

        resetIdleTimer();

        window.addEventListener('focus', handleFocus);
        window.addEventListener('blur', handleBlur);
        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('mousemove', onUserActivity);
        window.addEventListener('keydown', onUserActivity);
    });

    onUnmounted(() => {
        if (idleTimer) clearTimeout(idleTimer);
        if (activityDebounceTimer) clearTimeout(activityDebounceTimer);

        window.removeEventListener('focus', handleFocus);
        window.removeEventListener('blur', handleBlur);
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        window.removeEventListener('mousemove', onUserActivity);
        window.removeEventListener('keydown', onUserActivity);
    });
}
