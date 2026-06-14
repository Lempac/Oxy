<?php

use App\Enums\MessageType;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use App\Models\User;

test('user can edit their own message', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    $message = Message::factory()->create([
        'user_id' => $user->id,
        'channel_id' => $channel->id,
        'type' => MessageType::Text->value,
        'mdata' => 'Original message',
    ]);

    $response = $this->patch("/api/message/{$message->id}", [
        'mdata' => 'Updated message',
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'mdata' => 'Updated message',
    ]);
});

test('user cannot edit another user\'s message', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    $message = Message::factory()->create([
        'user_id' => $user1->id,
        'channel_id' => $channel->id,
        'type' => MessageType::Text->value,
        'mdata' => 'Original message',
    ]);

    // Act as user2 and try to edit user1's message
    $this->actingAs($user2);
    $response = $this->patch("/api/message/{$message->id}", [
        'mdata' => 'Hacked message',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'mdata' => 'Original message',
    ]);
});
