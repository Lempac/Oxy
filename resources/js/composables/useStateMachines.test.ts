import { describe, expect, it } from 'vitest';
import { useVoiceCallStateMachine } from './useVoiceCallStateMachine';
import { useApplicationStateMachine } from './useApplicationStateMachine';
import { useWhiteboardSyncStateMachine } from './useWhiteboardSyncStateMachine';
import { VoiceParticipantState, ApplicationState, WhiteboardSyncState } from '@/types';

describe('Voice Call State Machine Composable', () => {
    it('initializes with Disconnected state by default', () => {
        const { currentState, isDisconnected } = useVoiceCallStateMachine();
        expect(currentState.value).toBe(VoiceParticipantState.Disconnected);
        expect(isDisconnected.value).toBe(true);
    });

    it('allows valid transitions (Disconnected -> Joining -> Connected -> Muted -> Disconnected)', () => {
        const sm = useVoiceCallStateMachine();

        expect(sm.transitionTo(VoiceParticipantState.Joining)).toBe(true);
        expect(sm.isJoining.value).toBe(true);

        expect(sm.transitionTo(VoiceParticipantState.Connected)).toBe(true);
        expect(sm.isConnected.value).toBe(true);

        expect(sm.transitionTo(VoiceParticipantState.Muted)).toBe(true);
        expect(sm.isMuted.value).toBe(true);

        expect(sm.transitionTo(VoiceParticipantState.Disconnected)).toBe(true);
        expect(sm.isDisconnected.value).toBe(true);
    });

    it('blocks invalid state transitions (Disconnected -> Muted directly)', () => {
        const sm = useVoiceCallStateMachine();
        expect(sm.transitionTo(VoiceParticipantState.Muted)).toBe(false);
        expect(sm.currentState.value).toBe(VoiceParticipantState.Disconnected);
    });
});

describe('Application State Machine Composable', () => {
    it('initializes with Initializing state', () => {
        const sm = useApplicationStateMachine();
        expect(sm.state.value).toBe(ApplicationState.Initializing);
        expect(sm.isInitializing.value).toBe(true);
    });

    it('handles valid authentication/ready lifecycle transitions', () => {
        const sm = useApplicationStateMachine();
        expect(sm.transitionTo(ApplicationState.Authenticating)).toBe(true);
        expect(sm.transitionTo(ApplicationState.Ready)).toBe(true);
        expect(sm.isReady.value).toBe(true);
        expect(sm.transitionTo(ApplicationState.Reconnecting)).toBe(true);
        expect(sm.isReconnecting.value).toBe(true);
    });

    it('rejects invalid direct transitions', () => {
        const sm = useApplicationStateMachine(ApplicationState.Unauthenticated);
        expect(sm.transitionTo(ApplicationState.Ready)).toBe(false);
        expect(sm.state.value).toBe(ApplicationState.Unauthenticated);
    });
});

describe('Whiteboard Sync State Machine Composable', () => {
    it('manages whiteboard save state lifecycle', () => {
        const sm = useWhiteboardSyncStateMachine(WhiteboardSyncState.Synced);
        expect(sm.isSynced.value).toBe(true);

        expect(sm.transitionTo(WhiteboardSyncState.Dirty)).toBe(true);
        expect(sm.isDirty.value).toBe(true);

        expect(sm.transitionTo(WhiteboardSyncState.Saving)).toBe(true);
        expect(sm.isSaving.value).toBe(true);

        expect(sm.transitionTo(WhiteboardSyncState.Synced)).toBe(true);
        expect(sm.isSynced.value).toBe(true);
    });
});
