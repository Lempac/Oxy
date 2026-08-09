<?php

use App\Models\Channel;
use App\Models\Message;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Storage::fake('public');

    Permission::findOrCreate('CAN_CREATE_MESSAGE');
    Permission::findOrCreate('CAM_CREATE_ATTACHMENTS');
    Permission::findOrCreate('CAN_DELETE_MESSAGE');
});

test('user can create message with text and multiple files simultaneously', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    $role = Role::create([
        'name' => 'Member',
        'server_id' => $server->id,
        'color' => '#150f83',
        'importance' => 1,
    ]);
    $role->givePermissionTo(['CAN_CREATE_MESSAGE', 'CAM_CREATE_ATTACHMENTS']);

    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    $server->users()->attach($user->id);

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->image('photo.png', 800, 600);
    $file2 = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    $response = $this->post("/api/message/{$server->slug}/{$channel->slug}", [
        'content' => 'Here are the requested files',
        'attachments' => [$file1, $file2],
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('messages', [
        'content' => 'Here are the requested files',
        'channel_id' => $channel->id,
        'user_id' => $user->id,
    ]);

    $message = Message::where('content', 'Here are the requested files')->first();
    expect($message)->not->toBeNull();
    expect($message->attachments)->toHaveCount(2);

    expect($message->attachments[0]->filename)->toBe('photo.png');
    expect($message->attachments[1]->filename)->toBe('document.pdf');
});

test('user can edit their own message', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    $message = Message::factory()->create([
        'user_id' => $user->id,
        'channel_id' => $channel->id,
        'content' => 'Original message',
    ]);

    $response = $this->patch("/api/message/{$message->id}", [
        'content' => 'Updated message',
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'content' => 'Updated message',
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
        'content' => 'Original message',
    ]);

    $this->actingAs($user2);
    $this->withExceptionHandling();

    $response = $this->patch("/api/message/{$message->id}", [
        'content' => 'Hacked message',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'content' => 'Original message',
    ]);
});

test('user cannot create empty message without text or files', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $channel = Channel::factory()->create(['server_id' => $server->id]);

    $role = Role::create([
        'name' => 'Member',
        'server_id' => $server->id,
        'color' => '#150f83',
        'importance' => 1,
    ]);
    $role->givePermissionTo(['CAN_CREATE_MESSAGE', 'CAM_CREATE_ATTACHMENTS']);

    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    $server->users()->attach($user->id);

    $this->actingAs($user);
    $this->withExceptionHandling();

    $response = $this->post("/api/message/{$server->slug}/{$channel->slug}", [
        'content' => '',
        'attachments' => [],
    ]);

    $response->assertSessionHasErrors('content');
});
