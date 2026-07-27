# User Presence State Machine

The **User Presence State Machine** manages the online presence and activity states of users across Oxy.

---

## State Transition Diagram

```mermaid
stateDiagram-v2
    [*] --> Offline
    
    Offline --> Online: User connects / authenticates
    Offline --> Invisible: User connects in invisible mode
    
    Online --> Idle: Inactivity timeout
    Online --> DoNotDisturb: Set DND mode
    Online --> Invisible: Toggle invisible mode
    Online --> Offline: Logout / disconnect
    
    Idle --> Online: User activity detected
    Idle --> DoNotDisturb: Set DND mode
    Idle --> Invisible: Toggle invisible mode
    Idle --> Offline: Logout / disconnect
    
    DoNotDisturb --> Online: Clear DND mode
    DoNotDisturb --> Idle: Inactivity timeout
    DoNotDisturb --> Invisible: Toggle invisible mode
    DoNotDisturb --> Offline: Logout / disconnect

    Invisible --> Online: Disable invisible mode
    Invisible --> Idle: Inactivity timeout
    Invisible --> DoNotDisturb: Set DND mode
    Invisible --> Offline: Disconnect
```

---

## Technical Details

- **Enum**: `App\Enums\UserStatus`
- **Database Table**: `users.status`
- **Model**: `App\Models\User`
- **Broadcast Event**: `App\Events\Users\UserStatusUpdated` (dispatched over `presence` channel)

### Execution Example:

```php
$user = User::find($id);

// Safely transition status
$user->transitionStatusTo(UserStatus::Online);
```
