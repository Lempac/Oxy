import { ref, computed } from 'vue';
import { ApplicationState, ApplicationStateType } from '@/types';

const ALLOWED_TRANSITIONS: Record<ApplicationStateType, ApplicationStateType[]> = {
    [ApplicationState.Initializing]: [
        ApplicationState.Unauthenticated,
        ApplicationState.Authenticating,
        ApplicationState.Ready,
        ApplicationState.Error,
    ],
    [ApplicationState.Unauthenticated]: [ApplicationState.Authenticating, ApplicationState.Error],
    [ApplicationState.Authenticating]: [
        ApplicationState.Ready,
        ApplicationState.Unauthenticated,
        ApplicationState.Error,
    ],
    [ApplicationState.Ready]: [ApplicationState.Reconnecting, ApplicationState.Unauthenticated, ApplicationState.Error],
    [ApplicationState.Reconnecting]: [ApplicationState.Ready, ApplicationState.Unauthenticated, ApplicationState.Error],
    [ApplicationState.Error]: [
        ApplicationState.Initializing,
        ApplicationState.Authenticating,
        ApplicationState.Unauthenticated,
    ],
};

export function useApplicationStateMachine(initialState: ApplicationStateType = ApplicationState.Initializing) {
    const state = ref<ApplicationStateType>(initialState);

    const isInitializing = computed(() => state.value === ApplicationState.Initializing);
    const isUnauthenticated = computed(() => state.value === ApplicationState.Unauthenticated);
    const isAuthenticating = computed(() => state.value === ApplicationState.Authenticating);
    const isReady = computed(() => state.value === ApplicationState.Ready);
    const isReconnecting = computed(() => state.value === ApplicationState.Reconnecting);
    const isError = computed(() => state.value === ApplicationState.Error);

    function canTransitionTo(targetState: ApplicationStateType): boolean {
        return ALLOWED_TRANSITIONS[state.value]?.includes(targetState) ?? false;
    }

    function transitionTo(targetState: ApplicationStateType): boolean {
        if (state.value === targetState) {
            return false;
        }

        if (!canTransitionTo(targetState)) {
            console.error(`Invalid application state transition from ${state.value} to ${targetState}`);
            return false;
        }

        state.value = targetState;
        return true;
    }

    return {
        state,
        isInitializing,
        isUnauthenticated,
        isAuthenticating,
        isReady,
        isReconnecting,
        isError,
        canTransitionTo,
        transitionTo,
    };
}
