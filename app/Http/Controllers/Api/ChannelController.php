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

        return back()->with('message', 'Channel added to server successfully.');
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

        return back()->with('message', 'Channel updated successfully.');
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

        $previousUrl = url()->previous();

        // If the user was viewing the channel that was just deleted, redirect them to the server's text/whiteboard/server root.
        if (str_contains($previousUrl, '/'.$channelSlug)) {
            if ($type === ChannelType::Text->value || $type === ChannelType::Text) {
                return redirect()->route('home.text', ['server' => $server->slug])->with('message', 'Channel deleted successfully.');
            } elseif ($type === ChannelType::Whiteboard->value || $type === ChannelType::Whiteboard) {
                return redirect()->route('home.whiteboard', ['server' => $server->slug])->with('message', 'Channel deleted successfully.');
            }

            return redirect()->route('home.server', ['server' => $server->slug])->with('message', 'Channel deleted successfully.');
        }

        return back()->with('message', 'Channel deleted successfully.');
    }

    public function reorder(Request $request, Server $server)
    {
        $request->validate([
            'channel_ids' => 'required|array',
            'channel_ids.*' => 'required|string',
        ]);

        setPermissionsTeamId($server->id);
        if (! Auth::user()->hasPermissionTo('CAN_EDIT_CHANNEL')) {
            abort(403, 'Forbidden.');
        }

        $channelIds = $request->input('channel_ids');
        foreach ($channelIds as $position => $id) {
            Channel::where('id', $id)
                ->where('server_id', $server->id)
                ->update(['position' => $position]);
        }

        return back()->with('message', 'Channels reordered successfully.');
    }

    public function upload(Request $request, Server $server, Channel $channel)
    {
        $request->validate(['audio' => 'required|file|mimes:webm,mp3,wav,ogg|mimetypes:audio/webm,audio/mpeg,audio/wav,audio/ogg']);

        $audioData = file_get_contents($request->file('audio')->getRealPath());

        broadcast(new Status($channel, $audioData));

        return response()->json(['message' => 'Audio data sent successfully']);
    }

    public function voiceJoin(Request $request, Server $server, Channel $channel)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $user->load('allRoles');
        $userPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'nickname' => $user->nickname,
            'icon' => $user->icon,
            'status' => $user->status?->value ?? 'online',
            'light_theme' => $user->light_theme?->value ?? 'oxy',
            'dark_theme' => $user->dark_theme?->value ?? 'dark',
            'roles' => $user->allRoles,
            'rolesWithServer' => $user->allRoles,
        ];

        broadcast(new \App\Events\Voices\VoiceStateChanged($server->id, $channel->id, $userPayload, 'joined'))->toOthers();

        return response()->json(['status' => 'ok']);
    }

    public function voiceLeave(Request $request, Server $server, Channel $channel)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $userPayload = [
            'id' => $user->id,
        ];

        broadcast(new \App\Events\Voices\VoiceStateChanged($server->id, $channel->id, $userPayload, 'left'))->toOthers();

        return response()->json(['status' => 'ok']);
    }
}
