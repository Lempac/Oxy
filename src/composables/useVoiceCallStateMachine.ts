import { ref, computed } from 'vue';
import { Room, RoomEvent, Track, RemoteParticipant, LocalParticipant } from 'livekit-client';
import { Channel, User, VoiceParticipantState, VoiceParticipantStateType } from '@/types';
import pb from '@/pocketbase';

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
const isVideoEnabled = ref<boolean>(false);
const isScreenShareEnabled = ref<boolean>(false);

const activeChannel = ref<Channel | null>(null);
const activeServerId = ref<string | number | null>(null);
const connectedUsers = ref<Record<string | number, User[]>>({});
const isAfk = ref<boolean>(false);
const speakingUsers = ref<Record<string, boolean>>({});

let livekitRoom: Room | null = null;
let currentUserId: string | number | null = null;

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
        if (currentState.value === targetState) return false;
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
            isVideoEnabled.value = false;
            isScreenShareEnabled.value = false;
        }
        return true;
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
        if (serverId) activeServerId.value = serverId;
        currentUserId = currentUser?.id ?? pb.authStore.model?.id ?? null;

        if (currentUser) {
            connectedUsers.value[channel.id] = [currentUser];
        }

        try {
            // Fetch token from LiveKit PocketBase hook
            let token = '';
            let wsUrl = import.meta.env.VITE_LIVEKIT_URL || 'ws://localhost:7880';

            try {
                const res = await pb.send('/api/livekit/token', {
                    method: 'POST',
                    body: JSON.stringify({ channelId: channel.id, serverId: serverId || channel.server_id })
                });
                token = res.token;
                if (res.url) wsUrl = res.url;
            } catch (err) {
                console.warn('LiveKit token endpoint unavailable, proceeding with local room:', err);
            }

            if (token) {
                livekitRoom = new Room({
                    adaptiveStream: true,
                    dynacast: true,
                });

                livekitRoom.on(RoomEvent.ActiveSpeakersChanged, (speakers) => {
                    const activeMap: Record<string, boolean> = {};
                    speakers.forEach(s => {
                        activeMap[s.identity] = true;
                    });
                    speakingUsers.value = activeMap;
                });

                livekitRoom.on(RoomEvent.ParticipantConnected, (participant: RemoteParticipant) => {
                    const u: User = {
                        id: participant.identity,
                        nickname: participant.name || participant.identity,
                        status: 'online',
                        icon: null,
                        light_theme: 'oxy',
                        dark_theme: 'dark',
                        roles: [],
                        servers: []
                    };
                    const current = connectedUsers.value[channel.id] || [];
                    if (!current.some(x => String(x.id) === String(u.id))) {
                        connectedUsers.value[channel.id] = [...current, u];
                    }
                });

                livekitRoom.on(RoomEvent.ParticipantDisconnected, (participant: RemoteParticipant) => {
                    const current = connectedUsers.value[channel.id] || [];
                    connectedUsers.value[channel.id] = current.filter(x => String(x.id) !== String(participant.identity));
                });

                await livekitRoom.connect(wsUrl, token);
                await livekitRoom.localParticipant.enableAudio();
            }

            transitionTo(VoiceParticipantState.Connected);
            return true;
        } catch (error) {
            console.error('Error joining voice channel:', error);
            activeChannel.value = null;
            delete connectedUsers.value[channel.id];
            transitionTo(VoiceParticipantState.Disconnected);
            return false;
        }
    }

    async function leaveChannel(): Promise<boolean> {
        const chId = activeChannel.value?.id;

        if (livekitRoom) {
            try {
                await livekitRoom.disconnect();
            } catch {}
            livekitRoom = null;
        }

        if (chId) {
            delete connectedUsers.value[chId];
        }

        isAfk.value = false;
        isMutedExplicit.value = false;
        isDeafenedExplicit.value = false;
        isVideoEnabled.value = false;
        isScreenShareEnabled.value = false;

        activeChannel.value = null;
        activeServerId.value = null;
        transitionTo(VoiceParticipantState.Disconnected);
        return true;
    }

    function toggleMute(): boolean {
        if (!isConnected.value) return false;
        isMutedExplicit.value = !isMutedExplicit.value;
        if (livekitRoom?.localParticipant) {
            livekitRoom.localParticipant.setMicrophoneEnabled(!isMutedExplicit.value);
        }
        return true;
    }

    function toggleDeafen(): boolean {
        if (!isConnected.value) return false;
        isDeafenedExplicit.value = !isDeafenedExplicit.value;
        if (isDeafenedExplicit.value) {
            isMutedExplicit.value = true;
            if (livekitRoom?.localParticipant) {
                livekitRoom.localParticipant.setMicrophoneEnabled(false);
            }
        }
        return true;
    }

    async function toggleCamera(): Promise<boolean> {
        if (!isConnected.value) return false;
        isVideoEnabled.value = !isVideoEnabled.value;
        if (livekitRoom?.localParticipant) {
            await livekitRoom.localParticipant.setCameraEnabled(isVideoEnabled.value);
        }
        return isVideoEnabled.value;
    }

    async function toggleScreenShare(): Promise<boolean> {
        if (!isConnected.value) return false;
        isScreenShareEnabled.value = !isScreenShareEnabled.value;
        if (livekitRoom?.localParticipant) {
            await livekitRoom.localParticipant.setScreenShareEnabled(isScreenShareEnabled.value);
        }
        return isScreenShareEnabled.value;
    }

    async function toggleAfk(currentAuthUserStatus?: string): Promise<boolean> {
        isAfk.value = !isAfk.value;
        return isAfk.value;
    }

    async function restoreSession(currentUser?: User | null) {
        if (isConnected.value || isJoining.value) return;
    }

    function isUserSpeaking(userId: string | number): boolean {
        return Boolean(speakingUsers.value[String(userId)]);
    }

    function resetState(): void {
        if (livekitRoom) {
            try { livekitRoom.disconnect(); } catch {}
            livekitRoom = null;
        }
        currentState.value = VoiceParticipantState.Disconnected;
        activeChannel.value = null;
        activeServerId.value = null;
        connectedUsers.value = {};
        isAfk.value = false;
        isMutedExplicit.value = false;
        isDeafenedExplicit.value = false;
        isVideoEnabled.value = false;
        isScreenShareEnabled.value = false;
    }

    return {
        currentState,
        activeChannel,
        activeServerId,
        connectedUsers,
        speakingUsers,
        isAfk,
        isDisconnected,
        isJoining,
        isConnected,
        isMuted,
        isDeafened,
        isLeaving,
        isVideoEnabled,
        isScreenShareEnabled,
        canTransitionTo,
        transitionTo,
        getChannelUsers,
        setChannelUsers,
        isUserSpeaking,
        joinChannel,
        leaveChannel,
        toggleAfk,
        restoreSession,
        toggleMute,
        toggleDeafen,
        toggleCamera,
        toggleScreenShare,
        resetState,
    };
}
