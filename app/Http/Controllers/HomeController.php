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

    public function server(Request $request, Server $server): Response
    {
        return Inertia::render('Home')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
            'inviteCode' => $server->id.'#'.hash('xxh32', $server->id),
        ]);
    }

    public function text(Request $request, Server $server): Response
    {
        return Inertia::render('Text/Texting')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
            'channels' => $server->channels()->where('type', ChannelType::Text)->get(),
            'inviteCode' => $server->id.'#'.hash('xxh32', $server->id),
        ]);
    }

    public function channel(Request $request, Server $server, Channel $channel): Response
    {
        return Inertia::render('Text/Texting', [
            'servers' => $request->user()->servers,
            'selectedServer' => $serverObj,
            'selectedServer.users' => $serverObj->users,
            'selectedServer.roles' => $serverObj->roles,
            'selectedChannel' => Channel::find($channel),
            'channels' => $serverObj->channels()->where('type', ChannelType::Text)->get(),
            'messages' => Message::where('channel_id', $channel)->with('user')->get()->each(function (Message $message) {
                $message['sender'] = fn (): User => $message->user;
            }),
            'inviteCode' => $server->id.'#'.hash('xxh32', $server->id),
        ]);
    }

    public function message(Request $request, Server $server, Channel $channel, Message $message): Response
    {
        return Inertia::render('Text/Texting', [
            'servers' => $request->user()->servers,
            'selectedServer' => $serverObj,
            'selectedChannel' => Channel::find($channel),
            'selectedMessage' => Message::find($message),
            'channels' => $serverObj->channels()->where('type', ChannelType::Text)->get(),
            'messages' => Message::where('channel_id', $channel)->with('user')->get()->each(function (Message $message) {
                $message['sender'] = fn (): User => $message->user;
            }),
            'inviteCode' => $server->id.'#'.hash('xxh32', $server->id),
        ]);
    }

    public function voice(Request $request, Server $server): Response
    {
        return Inertia::render('Voice/Speaking', [
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
            'channels' => $server->channels()->where('type', ChannelType::Voice)->get(),
            'inviteCode' => $server->id.'#'.hash('xxh32', $server->id),
        ]);
    }

    public function vchannel(Request $request, Server $server, Channel $channel): Response
    {
        return Inertia::render('Voice/Speaking', [
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $server->users,
            'selectedServer.roles' => $server->roles,
            'selectedChannel' => $channel,
            'channels' => $server->channels()->where('type', ChannelType::Voice)->get(),
            'inviteCode' => $server->id.'#'.hash('xxh32', $server->id),
        ]);
    }

    public function wchannel(Request $request, int $server, int $channel): Response
    {
        $serverObj = Server::find($server);
        $channelObj = Channel::with('whiteboard')->find($channel);

        if (! $channelObj->whiteboard) {
            $channelObj->whiteboard()->create();
            $channelObj->load('whiteboard');
        }

        return Inertia::render('Whiteboard/Whiteboarding')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $serverObj,
            'selectedServer.users' => $serverObj->users,
            'selectedServer.roles' => $serverObj->roles,
            'selectedChannel' => $channelObj,
            'channels' => $serverObj->channels()->where('type', ChannelType::Whiteboard)->get(),
            'inviteCode' => $server.'#'.hash('xxh32', $server),
        ]);
    }

    public function whiteboard(Request $request, int $server): Response
    {
        $serverObj = Server::find($server);

        return Inertia::render('Whiteboard/Whiteboarding')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $serverObj,
            'selectedServer.users' => $serverObj->users,
            'selectedServer.roles' => $serverObj->roles,
            'channels' => $serverObj->channels()->where('type', ChannelType::Whiteboard)->get(),
            'inviteCode' => $server.'#'.hash('xxh32', $server),
        ]);
    }
}
