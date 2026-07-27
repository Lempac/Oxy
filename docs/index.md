# Oxy Architectural Reference

Welcome to the architectural documentation for Oxy. This guide provides core maintainers and external contributors with a clear technical blueprint of Oxy's state machine architecture and enum standards.

---

## Architectural Principles

1. **Lightweight Enum-Backed State Machines**:
   - Built using PHP 8.1+ String-backed Enums (`App\Enums\*`) and TypeScript string literal unions (`resources/js/types/index.d.ts`).
   - Database migrations use `array_column(Enum::cases(), 'value')` to enforce strict database column constraints.
   - Avoids third-party state package overhead by leveraging Eloquent `$casts` and model transition methods (`transitionStatusTo(...)`).

2. **Deterministic State Guards & Event Propagation**:
   - State transition rules are declared via `allowedTransitions()` matrix methods on Enums.
   - Calling `$model->transitionStatusTo(...)` validates transitions before saving to the database and broadcasting real-time WebSocket events.

3. **Frontend & Backend Alignment**:
   - Vue 3 composables (`useVoiceCallStateMachine`, `useApplicationStateMachine`, `useWhiteboardSyncStateMachine`) mirror backend state machines for predictable client-side UI behavior.

---

## Documentation Sections

- [Enum Standard & Definitions](architecture/enums.md)
- [User Presence State Machine](state-machines/user-presence.md)
- [Voice Call State Machine](state-machines/voice-call.md)
- [Application Lifecycle State Machine](state-machines/application-lifecycle.md)
- [Whiteboard Sync State Machine](state-machines/whiteboard-sync.md)
- [Domain Entities State Machines](state-machines/domain-entities.md)
