# Oxy Architectural Reference

Welcome to the technical documentation for Oxy. This reference provides core maintainers and API consumers with a complete architectural blueprint of Oxy's backend services, real-time engines, state machines, and security controls.

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

4. **Zero-Trust Anti-Scraping & High-Entropy Security**:
   - 128-bit Base64-URL invite tokens eliminate collision checks and render invite scraping mathematically impossible.
   - Granular IP-based rate limiting (`throttle:10,1`) guards invite redemption and registration endpoints.

---

## Systems & Architecture

- [Enum Standard & Definitions](architecture/enums.md)
- [Server Invite & Anti-Scraping System](architecture/invites.md)
- [Permissions & Team Roles System](architecture/permissions.md)
- [Real-Time WebSockets & Broadcasting Architecture](architecture/broadcasting.md)
- [Authentication & User System Architecture](architecture/authentication.md)

---

## State Machines Reference

- [User Presence State Machine](state-machines/user-presence.md)
- [Voice Call State Machine](state-machines/voice-call.md)
- [Application Lifecycle State Machine](state-machines/application-lifecycle.md)
- [Whiteboard Sync State Machine](state-machines/whiteboard-sync.md)
- [Domain Entities State Machines](state-machines/domain-entities.md)
