<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Create the server
        $server = Server::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => empty($path) ? null : Storage::url($path),
        ]);

        // Attach the authenticated user to the server
        $server->users()->attach(Auth::id());
        // Redirect to the home page or the newly created server page using Inertia
        return to_route('home')->with('success', 'Server created successfully.');
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

}
