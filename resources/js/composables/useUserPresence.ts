import { onMounted, onUnmounted, ref } from 'vue';
import { fetchJson } from '@/bootstrap';
import { UserStatus } from '@/types';

export function useUserPresence(enabled: boolean = true) {
    const currentStatus = ref<string | null>(null);
    let idleTimer: ReturnType<typeof setTimeout> | null = null;
    const IDLE_TIMEOUT_MS = 2 * 60 * 1000; // 2 minutes of inactivity

    const sendStatus = async (status: string) => {
        if (!enabled || currentStatus.value === status) return;

        currentStatus.value = status;
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

        if (document.visibilityState === 'visible' && document.hasFocus()) {
            if (currentStatus.value !== UserStatus.Online) {
                sendStatus(UserStatus.Online);
            }
            idleTimer = setTimeout(() => {
                sendStatus(UserStatus.Idle);
            }, IDLE_TIMEOUT_MS);
        }
    };

    const handleFocus = () => {
        resetIdleTimer();
    };

    const handleBlur = () => {
        if (idleTimer) clearTimeout(idleTimer);
        sendStatus(UserStatus.Idle);
    };

    const handleVisibilityChange = () => {
        if (document.visibilityState === 'hidden') {
            if (idleTimer) clearTimeout(idleTimer);
            sendStatus(UserStatus.Idle);
        } else {
            resetIdleTimer();
        }
    };

    onMounted(() => {
        if (!enabled) return;

        // Set initial online status
        resetIdleTimer();

        window.addEventListener('focus', handleFocus);
        window.addEventListener('blur', handleBlur);
        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('mousemove', resetIdleTimer);
        window.addEventListener('keydown', resetIdleTimer);
    });

    onUnmounted(() => {
        if (idleTimer) clearTimeout(idleTimer);

        window.removeEventListener('focus', handleFocus);
        window.removeEventListener('blur', handleBlur);
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        window.removeEventListener('mousemove', resetIdleTimer);
        window.removeEventListener('keydown', resetIdleTimer);
    });
}
