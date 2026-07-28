# Server Invite & Anti-Scraping System

This document outlines the architecture, database design, generation rules, and security controls for Oxy's server invitation system.

---

## 1. Overview & Architectural Goals

The Oxy invite system provides secure, shareable invitation links for joining servers. Key design principles:

1. **High Entropy & Anti-Scraping**: 128 bits of entropy per invite code to mathematically prevent brute-force code enumeration and scraping attacks.
2. **Zero DB Collision Loop Overhead**: High entropy guarantees unique codes without needing database check loops during code creation.
3. **Fast Indexed Lookups**: Codes are stored in an indexed `code` column (`UNIQUE`) on `server_invites` table for $O(1)$ B-Tree database lookups.
4. **Endpoint Rate Limiting**: Dedicated rate-limiting controls to mitigate automated scanning attempts.

---

## 2. Invite Code Structure

Invite codes are generated using cryptographically secure random bytes converted to URL-safe Base64:

```php
public static function generateCode(): string
{
    // 16 bytes = 128 bits of entropy -> 22 URL-safe Base64 characters
    return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
}
```

* **Format**: 22 characters matching `^[A-Za-z0-9_-]{22}$` (e.g., `4fK9xL2pQ0wM8vR3tN1sZb`).
* **Search Space**: $2^{128} \approx 3.4 \times 10^{38}$ possible combinations. At 1,000 requests/second, automated enumeration would take over $10^{27}$ years to find a single valid code.

---

## 3. Database Schema & Models

### Table Schema (`server_invites`)

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | UUID | Primary Key | Unique identifier for invite record |
| `server_id` | UUID | Foreign Key (`servers.id`) | Server being invited to |
| `created_by_user_id` | UUID | Nullable Foreign Key (`users.id`) | User who created the invite |
| `code` | String | Unique Index | High-entropy Base64-URL invite code |
| `max_uses` | Integer | Nullable | Maximum allowed uses (null = unlimited) |
| `uses` | Integer | Default `0` | Counter of successful joins |
| `expires_at` | Timestamp | Nullable | Expiration timestamp |
| `created_at` / `updated_at` | Timestamps | System | Record creation & update times |

### Model Integration (`App\Models\Server`)

```php
public function getInviteCode(): string
{
    $invite = $this->invites()
        ->where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
        ->where(function ($query) {
            $query->whereNull('max_uses')->orWhereColumn('uses', '<', 'max_uses');
        })
        ->latest()
        ->first();

    if (! $invite) {
        $invite = $this->invites()->create([
            'code' => ServerInvite::generateCode(),
        ]);
    }

    return $invite->code;
}
```

---

## 4. Rate Limiting & Anti-Scraping Security

To complement 128-bit code entropy, all invite verification and redemption endpoints enforce IP-based rate limiting via Laravel's `throttle` middleware (10 requests per minute):

| Endpoint | HTTP Method | Controller Action | Rate Limit |
| :--- | :--- | :--- | :--- |
| `/invites/{code}/check` | `GET` | `ServerInviteController@check` | `10 req/min` |
| `/invites/join` | `POST` | `ServerInviteController@join` | `10 req/min` |
| `/api/server/add-user` | `POST` | `ServerController@addUser` | `10 req/min` |
| `/register` | `POST` | `RegisteredUserController@store` | `10 req/min` |

---

## 5. Endpoints & Workflows

### Creating an Invite
- **Route**: `POST /server/{server}/invites`
- **Controller**: `ServerInviteController@store`
- **Permissions**: Requires `CAN_INVITE` or `CAN_MANAGE_SERVER`.
- **Params**: `max_uses` (optional int), `expires_in_hours` (optional int).

### Checking an Invite (Public)
- **Route**: `GET /invites/{code}/check`
- **Controller**: `ServerInviteController@check`
- **Response**: Returns valid state and server basic metadata (name, description, icon).

### Joining a Server (Logged-in User)
- **Route**: `POST /api/server/add-user` or `POST /invites/join`
- **Controllers**: `ServerController@addUser`, `ServerInviteController@join`
- **Behavior**: Validates code & expiration/use limits, attaches user to server `server_user` pivot, assigns optional server default role (`$server->assignDefaultRole($user)`), increments `uses` counter, and broadcasts `ServerJoined` event.

### Registration with Invite Code
- **Route**: `POST /register`
- **Controller**: `RegisteredUserController@store`
- **Behavior**: Validates `server_code`, creates user account, attaches user to server, assigns optional server default role (`$server->assignDefaultRole($user)`), and increments invite usage.
