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
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        Channel::create(['name' => $request->get('name'), 'type' => $request->get('type'), 'server_id' => $server->id]);

        //        broadcast(new ChannelCreated($serverId));

        return response()->json(['message' => 'Channel added to server successfully.']);
    }

    public function edit(Request $request, Channel $channel)
    {
        $request->validate(['name' => 'required|string|max:50']);

        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_CHANNEL->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $channel->name = $request->get('name');
        $channel->save();

        //        broadcast(new ChannelEdited($channelId));

        return response()->json(['message' => 'Channel updated successfully.']);
    }

    public function delete(Channel $channel)
    {

        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_CHANNEL->value);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $channel->delete();

        //        broadcast(new ChannelDeleted($channelId));

        return response()->json(['message' => 'Channel deleted successfully.']);
    }

    public function upload(Request $request, Channel $channel)
    {
        $request->validate(['audio' => 'required|file|mimes:webm,mp3,wav,ogg|mimetypes:audio/webm,audio/mpeg,audio/wav,audio/ogg']);

        $audioData = file_get_contents($request->file('audio')->getRealPath());

        broadcast(new Status($channel, $audioData));

        return response()->json(['message' => 'Audio data sent successfully']);
    }
}
