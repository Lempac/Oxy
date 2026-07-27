# Application Lifecycle State Machine

The **Application Lifecycle State Machine** tracks application startup, session authentication, and Laravel Echo WebSocket connection states.

---

## State Transition Diagram

```mermaid
stateDiagram-v2
    [*] --> Initializing
    
    Initializing --> Unauthenticated: Guest user detected
    Initializing --> Authenticating: Saved session token found
    Initializing --> Ready: Session verified & Echo connected
    Initializing --> Error: Bootstrap failure
    
    Unauthenticated --> Authenticating: Form submission
    Unauthenticated --> Error: Network failure
    
    Authenticating --> Ready: Auth success & socket subscribed
    Authenticating --> Unauthenticated: Credentials rejected
    Authenticating --> Error: Auth server error
    
    Ready --> Reconnecting: WebSocket disconnect
    Ready --> Unauthenticated: User logged out
    Ready --> Error: Critical session error
    
    Reconnecting --> Ready: Re-subscribed successfully
    Reconnecting --> Unauthenticated: Refresh token expired
    Reconnecting --> Error: Retry limit reached
    
    Error --> Initializing: Retry application boot
    Error --> Authenticating: Retry login
    Error --> Unauthenticated: Clear session
```

---

## Vue 3 Composable (`useApplicationStateMachine`)

```ts
import { useApplicationStateMachine } from '@/composables/useApplicationStateMachine';

const appSM = useApplicationStateMachine();

if (appSM.isReady.value) {
    // Application is ready for real-time operations
}
```
