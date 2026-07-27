import { ref, computed } from 'vue';
import { WhiteboardSyncState, WhiteboardSyncStateType } from '@/types';

const ALLOWED_TRANSITIONS: Record<WhiteboardSyncStateType, WhiteboardSyncStateType[]> = {
    [WhiteboardSyncState.Uninitialized]: [WhiteboardSyncState.Synced, WhiteboardSyncState.Dirty],
    [WhiteboardSyncState.Synced]: [WhiteboardSyncState.Dirty],
    [WhiteboardSyncState.Dirty]: [WhiteboardSyncState.Saving],
    [WhiteboardSyncState.Saving]: [WhiteboardSyncState.Synced, WhiteboardSyncState.SaveFailed],
    [WhiteboardSyncState.SaveFailed]: [WhiteboardSyncState.Saving, WhiteboardSyncState.Dirty],
};

export function useWhiteboardSyncStateMachine(initialState: WhiteboardSyncStateType = WhiteboardSyncState.Uninitialized) {
    const syncState = ref<WhiteboardSyncStateType>(initialState);

    const isSynced = computed(() => syncState.value === WhiteboardSyncState.Synced);
    const isDirty = computed(() => syncState.value === WhiteboardSyncState.Dirty);
    const isSaving = computed(() => syncState.value === WhiteboardSyncState.Saving);
    const isSaveFailed = computed(() => syncState.value === WhiteboardSyncState.SaveFailed);

    function canTransitionTo(targetState: WhiteboardSyncStateType): boolean {
        return ALLOWED_TRANSITIONS[syncState.value]?.includes(targetState) ?? false;
    }

    function transitionTo(targetState: WhiteboardSyncStateType): boolean {
        if (syncState.value === targetState) {
            return false;
        }

        if (!canTransitionTo(targetState)) {
            console.error(`Invalid whiteboard sync transition from ${syncState.value} to ${targetState}`);
            return false;
        }

        syncState.value = targetState;
        return true;
    }

    return {
        syncState,
        isSynced,
        isDirty,
        isSaving,
        isSaveFailed,
        canTransitionTo,
        transitionTo,
    };
}
