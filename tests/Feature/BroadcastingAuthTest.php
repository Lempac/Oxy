<?php

use App\Models\Channel;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can authenticate for broadcasting channels with string UUIDs', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $channel = Channel::factory()->for($server)->create();
    $server->users()->attach($user->id);

    $this->actingAs($user);

    $responseServer = $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => "private-servers.{$server->id}",
    ]);
    $responseServer->assertStatus(200);

    $responseChannels = $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => "private-channels.{$server->id}",
    ]);
    $responseChannels->assertStatus(200);

    $responseRoles = $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => "private-roles.{$server->id}",
    ]);
    $responseRoles->assertStatus(200);

    $responseMessages = $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => "private-messages.{$channel->id}",
    ]);
    $responseMessages->assertStatus(200);

    $responseWhiteboards = $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => "private-whiteboards.{$channel->id}",
    ]);
    $responseWhiteboards->assertStatus(200);
});
