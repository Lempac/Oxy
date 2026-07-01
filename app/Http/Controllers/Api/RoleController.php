<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Server $server)
    {
        $server->load('roles');

        return response()->json($server->roles);
    }

    public function create(Request $request, Server $server)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7',
            'perms' => 'present|array',
            'importance' => 'required|integer|min:0',
        ]);

        $serverId = $server->id;

        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_CREATE_ROLE')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $role = Role::create([
            'name' => $request->name,
            'color' => $request->color,
            'importance' => $request->importance,
            'server_id' => $serverId,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->perms);

        //        broadcast(new RoleCreated($role));

        return response()->json(['message' => 'Role added to server successfully.', 'role' => $role], 201);
    }

    public function edit(Request $request, int $roleId)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'color' => 'nullable|string|size:7',
            'perms' => 'nullable|array',
            'importance' => 'nullable|integer|min:0',
        ]);

        $role = Role::with('server')->find($roleId);

        if (! $role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        setPermissionsTeamId($role->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_ROLE')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $role->update($request->only(['name', 'color', 'importance']));
        if ($request->has('perms')) {
            $role->syncPermissions($request->perms);
        }

        //        broadcast(new RoleEdited($role));

        return response()->json(['message' => 'Role updated successfully.', 'role' => $role]);
    }

    public function delete(int $roleId)
    {
        $role = Role::with('server')->find($roleId);

        if (! $role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        setPermissionsTeamId($role->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_DELETE_ROLE')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $role->delete();

        //        broadcast(new RoleDeleted($role));

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    public function showSettings(Server $server)
    {
        return Inertia::render('Settings/Role')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
            'allPermissions' => Permission::all(['name', 'title', 'description', 'category']),
        ]);
    }

    public function showMembers(Server $server)
    {
        $serverId = $server->id;
        setPermissionsTeamId($serverId);

        return Inertia::render('Settings/Members')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users->each(function (User $user) use ($server) {
                $user['rolesWithServer'] = $user->roles()->where('roles.server_id', $server->id)->get();
            }),
            'selectedServer.roles' => $server->roles,
        ]);
    }

    public function addUser(int $roleId, int $userId)
    {
        $user = User::find($userId);
        $role = Role::with('server')->find($roleId);

        if (! $role) {
            abort(404, 'Role not found.');
        }

        if (! $user) {
            abort(404, 'User not found.');
        }

        setPermissionsTeamId($role->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_MEMBER_ROLES') && ! Auth::user()->hasPermissionTo('CAN_MANAGE_ROLE')) {
            abort(403, 'Forbidden.');
        }

        $user->assignRole($role);

        return back()->with('message', 'Role added successfully.');
    }

    public function removeUser(int $roleId, int $userId)
    {
        $user = User::find($userId);
        $role = Role::with('server')->find($roleId);

        if (! $role) {
            abort(404, 'Role not found.');
        }

        if (! $user) {
            abort(404, 'User not found.');
        }

        setPermissionsTeamId($role->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_MEMBER_ROLES') && ! Auth::user()->hasPermissionTo('CAN_MANAGE_ROLE')) {
            abort(403, 'Forbidden.');
        }

        $user->removeRole($role);

        return back()->with('message', 'Role deleted successfully.');
    }
}
