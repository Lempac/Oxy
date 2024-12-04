<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermsType;
use App\Events\Servers\ServerCreated;
use App\Events\Servers\ServerEdited;
use App\Events\Servers\ServerJoined;
use App\Events\Servers\ServerLeft;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

class ServerController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->file('icon')?->isValid()) {

            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return response()->json(['errors' => ['icon' => ['The image must not exceed 1920x1080 pixels.'],],'message' => 'The image must not exceed 1920x1080 pixels.'], 422);
            }

            $path = $request->file('icon')->store('uploads', 'public');
        }

        $server = Server::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => empty($path) ? null : Storage::url($path),
        ]);

        $role = Role::create([
            'name' => 'Owner',
            'color' => '#ffffff',
            'perms' => PHP_INT_MAX,
            'importance' => 0,
        ]);

        $server->roles()->attach($role);
        $server->users()->attach(Auth::id());
        Auth::user()->roles()->attach($role);

        //        broadcast(new ServerCreated($server->id, $server->name, $server->description, $server->icon));

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
        if (hash('xxh32', $serverId) !== $code) {
            return response()->json(['message' => 'Code is invalid.'], 404);
        }

        $server = Server::find($serverId);
        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        if ($server->users->contains(Auth::id())) {
            return response()->json(['message' => 'You\'re already in the server.'], 409);
        }

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

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_KICK->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $server->users()->detach($request->user_id);

        broadcast(new ServerLeft($request->user_id, $serverId));

        return response()->json(['message' => 'User removed from server successfully.']);
    }

    public function edit(Request $request, int $serverId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($request->file('icon')?->isValid()) {
            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return response()->json(['errors' => ['icon' => ['The image must not exceed 1920x1080 pixels.'], 'message' => 'The image must not exceed 1920x1080 pixels.']], 422);
            }

            $path = $request->file('icon')->store('uploads', 'public');
            $server->icon = Storage::url($path);
        }

        $server->name = $request->input('name', $server->name);
        $server->description = $request->input('description', $server->description);
        $server->save();

        //        broadcast(new ServerEdited($serverId, $server->name, $server->description, $server->icon));

        return response()->json(['message' => 'Server updated successfully.']);
    }

    public function showSettings($serverId)
    {
        $server = Server::find($serverId);
        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        return Inertia::render('Settings/Server')->with([
            'selected_server' => $server,
            'selected_server.users' => $server->users,
            'selected_server.roles' => $server->roles,
        ]);
    }

    public function destroy(int $serverId)
    {
        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_SERVER->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $server->delete();

        return redirect('/home');
    }

    public function update(Request $request, int $serverId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $server = Server::find($serverId);
        if (! $server) {
            return redirect()->back()->withErrors(['message' => 'Server not found.']);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($request->file('icon')?->isValid()) {
            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return response()->json(['errors' => ['icon' => ['The image must not exceed 1920x1080 pixels.'],], 'message' => 'The image must not exceed 1920x1080 pixels.'], 422);
            }

            $path = $request->file('icon')->store('uploads', 'public');
            $server->icon = Storage::url($path);
        }

        $server->name = $request->input('name', $server->name);
        $server->description = $request->input('description', $server->description);
        $server->save();

        return redirect()->route('settings.server', ['id' => $serverId])
            ->with('message', 'Server updated successfully.');
    }

    public function delete(int $serverId)
    {
        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_SERVER->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $server->roles->each(function ($role) {
            $role->delete();
        });
        $server->delete();

        return response()->json(['message' => 'Server deleted successfully.']);
    }

    public function leave(int $serverId)
    {
        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $server->users()->detach(Auth::id());

        return response()->json(['message' => 'You have left the server.'], 200);
    }
}
