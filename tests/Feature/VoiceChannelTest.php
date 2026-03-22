<?php

use App\Enums\ChannelType;
use App\Models\Channel;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('broadcasting.default', 'reverb');
    Event::fake();

    // Explicitly load channels in the test to ensure they are registered with the Broadcaster
    require base_path('routes/channels.php');
});

test('voice channel authorization', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $role = Role::factory()->create();

    // Attach user to server via role_server_user table
    $server->users()->attach($user->id, ['role_id' => $role->id]);

    $channel = Channel::factory()->create([
        'server_id' => $server->id,
        'type' => ChannelType::Voice,
    ]);

    $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
        'channel_name' => "presence-voices.{$channel->id}",
        'socket_id' => '123.456',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('channel_data', function ($data) use ($user) {
            $decoded = json_decode($data, true);

            return $decoded['user_id'] == $user->id &&
                $decoded['user_info']['id'] == $user->id &&
                $decoded['user_info']['name'] == $user->name &&
                $decoded['user_info']['icon'] == $user->icon;
        });
});

test('unauthenticated user cannot access voice channel', function () {
    $server = Server::factory()->create();
    $channel = Channel::factory()->create([
        'server_id' => $server->id,
        'type' => ChannelType::Voice,
    ]);

    $response = $this->postJson('/broadcasting/auth', [
        'channel_name' => "presence-voices.{$channel->id}",
        'socket_id' => '123.456',
    ]);

    $response->assertStatus(403);
});
