<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermsType;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
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
            return back()->withErrors(['message' => 'Forbidden.']);
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

        $roles = $role->server->first()->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_ROLE->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        $role->update($request->only(['name', 'color', 'perms', 'importance']));

        //        broadcast(new RoleEdited($role));

        return back();
    }

    public function delete(Role $role)
    {
        $roles = $role->server->first()->roles->intersect(Auth::user()->roles);
        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_ROLE->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
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
        $user->roles()->attach($role);

        return back();
    }

    public function removeUser(Role $role, User $user)
    {
        $user->roles()->detach($role);

        return back();
    }
}
