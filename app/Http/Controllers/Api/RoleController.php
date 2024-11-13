<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermsType;
use App\Events\Roles\RoleEdited;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index($serverId)
    {
        $server = Server::with('roles')->find($serverId);
        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        return response()->json($server->roles);
    }

    public function create(Request $request, int $serverId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7',
            'perms' => 'required|integer',
            'importance' => 'required|integer|min:0',
        ]);

        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->contains(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_CREATE_ROLE);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $role = Role::create([
            'name' => $request->name,
            'color' => $request->color,
            'perms' => $request->perms,
            'importance' => $request->importance,
        ]);

        // Attach the role to the server
        $server->roles()->attach($role->id);
        //        broadcast(new RoleCreated($role));

        return response()->json(['message' => 'Role added to server successfully.', 'role' => $role], 201);
    }

    public function edit(Request $request, int $roleId)
    {

        $request->validate([
            'name' => 'nullable|string|max:255',
            'color' => 'nullable|string|size:7',
            'perms' => 'nullable|integer',
            'importance' => 'nullable|integer|min:0',
        ]);

        $role = Role::find($roleId);

        if (! $role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        $roles = $role->server->roles->intersect(Auth::user()->roles);

        if ($roles->contains(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_ROLE);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $role->update($request->only(['name', 'color', 'perms', 'importance']));

        broadcast(new RoleEdited($role));

        return response()->json(['message' => 'Role updated successfully.', 'role' => $role]);
    }

    public function delete(int $roleId)
    {
        $role = Role::find($roleId);

        if (! $role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        $roles = $role->server->roles->intersect(Auth::user()->roles);

        if ($roles->contains(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_ROLE);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $role->delete();

        //        broadcast(new RoleDeleted($role));

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    public function showSettings(int $serverId)
    {
        $server = Server::find($serverId);
        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        return Inertia::render('Settings/Role')->with(['selected_server' => $server]);
    }

    public function showMembers(int $serverId)
    {
        $server = Server::find($serverId);
        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        return Inertia::render('Settings/Members')->with([
            'selected_server' => $server,
            'selected_server.users' => $server->users->each(function (User $user) use ($server) {
                $user['rolesWithServer'] = $user->roles->intersect($server->roles);
            }),
        ]);
    }

    public function addUser(int $roleId, int $userId)
    {
        $user = User::find($userId);
        $role = Role::find($roleId);

        if (! $role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->roles()->attach($role);

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    public function removeUser(int $roleId, int $userId)
    {
        $user = User::find($userId);
        $role = Role::find($roleId);

        if (! $role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->roles()->detach($role);

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
