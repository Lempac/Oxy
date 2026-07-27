# Whiteboard Sync State Machine

The **Whiteboard Sync State Machine** manages canvas shape modifications, Yjs document synchronization, and database persistence.

---

## State Transition Diagram

```mermaid
stateDiagram-v2
    [*] --> Uninitialized
    
    Uninitialized --> Synced: Initial state loaded
    Uninitialized --> Dirty: Local edits before initial load
    
    Synced --> Dirty: User draws or edits shape
    
    Dirty --> Saving: Auto-save / Manual Save triggered
    
    Saving --> Synced: Persistence successful
    Saving --> SaveFailed: Network error / Server rejection
    
    SaveFailed --> Saving: Retry auto-save
    SaveFailed --> Dirty: New local edits added
```

---

## Technical Details

- **Enum**: `App\Enums\WhiteboardSyncState`
- **Database Table**: `whiteboards.sync_status`
- **Model**: `App\Models\Whiteboard`
- **Frontend Composable**: `useWhiteboardSyncStateMachine`

---

## Example Usage

```php
// Backend Model Transition
$whiteboard->transitionSyncStatusTo(WhiteboardSyncState::Dirty);
```

```ts
// Frontend Composable Transition
const syncSM = useWhiteboardSyncStateMachine();
syncSM.transitionTo(WhiteboardSyncState.Saving);
```
