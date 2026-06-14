<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermsType;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
            'perms' => 'required|integer',
            'importance' => 'required|integer|min:0',
        ]);

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_CREATE_ROLE->value);
        })) {
            abort(403, 'Forbidden.');
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

        return back();
    }

    public function edit(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'color' => 'nullable|string|size:7',
            'perms' => 'nullable|integer',
            'importance' => 'nullable|integer|min:0',
        ]);

        $hasPerms = false;
        foreach ($role->server as $server) {
            $userRoles = $server->roles->intersect(Auth::user()->roles);
            if ($userRoles->contains(function (Role $r) {
                return $r->hasPerms(PermsType::CAN_EDIT_ROLE->value);
            })) {
                $hasPerms = true;
                break;
            }
        }

        if (! $hasPerms) {
            abort(403, 'Forbidden.');
        }

        $role->update($request->only(['name', 'color', 'perms', 'importance']));

        //        broadcast(new RoleEdited($role));

        return back();
    }

    public function delete(Role $role)
    {

        $hasPerms = false;
        foreach ($role->server as $server) {
            $userRoles = $server->roles->intersect(Auth::user()->roles);
            if ($userRoles->contains(function (Role $r) {
                return $r->hasPerms(PermsType::CAN_DELETE_ROLE->value);
            })) {
                $hasPerms = true;
                break;
            }
        }

        if (! $hasPerms) {
            abort(403, 'Forbidden.');
        }

        $role->delete();

        //        broadcast(new RoleDeleted($role));

        return back();
    }

    public function showSettings(Server $server)
    {

        return Inertia::render('Settings/Role')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
        ]);
    }

    public function showMembers(Server $server)
    {

        return Inertia::render('Settings/Members')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users->each(function (User $user) use ($server) {
                $user['rolesWithServer'] = $user->roles->intersect($server->roles);
            }),
            'selectedServer.roles' => $server->roles,
        ]);
    }

    public function addUser(Role $role, User $user)
    {

        $hasPerms = false;
        foreach ($role->server as $server) {
            $userRoles = $server->roles->intersect(Auth::user()->roles);
            if ($userRoles->contains(function (Role $r) {
                return $r->hasPerms(PermsType::CAN_EDIT_MEMBER_ROLES->value) || $r->hasPerms(PermsType::CAN_MANAGE_ROLE->value);
            })) {
                $hasPerms = true;
                break;
            }
        }

        if (! $hasPerms) {
            abort(403, 'Forbidden.');
        }

        // We need to attach the role to the user, with the server_id pivot
        // If a role belongs to multiple servers, attach it for the first one for simplicity or we should pass the server_id
        // Currently the schema has server_id on the role_server_user pivot. Let's attach using the first server id for now, as that's how it would have behaved.
        $serverId = $role->server->first()->id ?? null;
        $user->roles()->attach($role, ['server_id' => $serverId]);

        return back();
    }

    public function removeUser(Role $role, User $user)
    {

        $hasPerms = false;
        foreach ($role->server as $server) {
            $userRoles = $server->roles->intersect(Auth::user()->roles);
            if ($userRoles->contains(function (Role $r) {
                return $r->hasPerms(PermsType::CAN_EDIT_MEMBER_ROLES->value) || $r->hasPerms(PermsType::CAN_MANAGE_ROLE->value);
            })) {
                $hasPerms = true;
                break;
            }
        }

        if (! $hasPerms) {
            abort(403, 'Forbidden.');
        }

        $user->roles()->detach($role);

        return back();
    }
}
