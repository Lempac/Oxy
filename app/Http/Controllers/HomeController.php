<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\ChannelType;

class HomeController extends Controller
{
    public function home(Request $request): Response
    {
        return Inertia::render('Home')->with([
            'servers' => $request->user()->servers
        ]);
    }

    public function server(Request $request, int $server): Response
    {
        return Inertia::render('Home')->with([
            'servers' => $request->user()->servers,
            'selected_server' => Server::find($server),
            'invite_code' => $server . '#' . hash('xxh32', $server),
        ]);
    }

    public function text(Request $request, int $server): Response
    {
        return Inertia::render('Text/Texting')->with([
            'servers' => $request->user()->servers,
            'selected_server' => Server::find($server),
            'channels' => Server::find($server)->channels()->where('type', ChannelType::Text)->get(),
            'invite_code' => $server . '#' . hash('xxh32', $server),
        ]);
    }

    public function channel(Request $request, int $server, int $channel): Response
    {
        return Inertia::render('Text/Texting', [
            'selected_server' => Server::find($server),
            'selected_channel' => Channel::find($channel),
            'servers' => $request->user()->servers,
            'channels' => Server::find($server)->channels()->where('type', ChannelType::Text)->get(),
            'messages' => Message::where('channel_id', $channel)->get()->each(function (Message $message) {
                $message['sender'] = fn () : User => $message->user;
            }),
            'invite_code' => $server . '#' . hash('xxh32', $server),
        ]);
    }

    public function message(Request $request, int $server, int $channel, int $message): Response
    {
        return Inertia::render('Text/Texting', [
            'selected_server' => Server::find($server),
            'selected_channel' => Channel::find($channel),
            'selected_message' => Message::find($message),
            'servers' => $request->user()->servers,
            'channels' => Server::find($server)->channels()->where('type', ChannelType::Text)->get(),
            'messages' => Message::where('channel_id', $channel)->get()->each(function (Message $message) {
                $message['sender'] = fn () : User => $message->user;
            }),
            'invite_code' => $server . '#' . hash('xxh32', $server),
        ]);
    }
}
