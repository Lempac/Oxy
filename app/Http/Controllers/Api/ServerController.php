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
                return back()->withErrors(['icon' => ['The image must not exceed 1920x1080 pixels.']]);
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
        $serverId = $data[0];
        $code = $data[1];
        if (hash('xxh32', $serverId) !== $code) {
            abort(404, 'Code is invalid.');
        }


        if (! $server) {
            abort(404, 'Server not found.');
        }

        if ($server->users->contains(Auth::id())) {
            abort(409, 'You\'re already in the server.');
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



        if (! $server) {
            abort(404, 'Server not found.');
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_KICK->value);
        })) {
            abort(403, 'Forbidden.');
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



        if (! $server) {
            abort(404, 'Server not found.');
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value);
        })) {
            abort(403, 'Forbidden.');
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

        return back();
    }

    public function showSettings(Server $server)
    {

        if (! $server) {
            abort(404, 'Server not found.');
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value) || $role->hasPerms(PermsType::CAN_MANAGE_SERVER->value);
        })) {
            abort(403, 'Forbidden.');
        }

        return Inertia::render('Settings/Server')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
        ]);
    }

    public function destroy(Server $server)
    {


        if (! $server) {
            abort(404, 'Server not found.');
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_SERVER->value);
        })) {
            abort(403, 'Forbidden.');
        }

        $server->delete();

        return redirect('/home');
    }

    public function delete(Server $server)
    {


        if (! $server) {
            abort(404, 'Server not found.');
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_SERVER->value);
        })) {
            abort(403, 'Forbidden.');
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


        if (! $server) {
            return redirect()->back()->withErrors(['message' => 'Server not found.']);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_SERVER->value);
        })) {
            abort(403, 'Forbidden.');
        }

        if ($request->file('icon')?->isValid()) {
            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return back()->withErrors(['icon' => ['The image must not exceed 1920x1080 pixels.']]);
            }

            $path = $request->file('icon')->store('uploads', 'public');
            $server->icon = Storage::url($path);
        }

        $server->name = $request->input('name', $server->name);
        $server->description = $request->input('description', $server->description);
        $server->save();

        return redirect()->route('settings.server', ['server' => $server->slug])
            ->with('message', 'Server updated successfully.');
    }

    public function leave(Server $server)
    {


        if (! $server) {
            abort(404, 'Server not found.');
        }

        $server->users()->detach(Auth::id());

        return response()->json(['message' => 'You have left the server.'], 200);
    }
}
