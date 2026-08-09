import { onMounted, onUnmounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { fetchJson } from '@/bootstrap';
import { UserStatus } from '@/types';
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';

export function useUserPresence(enabled: boolean = true) {
    const page = usePage();
    const voiceState = useVoiceCallStateMachine();
    const currentStatus = ref<string | null>(null);
    let idleTimer: ReturnType<typeof setTimeout> | null = null;
    let activityDebounceTimer: ReturnType<typeof setTimeout> | null = null;
    const IDLE_TIMEOUT_MS = 2 * 60 * 1000; // 2 minutes of inactivity

    const sendStatus = async (status: string) => {
        if (!enabled || currentStatus.value === status) return;

        currentStatus.value = status;
        const authUser = page.props.user as any;
        if (authUser) {
            authUser.status = status;
        }

        try {
            await fetchJson('/profile/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status })
            });
        } catch {
            // Ignore status report network errors silently
        }
    };

    const resetIdleTimer = () => {
        if (idleTimer) clearTimeout(idleTimer);

        // If user is currently in AFK mode, do not auto-reset status
        if (voiceState.isAfk.value) return;

        const authUser = page.props.user as any;
        if (authUser?.status && authUser.status !== UserStatus.Online && authUser.status !== UserStatus.Idle) {
            // User set custom status (DND, Invisible, Offline) - do not override
            return;
        }

        if (document.visibilityState === 'visible' && document.hasFocus()) {
            if (currentStatus.value !== UserStatus.Online && authUser?.status !== UserStatus.Online) {
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
        const authUser = page.props.user as any;
        if (authUser?.status === UserStatus.Online) {
            sendStatus(UserStatus.Idle);
        }
    };

    const handleVisibilityChange = () => {
        if (document.visibilityState === 'hidden') {
            if (idleTimer) clearTimeout(idleTimer);
            const authUser = page.props.user as any;
            if (authUser?.status === UserStatus.Online) {
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
