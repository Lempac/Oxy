<?php

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, string $id): bool {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('messages.{channelId}', function (User $user, string $channelId): bool {
    $channel = Channel::find($channelId);

    return $channel ? $user->servers()->where('servers.id', $channel->server_id)->exists() : false;
});

Broadcast::channel('channels.{serverId}', function (User $user, string $serverId): bool {
    return $user->servers()->where('servers.id', $serverId)->exists();
});

Broadcast::channel('servers.{serverId}', function (User $user, string $serverId): bool {
    return $user->servers()->where('servers.id', $serverId)->exists();
});

Broadcast::channel('roles.{serverId}', function (User $user, string $serverId): bool {
    return $user->servers()->where('servers.id', $serverId)->exists();
});

Broadcast::channel('voices.{channelId}', function (User $user, string $channelId): ?array {
    $channel = Channel::find($channelId);

    return ($channel && $user->servers()->where('servers.id', $channel->server_id)->exists()) ? ['user' => $user] : null;
});

Broadcast::channel('whiteboards.{channelId}', function (User $user, string $channelId): bool {
    $channel = Channel::find($channelId);

    return $channel ? $user->servers()->where('servers.id', $channel->server_id)->exists() : false;
});
