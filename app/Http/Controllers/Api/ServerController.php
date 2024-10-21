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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($request->file('icon')?->isValid()) {
            $path = $request->file('icon')->store('uploads', 'public');
        }

        $server = Server::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => empty($path) ? null : Storage::url($path),
        ]);

        $server->users()->attach(Auth::id());

        broadcast(new ServerCreated($server));

        return response()->json(['message' => 'Server created.']);
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

        broadcast(new ServerJoined());

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

        broadcast(new ServerLeft());

        return response()->json(['message' => 'User removed from server successfully.']);
    }
    public function edit(Request $request, int $serverId)
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

        broadcast(new ServerEdited($server));

        return response()->json(['message' => 'Server updated successfully.']);
    }
}
