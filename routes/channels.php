<?php

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id): bool {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('messages.{channelId}', function (User $user, string $channelId): bool {
    return Channel::where('slug', $channelId)->first()?->server->users->contains($user) ?? false;
});

Broadcast::channel('channels.{serverId}', function (User $user, string $serverId): bool {
    return $user->servers->contains('slug', $serverId);
});

Broadcast::channel('servers.{serverId}', function (User $user, string $serverId): bool {
    return $user->servers->contains('slug', $serverId);
});

Broadcast::channel('roles.{serverId}', function (User $user, string $serverId): bool {
    return $user->servers->contains('slug', $serverId);
});

Broadcast::channel('voices.{channelId}', function (User $user, string $channelId): ?array {
    return Channel::where('slug', $channelId)->first()?->server->users->contains($user) ? ['user' => $user] : null;
});
