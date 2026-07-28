# Permissions & Team Roles System

This document details the multi-tenant team permissions architecture, role management, and access control model in Oxy.

---

## 1. Overview & Spatie Team Scoping

Oxy leverages [Spatie Laravel-Permission](https://spatie.be/docs/laravel-permission) configured with **Team Support** (`teams => true`).

In Oxy, each **Server** operates as a distinct Spatie Team (`team_foreign_key => server_id`). This guarantees strict isolation:
- A user can be an `Owner` or `Admin` in Server A without having those privileges in Server B.
- Permissions are evaluated relative to the active Server context by calling `setPermissionsTeamId($server->id)` prior to permission checks.

```php
setPermissionsTeamId($server->id);

if (! Auth::user()->hasPermissionTo('CAN_MANAGE_SERVER')) {
    abort(403, 'Forbidden.');
}
```

---

## 2. Server Permission Matrix

The following granular permissions are registered across Oxy:

| Permission Name | Category | Description |
| :--- | :--- | :--- |
| `CAN_INVITE` | Server | Generate server invite codes |
| `CAN_EDIT_SERVER` | Server | Modify server name, icon, description |
| `CAN_MANAGE_SERVER` | Server | Access server settings dashboard |
| `CAN_DELETE_SERVER` | Server | Delete server workspace |
| `CAN_KICK` | Members | Remove members from server |
| `CAN_CREATE_CHANNEL` | Channels | Create text/voice/whiteboard channels |
| `CAN_EDIT_CHANNEL` | Channels | Rename or edit channel settings |
| `CAN_DELETE_CHANNEL` | Channels | Delete channels |
| `CAN_CREATE_MESSAGE` | Chat | Send messages in channels |
| `CAN_DELETE_MESSAGE` | Chat | Delete any user's message in chat |
| `CAN_CREATE_ROLE` | Roles | Create custom server roles |
| `CAN_EDIT_ROLE` | Roles | Edit role permissions/color/importance |
| `CAN_DELETE_ROLE` | Roles | Delete custom roles |
| `CAN_EDIT_MEMBER_ROLES`| Roles | Assign or remove roles from members |
| `CAN_MANAGE_ROLE` | Roles | Manage role assignments |

---

## 3. Server Owner Role Bootstrap

When a new Server is created (`Server::create(...)` in `ServerController@create`), an **Owner** role is automatically provisioned for the server team and assigned to the server creator:

```php
setPermissionsTeamId($server->id);

$role = Role::create([
    'name' => 'Owner',
    'color' => '#ffffff',
    'importance' => 0,
    'server_id' => $server->id,
    'guard_name' => 'web',
]);

// Grant all permissions to Owner role
$permissions = Permission::pluck('name')->toArray();
$role->syncPermissions($permissions);

$server->users()->attach(Auth::id());
Auth::user()->assignRole($role);
```

---

## 4. Frontend Integration (`usePerms`)

Client-side UI components use the `usePerms` composable to selectively render admin controls based on the current user's team permissions:

```ts
import { usePerms } from '@/bootstrap';
import { PermType } from '@/types';

const perms = usePerms();

// Check if user has invite permission in current server context
if (perms.has([PermType.CAN_INVITE])) {
    // Show invite generator UI
}
```
