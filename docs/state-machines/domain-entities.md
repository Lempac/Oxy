# Domain Entities State Machines

This document covers state machines for chat message lifecycles and server membership statuses.

---

## 1. Message Lifecycle State Machine (`MessageStatus`)

Tracks message states from optimistic client dispatch through editing, delivery, and deletion.

```mermaid
stateDiagram-v2
    [*] --> Sending
    
    Sending --> Sent: Server acknowledgment
    Sending --> Failed: Network / Upload error
    
    Sent --> Delivered: Broadcasted to peers
    Sent --> Edited: Author edits content
    Sent --> Deleted: Author deletes message
    
    Delivered --> Edited: Author edits content
    Delivered --> Deleted: Author / Admin deletes message
    
    Edited --> Deleted: Author / Admin deletes message
    
    Failed --> Sending: Retry message submit
    Failed --> Deleted: User discards message
    
    Deleted --> [*]
```

### Usage Example:

```php
use App\Enums\MessageStatus;

$message = Message::find($id);
$message->transitionStatusTo(MessageStatus::Delivered);
```

---

## 2. Server Member State Machine (`ServerMemberStatus`)

Tracks user membership statuses within a server pivot table (`server_user`).

```mermaid
stateDiagram-v2
    [*] --> Invited
    
    Invited --> Active: Invite link accepted
    Invited --> Left: Invite declined / expired
    
    Active --> Muted: Admin mutes member
    Active --> Suspended: Admin suspends member
    Active --> Left: Member leaves server
    
    Muted --> Active: Admin unmutes member
    Muted --> Suspended: Admin suspends member
    Muted --> Left: Member leaves server
    
    Suspended --> Active: Admin lifts suspension
    Suspended --> Left: Member leaves server
    
    Left --> Active: Re-joined server
    Left --> Invited: Re-invited to server
```

### Usage Example:

```php
use App\Enums\ServerMemberStatus;

// Transition member pivot status
$server->users()->updateExistingPivot($userId, [
    'status' => ServerMemberStatus::Muted->value,
]);
```

---

## Technical Details

- **Message Model**: `App\Models\Message` (`$message->transitionStatusTo(...)`)
- **Server Pivot**: `server_user` (`status` column cast to `ServerMemberStatus`)
