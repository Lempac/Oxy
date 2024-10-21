<?php

namespace App\Http\Controllers\Api;

use App\Events\Servers\ServerCreated;
use App\Events\Servers\ServerEdited;
use App\Events\Servers\ServerJoined;
use App\Events\Servers\ServerLeft;
use App\Http\Controllers\Controller;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;
use Storage;


class ServerController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
        ]);

        $server = Server::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'user_id' => Auth::id(),
        ]);


        $server->users()->attach(Auth::id());

        return response()->json([
            'id' => $server->id,
            'server' => $server,
        ], 201);
    }
public function addUser(Request $request, int $serverId)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    $server = Server::find($serverId);

    if (!$server) {
        return response()->json(['message' => 'Server not found.'], 404);
    }

    $server->users()->attach($request->user_id);

    broadcast(new ServerJoined($request->user_id, $serverId));

    return response()->json(['message' => 'User added to server successfully.']);
}


public function removeUser(Request $request, int $serverId)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    $server = Server::find($serverId);

    if (!$server) {
        return response()->json(['message' => 'Server not found.'], 404);
    }

    $server->users()->detach($request->user_id);

    broadcast(new ServerLeft($request->user_id, $serverId));

    return response()->json(['message' => 'User removed from server successfully.']);
}

public function edit(Request $request, int $serverId)
{
    $request->validate([
        'name' => 'nullable|string|max:50',
        'description' => 'nullable|string|max:500',
        'icon' => 'nullable|string|max:255',
    ]);

    $server = Server::find($serverId);

    if (!$server) {
        return response()->json(['message' => 'Server not found.'], 404);
    }

    $server->update($request->only(['name', 'description', 'icon']));

    broadcast(new ServerEdited($server->name, $server->description, $server->icon));

    return response()->json(['message' => 'Server updated successfully.']);
}

}
