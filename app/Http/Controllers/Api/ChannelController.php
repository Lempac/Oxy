<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChannelType;
use App\Events\Channels\ChannelCreated;
use App\Events\Channels\ChannelDeleted;
use App\Events\Channels\ChannelEdited;
use App\Models\Channel;
use App\Models\Server;
use Illuminate\Http\Request;

class ChannelController
{
    public function create(Request $request, int $serverId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:'.implode(',', array_column(ChannelType::cases(), 'value'))
        ]);

        $server = Server::find($serverId);

        if (!$server) {
            return response()->json(['message' => 'Server not found.'], 404);
        }

        Channel::create([
            'name' => $request->get('name'),
            'type' => $request->get('type'),
            'server_id' => $serverId,
        ]);

        broadcast(new ChannelCreated($serverId));

        return response()->json(['message' => 'Channel added to server successfully.']);
    }

    public function edit(Request $request, int $channelId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $channel = Channel::find($channelId);

        if (!$channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        $channel->name = $request->get('name');
        $channel->save();

        broadcast(new ChannelEdited($channelId));

        return response()->json(['message' => 'Channel updated successfully.']);
    }

    public function delete(Request $request, int $channelId)
    {
        $channel = Channel::find($channelId);
        if (!$channel) {
            return response()->json(['message' => 'Channel not found.'], 404);
        }

        $channel->delete();

        broadcast(new ChannelDeleted($channelId));

        return response()->json(['message' => 'Channel deleted successfully.']);
    }
}
