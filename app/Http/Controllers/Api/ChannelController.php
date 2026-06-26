<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChannelType;
use App\Events\Voices\Status;
use App\Models\Channel;
use App\Models\Server;
use Auth;
use Illuminate\Http\Request;

class ChannelController
{
    public function create(Request $request, int $serverId)
    {
        $request->validate(['name' => 'required|string|max:50', 'type' => 'required|in:'.implode(',', array_column(ChannelType::cases(), 'value'))]);

        $server = Server::find($serverId);

        if (! $server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_CREATE_CHANNEL')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        Channel::create(['name' => $request->get('name'), 'type' => $request->get('type'), 'server_id' => $serverId]);

        //        broadcast(new ChannelCreated($serverId));

        return response()->json(['message' => 'Channel added to server successfully.']);
    }

    public function edit(Request $request, int $channelId)
    {
        $request->validate(['name' => 'required|string|max:50']);

        $channel = Channel::find($channelId);

        if (! $channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        setPermissionsTeamId($channel->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_CHANNEL')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $channel->name = $request->get('name');
        $channel->save();

        //        broadcast(new ChannelEdited($channelId));

        return response()->json(['message' => 'Channel updated successfully.']);
    }

    public function delete(int $channelId)
    {
        $channel = Channel::find($channelId);
        if (! $channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        setPermissionsTeamId($channel->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_DELETE_CHANNEL')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $channel->delete();

        //        broadcast(new ChannelDeleted($channelId));

        return response()->json(['message' => 'Channel deleted successfully.']);
    }

    public function upload(Request $request, int $channelId)
    {
        $request->validate(['audio' => 'required|file|mimes:webm,mp3,wav,ogg|mimetypes:audio/webm,audio/mpeg,audio/wav,audio/ogg']);

        $channel = Channel::find($channelId);
        if (! $channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        $audioData = file_get_contents($request->file('audio')->getRealPath());

        broadcast(new Status($channel, $audioData));

        return response()->json(['message' => 'Audio data sent successfully']);
    }
}
