<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChannelType;
use App\Enums\PermsType;
use App\Events\Voices\Status;
use App\Models\Channel;
use App\Models\Role;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;

class ChannelController
{
    public function create(Request $request, Server $server)
    {
        $request->validate(['name' => 'required|string|max:50', 'type' => 'required|in:'.implode(',', array_column(ChannelType::cases(), 'value'))]);

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_CREATE_CHANNEL->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        Channel::create(['name' => $request->get('name'), 'type' => $request->get('type'), 'server_id' => $server->id]);

        //        broadcast(new ChannelCreated($server->id));

        return back();
    }

    public function edit(Request $request, Channel $channel)
    {
        $request->validate(['name' => 'required|string|max:50']);

        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_CHANNEL->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        $channel->name = $request->get('name');
        $channel->save();

        //        broadcast(new ChannelEdited($channel->id));

        return back();
    }

    public function delete(Channel $channel)
    {
        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_CHANNEL->value);
        })) {
            return back()->withErrors(['message' => 'Forbidden.']);
        }

        $channel->delete();

        //        broadcast(new ChannelDeleted($channel->id));

        return back();
    }

    public function upload(Request $request, Channel $channel)
    {
        $request->validate(['audio' => 'required|file']);

        $audioData = file_get_contents($request->file('audio')->getRealPath());

        broadcast(new Status($channel, $audioData));

        return response()->json(['message' => 'Audio data sent successfully']);
    }
}
