<?php

namespace App\Http\Controllers;


use App\Models\Role;
use App\Models\Server;
use Illuminate\Http\Request;
use App\Events\Roles\RoleCreated;
use App\Events\Roles\RoleEdited;
use App\Events\Roles\RoleDeleted;
class RoleController extends Controller
{

public function create(Request $request, int $serverId)
{

    $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'required|string|size:7',
        'perms' => 'required|integer',
        'importance' => 'required|integer',
    ]);

    $server = Server::find($serverId);

    if (!$server) {
        return response()->json(['message' => 'Server not found.'], 404);
    }

    $role = Role::create([
        'name' => $request->get('name'),
        'color' => $request->get('color'),
        'perms' => $request->get('perms'),
        'importance' => $request->get('importance'),
    ]);

    broadcast(new RoleCreated($role));

    return response()->json(['message' => 'Role added to server successfully.', 'role' => $role], 201);
}

public function edit(Request $request, int $serverId, int $roleId)
{

    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'color' => 'sometimes|required|string|size:7',
        'perms' => 'sometimes|required|integer',
        'importance' => 'sometimes|required|integer',
    ]);

    $server = Server::find($serverId);

    if (!$server) {
        return response()->json(['message' => 'Server not found.'], 404);
    }

    $role = Role::find($roleId);

    if (!$role) {
        return response()->json(['message' => 'Role not found.'], 404);
    }

    $role->update($request->only(['name', 'color', 'perms', 'importance']));

    broadcast(new RoleEdited($role));

    return response()->json(['message' => 'Role updated successfully.', 'role' => $role]);
}

public function delete(Request $request, int $serverId, int $roleId)
    {
        $server = Server::find($serverId);

        if (!$server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        $role->delete();

        broadcast(new RoleDeleted($role));

        return response()->json(['message' => 'Role deleted successfully.']);
    }

}
