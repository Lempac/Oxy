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

const peerConnections: Record<string, RTCPeerConnection> = {};
const remoteAudioElements: Record<string, HTMLAudioElement> = {};
let activePresenceChannel: any = null;
let currentUserId: string | number | null = null;

const RTC_CONFIG: RTCConfiguration = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' },
    ],
};

function createPeerConnection(peerId: string): RTCPeerConnection {
    if (peerConnections[peerId]) {
        return peerConnections[peerId];
    }

    const pc = new RTCPeerConnection(RTC_CONFIG);
    peerConnections[peerId] = pc;

    if (activeStream.value) {
        activeStream.value.getTracks().forEach((track) => {
            pc.addTrack(track, activeStream.value!);
        });
    }

    pc.onicecandidate = (event) => {
        if (event.candidate && activePresenceChannel && currentUserId) {
            activePresenceChannel.whisper('webrtc-signal', {
                from: currentUserId,
                to: peerId,
                type: 'candidate',
                candidate: event.candidate,
            });
        }
    };

    pc.ontrack = (event) => {
        if (typeof Audio === 'undefined') return;
        let audio = remoteAudioElements[peerId];
        if (!audio) {
            audio = new Audio();
            audio.autoplay = true;
            remoteAudioElements[peerId] = audio;
        }
        audio.srcObject = event.streams[0];
        audio.muted = isDeafenedExplicit.value || currentState.value === VoiceParticipantState.Deafened;
        audio.play().catch(() => {});
    };

    pc.onconnectionstatechange = () => {
        if (pc.connectionState === 'failed' || pc.connectionState === 'closed') {
            cleanupPeer(peerId);
        }
    };

    return pc;
}

function cleanupPeer(peerId: string) {
    if (peerConnections[peerId]) {
        try {
            peerConnections[peerId].close();
        } catch {
            // Ignore close error
        }
        delete peerConnections[peerId];
    }
    if (remoteAudioElements[peerId]) {
        try {
            remoteAudioElements[peerId].pause();
            remoteAudioElements[peerId].srcObject = null;
        } catch {
            // Ignore pause error
        }
        delete remoteAudioElements[peerId];
    }
}

function broadcastVoiceState(): void {
    if (activePresenceChannel && currentUserId) {
        try {
            const isMutedVal = isMutedExplicit.value || currentState.value === VoiceParticipantState.Muted;
            const isDeafenedVal = isDeafenedExplicit.value || currentState.value === VoiceParticipantState.Deafened;
            activePresenceChannel.whisper('voice-state-update', {
                user_id: currentUserId,
                is_muted: isMutedVal,
                is_deafened: isDeafenedVal,
                is_afk: isAfk.value,
            });
        } catch {
            // Ignore whisper error
        }
    }
}

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

        broadcastVoiceState();
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

        currentUserId = currentUser?.id ?? null;

        if (currentUser) {
            connectedUsers.value[channel.id] = [currentUser];
        }

        try {
            if (typeof navigator !== 'undefined' && navigator.mediaDevices?.getUserMedia) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    activeStream.value = stream;
                    if (typeof MediaRecorder !== 'undefined') {
                        const recorder = new MediaRecorder(stream);
                        recorder.start(100);
                        mediaRecorder.value = recorder;
                    }
                } catch (micErr) {
                    console.warn('Microphone access not granted or unavailable:', micErr);
                }
            }

            const echoInstance = echo || (typeof window !== 'undefined' ? (window as any).Echo : null);
            if (echoInstance) {
                activePresenceChannel = echoInstance.join(`voices.${channel.id}`);

                activePresenceChannel
                    .here(async (users: any[]) => {
                        const mapped = users.map((u) => u.user || u);
                        connectedUsers.value[channel.id] = mapped;

                        if (currentUserId && typeof RTCPeerConnection !== 'undefined') {
                            for (const u of mapped) {
                                if (String(u.id) !== String(currentUserId)) {
                                    try {
                                        const pc = createPeerConnection(String(u.id));
                                        const offer = await pc.createOffer();
                                        await pc.setLocalDescription(offer);
                                        activePresenceChannel.whisper('webrtc-signal', {
                                            from: currentUserId,
                                            to: String(u.id),
                                            type: 'offer',
                                            offer,
                                        });
                                    } catch (e) {
                                        console.error('WebRTC offer error:', e);
                                    }
                                }
                            }
                        }
                    })
                    .joining((user: any) => {
                        const u = user.user || user;
                        const current = connectedUsers.value[channel.id] || [];
                        if (!current.some((x) => String(x.id) === String(u.id))) {
                            connectedUsers.value[channel.id] = [...current, u];
                        }

                        if (currentUserId && activePresenceChannel) {
                            activePresenceChannel.whisper('voice-state-update', {
                                user_id: currentUserId,
                                is_muted: isMuted.value,
                                is_deafened: isDeafened.value,
                                is_afk: isAfk.value,
                            });
                        }
                    })
                    .leaving((user: any) => {
                        const u = user.user || user;
                        const current = connectedUsers.value[channel.id] || [];
                        connectedUsers.value[channel.id] = current.filter((x) => String(x.id) !== String(u.id));
                        cleanupPeer(String(u.id));
                    })
                    .listenForWhisper('webrtc-signal', async (data: any) => {
                        if (!currentUserId || String(data.to) !== String(currentUserId)) return;
                        if (typeof RTCPeerConnection === 'undefined') return;

                        const peerId = String(data.from);

                        if (data.type === 'offer' && data.offer) {
                            try {
                                const pc = createPeerConnection(peerId);
                                await pc.setRemoteDescription(new RTCSessionDescription(data.offer));
                                const answer = await pc.createAnswer();
                                await pc.setLocalDescription(answer);
                                activePresenceChannel.whisper('webrtc-signal', {
                                    from: currentUserId,
                                    to: peerId,
                                    type: 'answer',
                                    answer,
                                });
                            } catch (e) {
                                console.error('WebRTC answer error:', e);
                            }
                        } else if (data.type === 'answer' && data.answer) {
                            const pc = peerConnections[peerId];
                            if (pc) {
                                try {
                                    await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
                                } catch (e) {
                                    console.error('WebRTC setRemoteDescription error:', e);
                                }
                            }
                        } else if (data.type === 'candidate' && data.candidate) {
                            const pc = peerConnections[peerId];
                            if (pc) {
                                try {
                                    await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                                } catch (e) {
                                    console.error('WebRTC addIceCandidate error:', e);
                                }
                            }
                        }
                    })
                    .listenForWhisper('voice-state-update', (data: any) => {
                        if (!data || !data.user_id) return;
                        const chUsers = connectedUsers.value[channel.id];
                        if (chUsers) {
                            const target = chUsers.find((u) => String(u.id) === String(data.user_id));
                            if (target) {
                                (target as any).is_muted = Boolean(data.is_muted);
                                (target as any).is_deafened = Boolean(data.is_deafened);
                                (target as any).is_afk = Boolean(data.is_afk);
                            }
                        }
                    });
            }

            transitionTo(VoiceParticipantState.Connected);
            return true;
        } catch (error) {
            console.error('Error joining voice channel:', error);
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

        for (const peerId of Object.keys(peerConnections)) {
            cleanupPeer(peerId);
        }

        if (chId && echoInstance) {
            try {
                echoInstance.leave(`voices.${chId}`);
            } catch {
                // Ignore leave error
            }
        }
        activePresenceChannel = null;
        currentUserId = null;

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
            activeStream.value.getTracks().forEach((track) => track.stop());
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
                activeStream.value.getAudioTracks().forEach((t) => {
                    t.enabled = true;
                });
            }
            broadcastVoiceState();
            return true;
        } else {
            isMutedExplicit.value = true;
            if (currentState.value === VoiceParticipantState.Connected) {
                currentState.value = VoiceParticipantState.Muted;
            }
            if (activeStream.value) {
                activeStream.value.getAudioTracks().forEach((t) => {
                    t.enabled = false;
                });
            }
            broadcastVoiceState();
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
            Object.values(remoteAudioElements).forEach((el) => {
                el.muted = false;
            });
            broadcastVoiceState();
            return true;
        } else {
            isDeafenedExplicit.value = true;
            isMutedExplicit.value = true;
            currentState.value = VoiceParticipantState.Deafened;
            if (activeStream.value) {
                activeStream.value.getAudioTracks().forEach((t) => {
                    t.enabled = false;
                });
            }
            Object.values(remoteAudioElements).forEach((el) => {
                el.muted = true;
            });
            broadcastVoiceState();
            return true;
        }
    }

    function resetState(): void {
        for (const peerId of Object.keys(peerConnections)) {
            cleanupPeer(peerId);
        }
        activePresenceChannel = null;
        currentUserId = null;

        currentState.value = VoiceParticipantState.Disconnected;
        activeChannel.value = null;
        activeServerId.value = null;
        connectedUsers.value = {};
        isAfk.value = false;
        isMutedExplicit.value = false;
        isDeafenedExplicit.value = false;
        previousStatusBeforeAfk.value = null;
        if (activeStream.value) {
            activeStream.value.getTracks().forEach((t) => t.stop());
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
