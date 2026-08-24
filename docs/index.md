# Oxy Architectural Reference

Welcome to the technical documentation for Oxy. This reference provides maintainers and developers with an architectural blueprint of Oxy's backend services, real-time engines, state machines, and security controls.

---

## Architectural Principles

1. **Lightweight TypeScript State Machines**:
   - Built using TypeScript string literal unions (`src/types/index.ts`).
   - Vue 3 composables (`useVoiceCallStateMachine`, `useApplicationStateMachine`, `useWhiteboardSyncStateMachine`) enforce predictable client-side UI state transitions.

2. **PocketBase Backend & TypeScript Server Hooks**:
   - Single-binary PocketBase backend handling SQLite database migrations (`pb_migrations/`), Authentication, and Realtime SSE subscriptions.
   - Server-side TypeScript hooks (`src/hooks/*.ts` -> `pb_hooks/*.pb.js`) for invite validation, token signing, and custom API endpoints.

3. **LiveKit WebRTC SFU**:
   - Dedicated LiveKit server for multi-participant voice rooms, video camera grids, and screen sharing.

4. **Zero-Trust Anti-Scraping & High-Entropy Security**:
   - 128-bit Base64-URL invite tokens eliminate collision checks and render invite scraping mathematically impossible.

---

## Systems & Architecture

- [Enum Standard & Definitions](architecture/enums.md)
- [Server Invite & Anti-Scraping System](architecture/invites.md)
- [Permissions & Team Roles System](architecture/permissions.md)
- [Real-Time Broadcasting Architecture](architecture/broadcasting.md)
- [Authentication & User System Architecture](architecture/authentication.md)
- [Message Attachments & Upload Architecture](architecture/attachments.md)

---

## State Machines Reference

- [User Presence State Machine](state-machines/user-presence.md)
- [Voice Call State Machine](state-machines/voice-call.md)
- [Application Lifecycle State Machine](state-machines/application-lifecycle.md)
- [Whiteboard Sync State Machine](state-machines/whiteboard-sync.md)
- [Domain Entities State Machines](state-machines/domain-entities.md)
