<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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

    public function addUser(Request $request, $serverId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $server = Server::find($serverId);

        if (!$server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $server->users()->attach($request->user_id);
        return response()->json(['message' => 'User added to server successfully.']);
    }

    public function removeUser(Request $request, $serverId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $server = Server::find($serverId);

        if (!$server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $server->users()->detach($request->user_id);
        return response()->json(['message' => 'User removed from server successfully.']);
    }
    public function edit(Request $request, $serverId)
    {
        $request->validate([
            'name' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $server = Server::find($serverId);

        if (!$server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        if ($request->has('name')) {
            $server->name = $request->name;
        }

        if ($request->has('description')) {
            $server->description = $request->description;
        }

        $server->save();

        return response()->json(['message' => 'Server updated successfully.']);
    }
}
