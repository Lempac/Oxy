<?php

use App\Models\Channel;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('CAN_EDIT_CHANNEL');
});

test('channel upload allows valid audio files', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    Storage::fake('local');

    $file = UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg');

    $response = $this->postJson("/api/channel/{$server->slug}/{$channel->slug}/upload", [
        'audio' => $file,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Audio data sent successfully']);
});

test('channel upload rejects invalid files', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    Storage::fake('local');

    $file = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');

    $response = $this->postJson("/api/channel/{$server->slug}/{$channel->slug}/upload", [
        'audio' => $file,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('audio');
});

test('user with CAN_EDIT_CHANNEL permission can reorder channels', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $channel1 = Channel::factory()->create(['server_id' => $server->id, 'name' => 'General', 'position' => 0]);
    $channel2 = Channel::factory()->create(['server_id' => $server->id, 'name' => 'Random', 'position' => 1]);

    $role = Role::create([
        'name' => 'Admin',
        'server_id' => $server->id,
        'color' => '#150f83',
        'importance' => 1,
    ]);
    $role->givePermissionTo('CAN_EDIT_CHANNEL');

    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    $server->users()->attach($user->id);

    $this->actingAs($user);

    $response = $this->post("/api/channel/{$server->slug}/reorder", [
        'channel_ids' => [$channel2->id, $channel1->id],
    ]);

    $response->assertStatus(302);

    expect($channel2->fresh()->position)->toBe(0);
    expect($channel1->fresh()->position)->toBe(1);
});

test('authenticated user can join and leave voice channel broadcasting state', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id, 'type' => \App\Enums\ChannelType::Voice->value]);

    $server->users()->attach($user->id);
    $this->actingAs($user);

    $joinResponse = $this->postJson("/api/channel/{$server->slug}/{$channel->slug}/voice-join");
    $joinResponse->assertStatus(200);
    $joinResponse->assertJson(['status' => 'ok']);

    $leaveResponse = $this->postJson("/api/channel/{$server->slug}/{$channel->slug}/voice-leave");
    $leaveResponse->assertStatus(200);
    $leaveResponse->assertJson(['status' => 'ok']);
});
