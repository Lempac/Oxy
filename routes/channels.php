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

    if (! $channel || ! $user->servers()->where('servers.id', $channel->server_id)->exists()) {
        return null;
    }

    $user->load('allRoles');

    return [
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
});

Broadcast::channel('whiteboards.{channelId}', function (User $user, string $channelId): bool {
    $channel = Channel::find($channelId);

    return $channel ? $user->servers()->where('servers.id', $channel->server_id)->exists() : false;
});

Broadcast::channel('presence', function (User $user): ?array {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'nickname' => $user->nickname,
        'status' => $user->status?->value ?? 'online',
    ];
});
