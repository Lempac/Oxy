import { ref, computed } from 'vue';
import { Channel, User, VoiceParticipantState, VoiceParticipantStateType } from '@/types';
import { fetchJson } from '@/bootstrap';
import echo from '@/echo';

const ALLOWED_TRANSITIONS: Record<VoiceParticipantStateType, VoiceParticipantStateType[]> = {
    [VoiceParticipantState.Disconnected]: [VoiceParticipantState.Joining],
    [VoiceParticipantState.Joining]: [VoiceParticipantState.Connected, VoiceParticipantState.Disconnected],
    [VoiceParticipantState.Connected]: [
        VoiceParticipantState.Muted,
        VoiceParticipantState.Deafened,
        VoiceParticipantState.Leaving,
        VoiceParticipantState.Disconnected,
    ],
    [VoiceParticipantState.Muted]: [
        VoiceParticipantState.Connected,
        VoiceParticipantState.Deafened,
        VoiceParticipantState.Leaving,
        VoiceParticipantState.Disconnected,
    ],
    [VoiceParticipantState.Deafened]: [
        VoiceParticipantState.Connected,
        VoiceParticipantState.Muted,
        VoiceParticipantState.Leaving,
        VoiceParticipantState.Disconnected,
    ],
    [VoiceParticipantState.Leaving]: [VoiceParticipantState.Disconnected],
};

const currentState = ref<VoiceParticipantStateType>(VoiceParticipantState.Disconnected);
const isMutedExplicit = ref<boolean>(false);
const isDeafenedExplicit = ref<boolean>(false);
const activeChannel = ref<Channel | null>(null);
const activeServerId = ref<string | number | null>(null);
const activeStream = ref<MediaStream | null>(null);
const mediaRecorder = ref<MediaRecorder | null>(null);
const connectedUsers = ref<Record<string | number, User[]>>({});
const isAfk = ref<boolean>(false);
const previousStatusBeforeAfk = ref<string | null>(null);

export function useVoiceCallStateMachine(initialState?: VoiceParticipantStateType) {
    if (initialState !== undefined) {
        currentState.value = initialState;
        if (initialState === VoiceParticipantState.Muted) {
            isMutedExplicit.value = true;
        } else if (initialState === VoiceParticipantState.Deafened) {
            isDeafenedExplicit.value = true;
            isMutedExplicit.value = true;
        } else if (initialState === VoiceParticipantState.Connected) {
            isMutedExplicit.value = false;
            isDeafenedExplicit.value = false;
        } else if (initialState === VoiceParticipantState.Disconnected) {
            isMutedExplicit.value = false;
            isDeafenedExplicit.value = false;
            isAfk.value = false;
        }
    }

    const isDisconnected = computed(() => currentState.value === VoiceParticipantState.Disconnected);
    const isJoining = computed(() => currentState.value === VoiceParticipantState.Joining);
    const isConnected = computed(() => currentState.value === VoiceParticipantState.Connected || currentState.value === VoiceParticipantState.Muted || currentState.value === VoiceParticipantState.Deafened);
    const isDeafened = computed(() => isDeafenedExplicit.value || currentState.value === VoiceParticipantState.Deafened);
    const isMuted = computed(() => isMutedExplicit.value || currentState.value === VoiceParticipantState.Muted || isDeafened.value);
    const isLeaving = computed(() => currentState.value === VoiceParticipantState.Leaving);

    function canTransitionTo(targetState: VoiceParticipantStateType): boolean {
        return ALLOWED_TRANSITIONS[currentState.value]?.includes(targetState) ?? false;
    }

    function transitionTo(targetState: VoiceParticipantStateType): boolean {
        if (currentState.value === targetState) {
            return false;
        }

        if (!canTransitionTo(targetState)) {
            console.error(`Invalid voice state transition from ${currentState.value} to ${targetState}`);
            return false;
        }

        currentState.value = targetState;
        if (targetState === VoiceParticipantState.Muted) {
            isMutedExplicit.value = true;
        } else if (targetState === VoiceParticipantState.Deafened) {
            isDeafenedExplicit.value = true;
            isMutedExplicit.value = true;
        } else if (targetState === VoiceParticipantState.Connected) {
            isMutedExplicit.value = false;
            isDeafenedExplicit.value = false;
        } else if (targetState === VoiceParticipantState.Disconnected) {
            isMutedExplicit.value = false;
            isDeafenedExplicit.value = false;
            isAfk.value = false;
        }
        return true;
    }

    async function toggleAfk(currentAuthUserStatus?: string): Promise<boolean> {
        isAfk.value = !isAfk.value;

        if (isAfk.value) {
            // User went AFK: switch status to 'idle' ONLY if it was 'online'
            if (currentAuthUserStatus === 'online') {
                previousStatusBeforeAfk.value = 'online';
                try {
                    await fetchJson('/profile/status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: 'idle' })
                    });
                } catch {
                    // Ignore errors silently
                }
            } else {
                previousStatusBeforeAfk.value = null;
            }
        } else {
            // User returned from AFK: restore back to online if it was online previously
            if (previousStatusBeforeAfk.value === 'online') {
                previousStatusBeforeAfk.value = null;
                try {
                    await fetchJson('/profile/status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: 'online' })
                    });
                } catch {
                    // Ignore errors silently
                }
            }
        }

        return isAfk.value;
    }

    function getChannelUsers(channelId: string | number): User[] {
        return connectedUsers.value[channelId] || [];
    }

    function setChannelUsers(channelId: string | number, users: User[]) {
        connectedUsers.value[channelId] = users;
    }

    async function joinChannel(channel: Channel, serverId?: string | number, currentUser?: User | null): Promise<boolean> {
        if (activeChannel.value?.id === channel.id && (isConnected.value || isJoining.value)) {
            return true;
        }

        if (!isDisconnected.value) {
            await leaveChannel();
        }

        if (!transitionTo(VoiceParticipantState.Joining)) {
            return false;
        }

        activeChannel.value = channel;
        if (serverId) {
            activeServerId.value = serverId;
        }

        if (currentUser) {
            connectedUsers.value[channel.id] = [currentUser];
        }

        try {
            const echoInstance = echo || (typeof window !== 'undefined' ? (window as any).Echo : null);
            if (echoInstance) {
                echoInstance.join(`voices.${channel.id}`)
                    .here((users: any[]) => {
                        connectedUsers.value[channel.id] = users.map(u => u.user || u);
                    })
                    .joining((user: any) => {
                        const u = user.user || user;
                        const current = connectedUsers.value[channel.id] || [];
                        if (!current.some(x => String(x.id) === String(u.id))) {
                            connectedUsers.value[channel.id] = [...current, u];
                        }
                    })
                    .leaving((user: any) => {
                        const u = user.user || user;
                        const current = connectedUsers.value[channel.id] || [];
                        connectedUsers.value[channel.id] = current.filter(x => String(x.id) !== String(u.id));
                    });
            }

            if (typeof navigator !== 'undefined' && navigator.mediaDevices?.getUserMedia) {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                activeStream.value = stream;
                if (typeof MediaRecorder !== 'undefined') {
                    const recorder = new MediaRecorder(stream);
                    recorder.start(100);
                    mediaRecorder.value = recorder;
                }
            }
            transitionTo(VoiceParticipantState.Connected);
            return true;
        } catch (error) {
            console.error('Error accessing microphone:', error);
            activeStream.value = null;
            mediaRecorder.value = null;
            activeChannel.value = null;
            delete connectedUsers.value[channel.id];
            transitionTo(VoiceParticipantState.Disconnected);
            return false;
        }
    }

    async function leaveChannel(): Promise<boolean> {
        const chId = activeChannel.value?.id;
        const echoInstance = echo || (typeof window !== 'undefined' ? (window as any).Echo : null);
        if (chId && echoInstance) {
            try {
                echoInstance.leave(`voices.${chId}`);
            } catch {
                // Ignore leave error
            }
        }
        if (chId) {
            delete connectedUsers.value[chId];
        }

        isAfk.value = false;
        isMutedExplicit.value = false;
        isDeafenedExplicit.value = false;

        if (isDisconnected.value) {
            activeChannel.value = null;
            activeServerId.value = null;
            return true;
        }

        if (!transitionTo(VoiceParticipantState.Leaving)) {
            currentState.value = VoiceParticipantState.Disconnected;
            activeChannel.value = null;
            activeServerId.value = null;
            return true;
        }

        if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
            try {
                mediaRecorder.value.stop();
            } catch {
                // Ignore recorder stop error
            }
            mediaRecorder.value = null;
        }

        if (activeStream.value) {
            activeStream.value.getTracks().forEach(track => track.stop());
            activeStream.value = null;
        }

        activeChannel.value = null;
        activeServerId.value = null;
        transitionTo(VoiceParticipantState.Disconnected);
        return true;
    }

    function toggleMute(): boolean {
        if (!isConnected.value) return false;

        if (isMuted.value) {
            isMutedExplicit.value = false;
            isDeafenedExplicit.value = false;
            if (currentState.value === VoiceParticipantState.Muted || currentState.value === VoiceParticipantState.Deafened) {
                currentState.value = VoiceParticipantState.Connected;
            }
            if (activeStream.value) {
                activeStream.value.getAudioTracks().forEach(t => { t.enabled = true; });
            }
            return true;
        } else {
            isMutedExplicit.value = true;
            if (currentState.value === VoiceParticipantState.Connected) {
                currentState.value = VoiceParticipantState.Muted;
            }
            if (activeStream.value) {
                activeStream.value.getAudioTracks().forEach(t => { t.enabled = false; });
            }
            return true;
        }
    }

    function toggleDeafen(): boolean {
        if (!isConnected.value) return false;

        if (isDeafened.value) {
            isDeafenedExplicit.value = false;
            if (currentState.value === VoiceParticipantState.Deafened) {
                currentState.value = isMutedExplicit.value ? VoiceParticipantState.Muted : VoiceParticipantState.Connected;
            }
            return true;
        } else {
            isDeafenedExplicit.value = true;
            isMutedExplicit.value = true;
            currentState.value = VoiceParticipantState.Deafened;
            if (activeStream.value) {
                activeStream.value.getAudioTracks().forEach(t => { t.enabled = false; });
            }
            return true;
        }
    }

    function resetState(): void {
        currentState.value = VoiceParticipantState.Disconnected;
        activeChannel.value = null;
        activeServerId.value = null;
        connectedUsers.value = {};
        isAfk.value = false;
        isMutedExplicit.value = false;
        isDeafenedExplicit.value = false;
        previousStatusBeforeAfk.value = null;
        if (activeStream.value) {
            activeStream.value.getTracks().forEach(t => t.stop());
            activeStream.value = null;
        }
        mediaRecorder.value = null;
    }

    return {
        currentState,
        activeChannel,
        activeServerId,
        connectedUsers,
        isAfk,
        isDisconnected,
        isJoining,
        isConnected,
        isMuted,
        isDeafened,
        isLeaving,
        canTransitionTo,
        transitionTo,
        toggleAfk,
        getChannelUsers,
        setChannelUsers,
        joinChannel,
        leaveChannel,
        toggleMute,
        toggleDeafen,
        resetState,
    };
}
