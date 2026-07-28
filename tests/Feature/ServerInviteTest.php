<?php

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\Server;
use App\Models\ServerInvite;
use App\Models\User;

test('generateCode creates high-entropy 22 character URL-safe base64 string', function () {
    $code1 = ServerInvite::generateCode();
    $code2 = ServerInvite::generateCode();

    expect(strlen($code1))->toBe(22)
        ->and(strlen($code2))->toBe(22)
        ->and($code1)->not->toBe($code2)
        ->and($code1)->toMatch('/^[A-Za-z0-9_-]+$/');
});

test('server getInviteCode creates or returns active invite code', function () {
    $server = Server::factory()->create();

    $code = $server->getInviteCode();
    expect($code)->toBeString()->toHaveLength(22);

    // Second call should return the same active code
    expect($server->getInviteCode())->toBe($code);
});

test('authenticated user can join server using valid invite code via addUser', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $inviteCode = $server->getInviteCode();

    $response = $this->actingAs($user)->postJson('/api/server/add-user', [
        'code' => $inviteCode,
    ]);

    $response->assertStatus(200);
    expect($server->fresh()->users->contains('id', $user->id))->toBeTrue();
});

test('authenticated user can join server using web invites join endpoint', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $inviteCode = $server->getInviteCode();

    $response = $this->actingAs($user)->postJson('/invites/join', [
        'server_code' => $inviteCode,
    ]);

    $response->assertStatus(200);
    expect($server->fresh()->users->contains('id', $user->id))->toBeTrue();
});

test('check endpoint returns server details with members_count and online_count', function () {
    $server = Server::factory()->create();
    $inviteCode = $server->getInviteCode();

    $user1 = User::factory()->create(['status' => UserStatus::Online]);
    $user2 = User::factory()->create(['status' => UserStatus::Offline]);
    $server->users()->attach([$user1->id, $user2->id]);

    $response = $this->getJson("/invites/{$inviteCode}/check");

    $response->assertStatus(200)
        ->assertJson([
            'valid' => true,
            'server' => [
                'name' => $server->name,
                'members_count' => 2,
                'online_count' => 1,
            ],
        ]);
});

test('authenticated user with permission can create invite using server UUID id or slug', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $server->users()->attach($user->id);

    $role = Role::factory()->create(['server_id' => $server->id]);
    $role->syncPermissions(['CAN_INVITE']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);

    $responseById = $this->actingAs($user)->postJson("/server/{$server->id}/invites");
    $responseById->assertStatus(200)
        ->assertJsonPath('message', 'Invite created successfully.');

    $responseBySlug = $this->actingAs($user)->postJson("/server/{$server->slug}/invites");
    $responseBySlug->assertStatus(200)
        ->assertJsonPath('message', 'Invite created successfully.');
});

test('user cannot join server with invalid invite code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/server/add-user', [
        'code' => 'INVALID_INVITE_CODE_123',
    ]);

    $response->assertStatus(404);
});
