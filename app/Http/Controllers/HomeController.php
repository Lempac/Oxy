<?php

namespace App\Http\Controllers;

use App\Enums\ChannelType;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function home(Request $request): Response
    {
        return Inertia::render('Home')->with([
            'servers' => $request->user()->servers,
        ]);
    }

    public function server(Request $request, int $server): Response
    {
        $serverObj = Server::find($server);

        return Inertia::render('Home')->with([
            'servers' => $request->user()->servers,
            'selected_server' => $serverObj->only(['id', 'name', 'icon', 'description']),
            'selected_server.users' => $serverObj->users,
            'invite_code' => $server.'#'.hash('xxh32', $server),
        ]);
    }

    public function text(Request $request, int $server): Response
    {
        $serverObj = Server::find($server);

        return Inertia::render('Text/Texting')->with([
            'servers' => $request->user()->servers,
            'selected_server' => $serverObj,
            'selected_server.users' => $serverObj->users,
            'selected_server.roles' => $serverObj->roles,
            'channels' => Server::find($server)->channels()->where('type', ChannelType::Text)->get(),
            'invite_code' => $server.'#'.hash('xxh32', $server),
        ]);
    }

    public function channel(Request $request, int $server, int $channel): Response
    {
        $serverObj = Server::find($server);

        return Inertia::render('Text/Texting', [
            'selected_server' => $serverObj,
            'selected_channel' => Channel::find($channel),
            'servers' => $request->user()->servers,
            'selected_server.users' => $serverObj->users,
            'selected_server.roles' => $serverObj->roles,
            'channels' => $serverObj->channels()->where('type', ChannelType::Text)->get(),
            'messages' => Message::where('channel_id', $channel)->get()->each(function (Message $message) {
                $message['sender'] = fn (): User => $message->user;
            }),
            'invite_code' => $server.'#'.hash('xxh32', $server),
        ]);
    }

    public function message(Request $request, int $server, int $channel, int $message): Response
    {
        $serverObj = Server::find($server);

        return Inertia::render('Text/Texting', [
            'selected_server' => $serverObj,
            'selected_channel' => Channel::find($channel),
            'selected_message' => Message::find($message),
            'servers' => $request->user()->servers,
            'channels' => $serverObj->channels()->where('type', ChannelType::Text)->get(),
            'messages' => Message::where('channel_id', $channel)->get()->each(function (Message $message) {
                $message['sender'] = fn (): User => $message->user;
            }),
            'invite_code' => $server.'#'.hash('xxh32', $server),
        ]);
    }

    public function voice(Request $request, int $server)
    {
        $serverObj = Server::find($server);

        return Inertia::render('Voice/Speaking', [
            'servers' => $request->user()->servers,
            'selected_server' => $serverObj,
            'selected_server.users' => $serverObj->users,
            'channels' => $serverObj->channels()->where('type', ChannelType::Voice)->get(),
            'invite_code' => $server.'#'.hash('xxh32', $server),
        ]);
    }

    public function vchannel(Request $request, int $server, int $channel): Response
    {
        $serverObj = Server::find($server);

        return Inertia::render('Text/Texting', [
            'selected_server' => $serverObj,
            'selected_channel' => Channel::find($channel),
            'servers' => $request->user()->servers,
            'selected_server.users' => $serverObj->users,
            'channels' => $serverObj->channels()->where('type', ChannelType::Voice)->get(),
            'messages' => Message::where('channel_id', $channel)->get()->each(function (Message $message) {
                $message['sender'] = fn (): User => $message->user;
            }),
            'invite_code' => $server.'#'.hash('xxh32', $server),
        ]);
    }
}
