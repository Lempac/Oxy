<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAuthConnection;

class AuthConnectionService
{
    /**
     * Authenticate or register a user using an external SSO connection.
     *
     * @param  string  $provider  Name of the provider (e.g. 'discord')
     * @param  string  $providerUserId  ID from external provider
     * @param  string  $username  Provider username / nickname
     * @param  string|null  $avatarUrl  Optional avatar image URL
     * @param  array  $tokens  Access/Refresh tokens
     * @param  User|null  $currentUser  Existing logged-in user to link connection to
     */
    public function handleConnection(
        string $provider,
        string $providerUserId,
        string $username,
        ?string $avatarUrl = null,
        array $tokens = [],
        ?User $currentUser = null
    ): User {
        $connection = UserAuthConnection::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($connection) {
            // Update connection token/details
            $connection->update([
                'provider_username' => $username,
                'avatar_url' => $avatarUrl,
                'access_token' => $tokens['access_token'] ?? $connection->access_token,
                'refresh_token' => $tokens['refresh_token'] ?? $connection->refresh_token,
                'expires_at' => isset($tokens['expires_in']) ? now()->addSeconds($tokens['expires_in']) : $connection->expires_at,
            ]);

            return $connection->user;
        }

        // If user is currently logged in, link connection to existing user
        $user = $currentUser;

        if (! $user) {
            // Check if user with matching nickname exists
            $user = User::where('nickname', $username)->first();
        }

        if (! $user) {
            // First time registering via SSO: generate unique nickname and user
            $baseNickname = $username;
            $nickname = $baseNickname;
            $count = 1;
            while (User::where('nickname', $nickname)->exists()) {
                $nickname = $baseNickname.'_'.$count;
                $count++;
            }

            $user = User::create([
                'nickname' => $nickname,
                'icon' => $avatarUrl,
                'password' => null, // Password nullable for SSO-first users
            ]);
        }

        // Create connection record
        UserAuthConnection::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
            'provider_username' => $username,
            'avatar_url' => $avatarUrl,
            'access_token' => $tokens['access_token'] ?? null,
            'refresh_token' => $tokens['refresh_token'] ?? null,
            'expires_at' => isset($tokens['expires_in']) ? now()->addSeconds($tokens['expires_in']) : null,
        ]);

        return $user;
    }
}
