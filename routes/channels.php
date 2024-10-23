<?php

use App\Models\Channel;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//Broadcast::channel('messages.{channelId}', function (User $user, int $channelId) {
//    return Channel::find($channelId)->server->users->has($user->id);
//}, ['guards' => 'web']);
//
//Broadcast::channel('channels.{serverId}', function (User $user, int $serverId) {
//    return Server::find($serverId)->users->has($user->id);
//}, ['guards' => 'web']);
//
//Broadcast::channel('servers.{serverId}', function (User $user, int $serverId) {
//    return Server::find($serverId)->users->has($user->id);
//}, ['guards' => 'web']);


