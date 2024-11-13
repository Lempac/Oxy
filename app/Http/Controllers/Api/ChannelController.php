<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChannelType;
use App\Enums\PermsType;
use App\Events\Channels\ChannelCreated;
use App\Events\Channels\ChannelDeleted;
use App\Events\Channels\ChannelEdited;
use App\Models\Channel;
use App\Models\Role;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;

class ChannelController
{
    public function create(Request $request, int $serverId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:'.implode(',', array_column(ChannelType::cases(), 'value')),
        ]);

        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        $roles = $server->roles->intersect(Auth::user()->roles);

        if ($roles->contains(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_CREATE_CHANNEL);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        Channel::create([
            'name' => $request->get('name'),
            'type' => $request->get('type'),
            'server_id' => $serverId,
        ]);

        //        broadcast(new ChannelCreated($serverId));

        return response()->json(['message' => 'Channel added to server successfully.']);
    }

    public function edit(Request $request, int $channelId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $channel = Channel::find($channelId);

        if (! $channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        if ($roles->contains(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_EDIT_CHANNEL);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $channel->name = $request->get('name');
        $channel->save();

        //        broadcast(new ChannelEdited($channelId));

        return response()->json(['message' => 'Channel updated successfully.']);
    }

    public function delete(Request $request, int $channelId)
    {
        $channel = Channel::find($channelId);
        if (! $channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        if ($roles->contains(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_CHANNEL);
        })) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $channel->delete();

        //        broadcast(new ChannelDeleted($channelId));

        return response()->json(['message' => 'Channel deleted successfully.']);
    }
}
