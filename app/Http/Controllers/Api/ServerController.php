<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermsType;
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
                return back()->withErrors(['icon' => 'The image must not exceed 1920x1080 pixels.']);
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

        return back();
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $data = explode('#', $request->code);
        if (count($data) != 2) {
            return back()->withErrors(['code' => 'Code is invalid.']);
        }

        $serverId = $data[0];
        $code = $data[1];
        if (hash('xxh32', $serverId) !== $code) {
            return back()->withErrors(['code' => 'Code is invalid.']);
        }

        $server = Server::find($serverId);
        if (! $server) {
            return back()->withErrors(['code' => 'Server not found.']);
        }

        if ($server->users->contains(Auth::id())) {
            return back()->withErrors(['code' => 'You\'re already in the server.']);
        }

        $server->users()->attach(Auth::id());

        broadcast(new ServerJoined(Auth::id(), $serverId));

        return back();
    }

    public function removeUser(Request $request, Server $server)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_KICK->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        $server->users()->detach($request->user_id);

        broadcast(new ServerLeft($request->user_id, $server->id));

        return back();
    }

    public function edit(Request $request, Server $server)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        if ($request->file('icon')?->isValid()) {
            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return back()->withErrors(['icon' => 'The image must not exceed 1920x1080 pixels.']);
            }

            $path = $request->file('icon')->store('uploads', 'public');
            $server->icon = Storage::url($path);
        }

        $server->name = $request->input('name', $server->name);
        $server->description = $request->input('description', $server->description);
        $server->save();

        //        broadcast(new ServerEdited($server->id, $server->name, $server->description, $server->icon));

        return back();
    }

    public function showSettings(Server $server)
    {
        return Inertia::render('Settings/Server')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
        ]);
    }

    public function destroy(Server $server)
    {
        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_SERVER->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        $server->delete();

        return redirect('/home');
    }

    public function delete(Server $server)
    {
        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_SERVER->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        Role::whereIn('id', $server->roles->pluck('id'))->delete();
        $server->delete();

        return back();
    }

    public function update(Request $request, Server $server)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        if ($request->file('icon')?->isValid()) {
            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return back()->withErrors(['icon' => 'The image must not exceed 1920x1080 pixels.']);
            }

            $path = $request->file('icon')->store('uploads', 'public');
            $server->icon = Storage::url($path);
        }

        $server->name = $request->input('name', $server->name);
        $server->description = $request->input('description', $server->description);
        $server->save();

        return redirect()->route('settings.server', ['server' => $server])
            ->with('message', 'Server updated successfully.');
    }

    public function leave(Server $server)
    {
        $server->users()->detach(Auth::id());

        return back();
    }
}
