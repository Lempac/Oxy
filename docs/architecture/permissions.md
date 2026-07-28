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

## 3. Server Owner Role Bootstrap & Default Role

### Owner Role Provisioning
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

### Optional Server Default Role
Servers support an **optional** default role configured via the `default_role_id` column on the `servers` table (`foreignUuid('default_role_id')->nullable()->constrained('roles')->nullOnDelete()`).

- **Configuration**: Server administrators can set, change, or clear (`null`) the default role in **Server Settings** (`Settings/Server.vue`).
- **Auto-Assignment**: When a new member joins a server (via invite code, API `addUser`, or registration), if `default_role_id` is set on the server, the user is automatically assigned that default role:

```php
public function assignDefaultRole(User $user): void
{
    if ($this->default_role_id) {
        $defaultRole = Role::where('id', $this->default_role_id)
            ->where('server_id', $this->id)
            ->first();
        if ($defaultRole) {
            setPermissionsTeamId($this->id);
            $user->assignRole($defaultRole);
        }
    }
}
```
- **Optionality & Safety**: Setting a default role is entirely optional. If no default role is set (`default_role_id === null`), members join without an automatic role. Deleting a role that is set as the default role safely resets `default_role_id` to `null`.

---

## 4. Member Name Color Hierarchy & Role Color Management

Oxy uses role colors to visually distinguish members in server interfaces.

### Highest-Order Role Color Rule
When rendering member names in server views (e.g., chat messages, member lists, voice call grids, member management tables), the member's display name color is driven by their **highest-order role**:
1. Role order is strictly determined by role `importance` ascending (`0` being highest order, e.g. Owner).
2. If a member possesses multiple roles in a server, the role with the lowest `importance` number is selected as their primary role.
3. The member's display name text renders in that primary role's `color` property.
4. If a member has no roles or role color is default, fallback UI styling is used.

The calculation is implemented cleanly on the client via `getMemberRoleColor(user, serverRoles)`:

```ts
export const getMemberRoleColor = (
    user?: (User & { rolesWithServer?: Role[] | null }) | null,
    serverRoles?: Role[] | null
): string | undefined => {
    if (!user || !serverRoles || serverRoles.length === 0) return undefined;

    const userRoles = user.rolesWithServer || user.roles || [];
    if (userRoles.length === 0) return undefined;

    const memberServerRoles = serverRoles.filter(sRole =>
        userRoles.some(uRole => uRole.id === sRole.id)
    );

    if (memberServerRoles.length === 0) return undefined;

    memberServerRoles.sort((a, b) => a.importance - b.importance);

    return memberServerRoles[0]?.color;
};
```

### Role Color UI Management
In **Role Settings** (`Settings/Role.vue`), role color configuration directly updates the role color:
- **Role Color Picker**: Admins can edit role colors via a color picker labeled `"Role Color (Member Name Color)"`.
- **Member Name Preview**: Includes a live preview component displaying how member display names render using the selected color.

---

## 5. Frontend Integration (`usePerms` & `getMemberRoleColor`)

Client-side UI components use the `usePerms` composable to selectively render admin controls based on the current user's team permissions, and `getMemberRoleColor` for name color rendering:

```ts
import { usePerms, getMemberRoleColor } from '@/bootstrap';
import { PermType } from '@/types';

const perms = usePerms();

// Check if user has invite permission in current server context
if (perms.has([PermType.CAN_INVITE])) {
    // Show invite generator UI
}

// Display member name in highest-order role color
// <span :style="{ color: getMemberRoleColor(user, server.roles) }">{{ user.nickname }}</span>
```
