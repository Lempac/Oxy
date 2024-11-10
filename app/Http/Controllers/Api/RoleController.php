<?php

namespace App\Http\Controllers\Api;

use App\Events\Roles\RoleCreated;
use App\Events\Roles\RoleDeleted;
use App\Events\Roles\RoleEdited;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
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

        $role = Role::create([
            'name' => $request->name,
            'color' => $request->color,
            'perms' => $request->perms,
            'importance' => $request->importance,
        ]);

        // Attach the role to the server
        $server->roles()->attach($role->id);
        broadcast(new RoleCreated($role));

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

        $role->delete();

        broadcast(new RoleDeleted($role));

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    public function showSettings($serverId)
    {
        $server = Server::find($serverId);
        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        return Inertia::render('Settings/Role')->with(['selected_server' => $server]);
    }
}
