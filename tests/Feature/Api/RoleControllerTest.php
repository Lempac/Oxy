<?php

use App\Enums\PermsType;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;

test('user without permissions cannot add user to role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([
        'perms' => 0,
    ]);

    $targetRole = Role::factory()->create([
        'perms' => 0,
    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->attach($role->id);
    $server->roles()->attach($targetRole->id);

    $user->roles()->attach($role->id, ['server_id' => $server->id]);

    $this->actingAs($user);

    $response = $this->post("/api/roles/{$targetRole->id}/add-user/{$targetUser->id}");

    $response->assertStatus(403);
});

test('user with permissions can add user to role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([
        'perms' => PermsType::CAN_EDIT_MEMBER_ROLES->value,
    ]);

    $targetRole = Role::factory()->create([
        'perms' => 0,
    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->attach($role->id);
    $server->roles()->attach($targetRole->id);

    $user->roles()->attach($role->id, ['server_id' => $server->id]);

    $this->actingAs($user);

    $response = $this->post("/api/roles/{$targetRole->id}/add-user/{$targetUser->id}");

    $response->assertStatus(200);
});

test('user without permissions cannot remove user from role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([
        'perms' => 0,
    ]);

    $targetRole = Role::factory()->create([
        'perms' => 0,
    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->attach($role->id);
    $server->roles()->attach($targetRole->id);

    $user->roles()->attach($role->id, ['server_id' => $server->id]);
    $targetUser->roles()->attach($targetRole->id, ['server_id' => $server->id]);

    $this->actingAs($user);

    $response = $this->delete("/api/roles/{$targetRole->id}/remove-user/{$targetUser->id}");

    $response->assertStatus(403);
});

test('user with permissions can remove user from role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([
        'perms' => PermsType::CAN_EDIT_MEMBER_ROLES->value,
    ]);

    $targetRole = Role::factory()->create([
        'perms' => 0,
    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->attach($role->id);
    $server->roles()->attach($targetRole->id);

    $user->roles()->attach($role->id, ['server_id' => $server->id]);
    $targetUser->roles()->attach($targetRole->id, ['server_id' => $server->id]);

    $this->actingAs($user);

    $response = $this->delete("/api/roles/{$targetRole->id}/remove-user/{$targetUser->id}");

    $response->assertStatus(200);
});
