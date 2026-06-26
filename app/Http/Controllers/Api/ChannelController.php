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
    public function create(Request $request, Server $server)
    {
        $request->validate(['name' => 'required|string|max:50', 'type' => 'required|in:'.implode(',', array_column(ChannelType::cases(), 'value'))]);

        $serverId = $server->id;

        setPermissionsTeamId($serverId);
        if (! Auth::user()->hasPermissionTo('CAN_CREATE_CHANNEL')) {
            abort(403, 'Forbidden.');
        }

        Channel::create(['name' => $request->get('name'), 'type' => $request->get('type'), 'server_id' => $serverId]);

        //        broadcast(new ChannelCreated($serverId));

        return back();
    }

    public function edit(Request $request, Server $server, Channel $channel)
    {
        $request->validate(['name' => 'required|string|max:50']);

        setPermissionsTeamId($channel->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_CHANNEL')) {
            abort(403, 'Forbidden.');
        }

        $channel->name = $request->get('name');
        $channel->save();

        //        broadcast(new ChannelEdited($channel->id));

        return back();
    }

    public function delete(Server $server, Channel $channel)
    {
        setPermissionsTeamId($channel->server_id);
        if (! Auth::user()->hasPermissionTo('CAN_DELETE_CHANNEL')) {
            abort(403, 'Forbidden.');
        }

        $type = $channel->type;
        $channelId = $channel->id;
        $channelSlug = $channel->slug;
        $channel->delete();

        //        broadcast(new ChannelDeleted($channelId));

        $previousUrl = url()->previous();
        
        // If the user was viewing the channel that was just deleted, redirect them to the server's text/voice/whiteboard root.
        if (str_contains($previousUrl, '/' . $channelSlug)) {
            if ($type === ChannelType::Text->value || $type === ChannelType::Text) {
                return redirect()->route('home.text', ['server' => $server->slug]);
            } elseif ($type === ChannelType::Voice->value || $type === ChannelType::Voice) {
                return redirect()->route('home.voice', ['server' => $server->slug]);
            } elseif ($type === ChannelType::Whiteboard->value || $type === ChannelType::Whiteboard) {
                return redirect()->route('home.whiteboard', ['server' => $server->slug]);
            }
            return redirect()->route('home.server', ['server' => $server->slug]);
        }

        return back();
    }

    public function upload(Request $request, Server $server, Channel $channel)
    {
        $request->validate(['audio' => 'required|file|mimes:webm,mp3,wav,ogg|mimetypes:audio/webm,audio/mpeg,audio/wav,audio/ogg']);

        $audioData = file_get_contents($request->file('audio')->getRealPath());

        broadcast(new Status($channel, $audioData));

        return response()->json(['message' => 'Audio data sent successfully']);
    }
}
