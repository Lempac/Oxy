# Enum Standards & Definitions

This guide details the coding standards and list of String-backed Enums across Oxy.

---

## Code Formatting Standards

- All PHP Enum cases must use **`PascalCase`** (e.g. `case Online = 'online';`, `case Voice = 'voice';`).
- PHP files must strictly conform to Laravel Pint formatting (`./vendor/bin/pint`).
- TypeScript definitions in `resources/js/types/index.d.ts` must mirror PHP Enum case names and values.

---

## Registered Enums

| Enum Class | Backing Type | Cases | Purpose |
| :--- | :--- | :--- | :--- |
| `App\Enums\UserStatus` | `string` | `Online`, `Offline`, `Idle`, `Invisible`, `DoNotDisturb` | Tracks real-time presence of users |
| `App\Enums\VoiceCallStatus` | `string` | `Idle`, `Ringing`, `Connecting`, `Active`, `Disconnected`, `Ended` | Controls voice call channel lifecycle |
| `App\Enums\VoiceParticipantState` | `string` | `Disconnected`, `Joining`, `Connected`, `Muted`, `Deafened`, `Leaving` | Tracks WebRTC client participant audio state |
| `App\Enums\ApplicationState` | `string` | `Initializing`, `Unauthenticated`, `Authenticating`, `Ready`, `Reconnecting`, `Error` | Manages app connection and auth lifecycle |
| `App\Enums\WhiteboardSyncState` | `string` | `Uninitialized`, `Synced`, `Dirty`, `Saving`, `SaveFailed` | Controls Yjs / Canvas database persistence |
| `App\Enums\MessageStatus` | `string` | `Sending`, `Sent`, `Delivered`, `Edited`, `Deleted`, `Failed` | Tracks text/file message lifecycle |
| `App\Enums\ServerMemberStatus` | `string` | `Invited`, `Active`, `Muted`, `Suspended`, `Left` | Manages server membership pivot status |
| `App\Enums\ChannelType` | `string` | `Text`, `Voice`, `Whiteboard` | Defines channel types |
| `App\Enums\Theme` | `string` | `Oxy`, `Light`, `Dark`, `Cupcake`, `Bumblebee`, ... | Controls UI theme preference |

---

## Integration Patterns

### PHP Eloquent Model Cast
```php
use App\Enums\UserStatus;

protected function casts(): array
{
    return [
        'status' => UserStatus::class,
    ];
}
```

### TypeScript Usage
```ts
import { UserStatus } from '@/types';

if (user.status === UserStatus.Online) {
    // Render online presence badge
}
```
