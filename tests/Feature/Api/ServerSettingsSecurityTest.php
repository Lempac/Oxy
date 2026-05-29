<?php

use App\Models\Server;
use App\Models\User;
use App\Models\Role;
use App\Enums\PermsType;

test('user without permissions cannot view server settings', function () {
    $user = User::factory()->create();

    $server = Server::factory()->create();

    // Add user to server without CAN_EDIT_SERVER or CAN_MANAGE_SERVER
    $role = Role::factory()->create([
        'perms' => 0,
    ]);

    $server->users()->attach($user->id);
    $user->roles()->attach($role->id, ['server_id' => $server->id]);
    $server->roles()->attach($role->id, ['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get("/settings/server/{$server->id}");

    $response->assertStatus(403);
});

test('user with permissions can view server settings', function () {
    $user = User::factory()->create();

    $server = Server::factory()->create();

    // Add user to server with CAN_EDIT_SERVER
    $role = Role::factory()->create([
        'perms' => PermsType::CAN_EDIT_SERVER->value,
    ]);

    $server->users()->attach($user->id);
    $user->roles()->attach($role->id, ['server_id' => $server->id]);
    $server->roles()->attach($role->id, ['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get("/settings/server/{$server->id}");

    $response->assertStatus(200);
});
