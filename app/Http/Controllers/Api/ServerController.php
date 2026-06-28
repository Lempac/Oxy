<?php

namespace App\Http\Controllers\Api;

use App\Events\Servers\ServerJoined;
use App\Events\Servers\ServerLeft;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
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

        setPermissionsTeamId($server->id);

        $role = Role::create([
            'name' => 'Owner',
            'color' => '#ffffff',
            'importance' => 0,
            'server_id' => $server->id,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::pluck('name')->toArray();
        $role->syncPermissions($permissions);

        $server->users()->attach(Auth::id());
        Auth::user()->assignRole($role);

        //        broadcast(new ServerCreated($server->id, $server->name, $server->description, $server->icon));

        return back()->with('message', 'Server created successfully.');
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

        // Note: addUser is called via fetchJson in bootstrap.ts, so we MUST return JSON!
        return response()->json(['message' => 'User added to server successfully.']);
    }

    public function removeUser(Request $request, Server $server)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $serverId = $server->id;

        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_KICK')) {
            abort(403, 'Forbidden.');
        }

        $server->users()->detach($request->user_id);

        broadcast(new ServerLeft($request->user_id, $serverId));

        return back()->with('message', 'User removed from server successfully.');
    }

    public function edit(Request $request, Server $server)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $serverId = $server->id;

        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_SERVER')) {
            abort(403, 'Forbidden.');
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

        //        broadcast(new ServerEdited($serverId, $server->name, $server->description, $server->icon));

        return back()->with('message', 'Server updated successfully.');
    }

    public function showSettings(Server $server)
    {
        $serverId = $server->id;
        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_SERVER') && ! Auth::user()->hasPermissionTo('CAN_MANAGE_SERVER')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return Inertia::render('Settings/Server')->with([
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
            'inviteCode' => $server->id.'#'.hash('xxh32', (string) $server->id),
        ]);
    }

    public function destroy(Server $server)
    {
        $serverId = $server->id;
        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_DELETE_SERVER')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $server->delete();

        return redirect('/home');
    }

    public function delete(Server $server)
    {
        $serverId = $server->id;
        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_DELETE_SERVER')) {
            abort(403, 'Forbidden.');
        }

        Role::where('server_id', $server->id)->delete();
        $server->delete();

        return redirect('/home')->with('message', 'Server deleted successfully.');
    }

    public function update(Request $request, Server $server)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $serverId = $server->id;
        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_SERVER')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($request->file('icon')?->isValid()) {
            [$width, $height] = getimagesize($request->file('icon')->getRealPath());

            if ($width > 1920 || $height > 1080) {
                return response()->json(['errors' => ['icon' => ['The image must not exceed 1920x1080 pixels.']], 'message' => 'The image must not exceed 1920x1080 pixels.'], 422);
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

        return redirect('/home')->with('message', 'You have left the server.');
    }
}
