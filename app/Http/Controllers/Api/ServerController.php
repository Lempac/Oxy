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

        broadcast(new ServerCreated($server->id, $server->name, $server->description, $server->icon));

        return response()->json(['message' => 'Server created successfully.']);
    }

    public function addUser(Request $request)
    {
        $request->validate([
           'code' => 'required|string|max:20',
        ]);

        $data = explode('#', $request->code);
        $serverId = $data[0];
        $code = $data[1];
        if (hash('xxh32', $serverId) !== $code) return response()->json(['message' => 'Code is invalid.'], 404);

        $server = Server::find($serverId);
        if(!$server) return response()->json(['message' => 'Server not found.'], 404);
        if($server->users->has(Auth::id())) return response()->json(['message' => 'Server has already been added.'], 409);

        $server->users()->attach(Auth::id());

        broadcast(new ServerJoined(Auth::id(), $serverId));

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

        broadcast(new ServerEdited($serverId, $server->name, $server->description, $server->icon));

        return response()->json(['message' => 'Server updated successfully.']);
    }

}
