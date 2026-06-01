<?php

use App\Models\Channel;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('channel upload allows valid audio files', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    Storage::fake('local');

    $file = UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg');

    $response = $this->postJson("/api/channel/{$channel->id}/upload", [
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

    $response = $this->postJson("/api/channel/{$channel->id}/upload", [
        'audio' => $file,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('audio');
});
