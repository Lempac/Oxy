import { ref, computed } from 'vue';
import { VoiceParticipantState, VoiceParticipantStateType } from '@/types';

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

export function useVoiceCallStateMachine(initialState: VoiceParticipantStateType = VoiceParticipantState.Disconnected) {
    const currentState = ref<VoiceParticipantStateType>(initialState);

    const isDisconnected = computed(() => currentState.value === VoiceParticipantState.Disconnected);
    const isJoining = computed(() => currentState.value === VoiceParticipantState.Joining);
    const isConnected = computed(() => currentState.value === VoiceParticipantState.Connected || currentState.value === VoiceParticipantState.Muted || currentState.value === VoiceParticipantState.Deafened);
    const isMuted = computed(() => currentState.value === VoiceParticipantState.Muted);
    const isDeafened = computed(() => currentState.value === VoiceParticipantState.Deafened);
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
        return true;
    }

    return {
        currentState,
        isDisconnected,
        isJoining,
        isConnected,
        isMuted,
        isDeafened,
        isLeaving,
        canTransitionTo,
        transitionTo,
    };
}
