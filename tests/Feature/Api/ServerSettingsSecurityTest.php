<?php

use App\Models\Role;
use App\Models\Server;
use App\Models\User;

test('user without permissions cannot view server settings', function () {
    $user = User::factory()->create();

    $server = Server::factory()->create();

    // Add user to server without CAN_EDIT_SERVER or CAN_MANAGE_SERVER
    $role = Role::factory()->create([

    ]);

    $server->users()->attach($user->id);
    $role->syncPermissions([]);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    $role->update(['server_id' => $server->id]);

    $this->actingAs($user);

    $response = $this->get("/settings/server/{$server->slug}");

    $response->assertStatus(403);
});

test('user with permissions can view server settings', function () {
    $user = User::factory()->create();

    $server = Server::factory()->create();

    // Add user to server with CAN_EDIT_SERVER
    $role = Role::factory()->create([

    ]);

    $server->users()->attach($user->id);
    $role->syncPermissions(['CAN_EDIT_SERVER']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    $role->update(['server_id' => $server->id]);

    $this->actingAs($user);

    $response = $this->get("/settings/server/{$server->slug}");

    $response->assertStatus(200);
});
