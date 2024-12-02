<?php

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id): bool {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('messages.{channelId}', function (User $user, int $channelId): bool {
    return Channel::find($channelId)->server->users->contains($user);
});

Broadcast::channel('channels.{serverId}', function (User $user, int $serverId): bool {
    return $user->servers->contains($serverId);
});

Broadcast::channel('servers.{serverId}', function (User $user, int $serverId): bool {
    return $user->servers->contains($serverId);
});

Broadcast::channel('roles.{serverId}', function (User $user, int $serverId): bool {
    return $user->servers->contains($serverId);
});

Broadcast::channel('voices.{channelId}', function (User $user, int $channelId): ?array {
    return Channel::find($channelId)->server->users->contains($user) ? ['user' => $user] : null;
});
