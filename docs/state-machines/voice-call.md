# Voice Call State Machine

The **Voice Call State Machine** governs voice channels, WebRTC call sessions, and client audio participant states.

---

## 1. Call Lifecycle State Machine (`VoiceCallStatus`)

```mermaid
stateDiagram-v2
    [*] --> Idle
    
    Idle --> Ringing: Call initiated
    Idle --> Connecting: Direct channel join
    Idle --> Ended: Force termination
    
    Ringing --> Connecting: Call accepted
    Ringing --> Disconnected: Peer unreachable
    Ringing --> Ended: Call rejected / cancelled
    
    Connecting --> Active: WebRTC stream established
    Connecting --> Disconnected: Network drop
    Connecting --> Ended: Connection aborted
    
    Active --> Disconnected: Temporary connection loss
    Active --> Ended: All participants leave / call ended
    
    Disconnected --> Connecting: Reconnection attempt
    Disconnected --> Ended: Timeout reached
    Disconnected --> Idle: Session reset

    Ended --> Idle: Session reset
    Ended --> Connecting: Restart call
```

---

## 2. Participant Audio State Machine (`VoiceParticipantState`)

Client-side participant state managed by `useVoiceCallStateMachine` composable (`resources/js/Pages/Voice/Speaking.vue`).

```mermaid
stateDiagram-v2
    [*] --> Disconnected
    
    Disconnected --> Joining: Click Join Channel
    
    Joining --> Connected: Audio stream granted
    Joining --> Disconnected: Permission denied / Error
    
    Connected --> Muted: Toggle Mute
    Connected --> Deafened: Toggle Deafen
    Connected --> Leaving: Click Leave Channel
    Connected --> Disconnected: Connection drop
    
    Muted --> Connected: Unmute
    Muted --> Deafened: Deafen
    Muted --> Leaving: Leave Channel
    Muted --> Disconnected: Network drop / Abort
    
    Deafened --> Connected: Undeafen
    Deafened --> Muted: Mute
    Deafened --> Leaving: Leave Channel
    Deafened --> Disconnected: Network drop / Abort
    
    Leaving --> Disconnected: Cleanup finished
```

---

## Frontend Integration (`useVoiceCallStateMachine`)

```ts
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';
import { VoiceParticipantState } from '@/types';

const voiceState = useVoiceCallStateMachine();

// Trigger transition
if (voiceState.transitionTo(VoiceParticipantState.Joining)) {
    // Setup MediaRecorder / WebRTC
}
```
