# Real-Time WebSockets & Broadcasting Architecture

This document covers Oxy's real-time event-driven architecture using **Laravel Reverb**, **Laravel Echo**, and WebSocket channels.

---

## 1. Overview & Connection Lifecycle

Oxy relies on real-time bidirectional WebSocket communication for chat messages, user presence, voice call coordination, whiteboard updates, and server membership changes.

- **WebSocket Engine**: [Laravel Reverb](https://laravel.com/docs/reverb) (high-performance WebSocket server for Laravel).
- **Client Frontend**: Laravel Echo (`resources/js/bootstrap.ts`) configured with Pusher JS protocol client.
- **Concurrency**: Processed alongside HTTP requests using SQLite WAL mode for non-blocking concurrent writes.

```mermaid
sequenceDiagram
    autonumber
    actor Client as Vue 3 Client (Laravel Echo)
    participant WS as Reverb WS Server
    participant App as Laravel Backend
    participant DB as SQLite DB (WAL)

    Client->>WS: Connect & Subscribe (private-servers.{serverId})
    WS->>App: Authenticate Channel Access (`routes/channels.php`)
    App-->>WS: Auth Granted (JWT / Web Session)
    WS-->>Client: Subscribed Success

    Note over Client, App: Action Triggered (e.g. Join Server)
    Client->>App: POST /api/server/add-user
    App->>DB: Attach User & Increment Uses
    App->>WS: Dispatch Event `ServerJoined`
    WS-->>Client: Broadcast `ServerJoined` to all server channel subscribers
```

---

## 2. Channel Architecture & Security (`routes/channels.php`)

All real-time channels in Oxy leverage Laravel **Implicit Model Binding** (`Server $server`, `Channel $channel`, `User $user`) to automatically resolve Eloquent model instances from UUID parameters in channel names:

```php
Broadcast::channel('servers.{server}', function (User $user, Server $server): bool {
    return $user->servers->contains('id', $server->id);
});

Broadcast::channel('messages.{channel}', function (User $user, Channel $channel): bool {
    return $channel->server->users->contains('id', $user->id);
});
```

*Note: Over the WebSocket wire, Laravel Echo automatically prepends `private-` or `presence-` (e.g., `private-servers.{serverId}`).*

| Channel Name Pattern | Channel Type | Model Binding | Authorization Rule |
| :--- | :--- | :--- | :--- |
| `servers.{server}` | Private | `Server $server` | User is a member of `$server->id` |
| `channels.{server}` | Private | `Server $server` | User is a member of `$server->id` |
| `roles.{server}` | Private | `Server $server` | User is a member of `$server->id` |
| `messages.{channel}` | Private | `Channel $channel` | User is a member of `$channel->server` |
| `whiteboards.{channel}` | Private | `Channel $channel` | User is a member of `$channel->server` |
| `voices.{channel}` | Presence | `Channel $channel` | User is a member of `$channel->server` |

---

## 3. Registered Broadcast Events

| Event Class | Channel Target | Payload | Description |
| :--- | :--- | :--- | :--- |
| `App\Events\Servers\ServerJoined` | `servers.{serverId}` | `userId`, `serverId` | Member joined server |
| `App\Events\Servers\ServerLeft` | `servers.{serverId}` | `userId`, `serverId` | Member left server |
| `App\Events\Servers\ServerEdited` | `servers.{serverId}` | `serverId`, `name`, `description`, `icon` | Server profile updated |
| `App\Events\Messages\MessageCreated` | `channels.{channelId}` | `Message` model with `sender` and `attachments` | New chat message |
| `App\Events\Messages\MessageEdited` | `channels.{channelId}` | `Message` model | Message content edited |
| `App\Events\Messages\MessageDeleted` | `channels.{channelId}` | `messageId` | Message deleted |
| `App\Events\Users\UserStatusUpdated` | `presence.server.{serverId}` | `userId`, `status` | Presence status change |
| `App\Events\Whiteboards\WhiteboardStateUpdated` | `whiteboards.{channelId}` | `Whiteboard` model | Canvas CRDT state sync |

---

## 4. Client Subscriptions (`useServerEvents`)

Vue 3 composables automate Echo listener registration and cleanup on component lifecycle:

```ts
import { useServerEvents } from '@/composables/useServerEvents';

// Automatically subscribes to server channel and syncs store state
useServerEvents(selectedServerId);
```
