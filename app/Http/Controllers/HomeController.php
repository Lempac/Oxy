<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    private function getUsersWithRoles(Server $server)
    {
        setPermissionsTeamId($server->id);

        return $server->users->each(function (User $user) use ($server) {
            $user['rolesWithServer'] = $user->roles()->where('roles.server_id', $server->id)->get();
        });
    }

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
            'selectedServer.users' => $this->getUsersWithRoles($server),
            'selectedServer.roles' => $server->roles,
            'channels' => $server->channels,
            'inviteCode' => $server->getInviteCode(),
        ]);
    }

    public function text(Request $request, Server $server): Response
    {
        return Inertia::render('Text/Texting')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $this->getUsersWithRoles($server),
            'selectedServer.roles' => $server->roles,
            'channels' => $server->channels,
            'inviteCode' => $server->getInviteCode(),
        ]);
    }

    public function channel(Request $request, Server $server, Channel $channel): Response
    {
        return Inertia::render('Text/Texting', [
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $this->getUsersWithRoles($server),
            'selectedServer.roles' => $server->roles,
            'selectedChannel' => $channel,
            'channels' => $server->channels,
            'messages' => Message::where('channel_id', $channel->id)->with('user')->get()->each(function (Message $message) use ($server) {
                if ($message->user) {
                    setPermissionsTeamId($server->id);
                    $message->user['rolesWithServer'] = $message->user->roles()->where('roles.server_id', $server->id)->get();
                }
                $message['sender'] = fn (): User => $message->user;
            }),
            'inviteCode' => $server->getInviteCode(),
        ]);
    }

    public function message(Request $request, Server $server, Channel $channel, Message $message): Response
    {
        return Inertia::render('Text/Texting', [
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $this->getUsersWithRoles($server),
            'selectedChannel' => $channel,
            'selectedMessage' => $message,
            'channels' => $server->channels,
            'messages' => Message::where('channel_id', $channel->id)->with('user')->get()->each(function (Message $message) use ($server) {
                if ($message->user) {
                    setPermissionsTeamId($server->id);
                    $message->user['rolesWithServer'] = $message->user->roles()->where('roles.server_id', $server->id)->get();
                }
                $message['sender'] = fn (): User => $message->user;
            }),
            'inviteCode' => $server->getInviteCode(),
        ]);
    }

    public function voice(Request $request, Server $server): RedirectResponse
    {
        return redirect()->route('home.server', ['server' => $server->slug]);
    }

    public function vchannel(Request $request, Server $server, Channel $channel): RedirectResponse
    {
        return redirect()->route('home.server', ['server' => $server->slug]);
    }

    public function wchannel(Request $request, Server $server, Channel $channel): Response
    {
        $channel->load('whiteboard');

        if (! $channel->whiteboard) {
            $channel->whiteboard()->create();
            $channel->load('whiteboard');
        }

        return Inertia::render('Whiteboard/Whiteboarding')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $this->getUsersWithRoles($server),
            'selectedServer.roles' => $server->roles,
            'selectedChannel' => $channel,
            'channels' => $server->channels,
            'inviteCode' => $server->getInviteCode(),
        ]);
    }

    public function whiteboard(Request $request, Server $server): Response
    {
        return Inertia::render('Whiteboard/Whiteboarding')->with([
            'servers' => $request->user()->servers,
            'selectedServer' => $server,
            'selectedServer.users' => $this->getUsersWithRoles($server),
            'selectedServer.roles' => $server->roles,
            'channels' => $server->channels,
            'inviteCode' => $server->getInviteCode(),
        ]);
    }
}
