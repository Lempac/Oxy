<?php

use App\Models\Role;
use App\Models\Server;
use App\Models\User;

test('new server has enable_whiteboard set to true by default', function () {
    $server = Server::factory()->create();

    expect($server->enable_whiteboard)->toBeTrue();
    expect($server->fresh()->enable_whiteboard)->toBeTrue();
});

test('user with CAN_EDIT_SERVER permission can update enable_whiteboard setting', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create(['enable_whiteboard' => true]);

    $role = Role::factory()->create(['server_id' => $server->id]);
    $server->users()->attach($user->id);
    $role->syncPermissions(['CAN_EDIT_SERVER']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);

    $this->actingAs($user);

    $response = $this->post("/settings/server/{$server->slug}", [
        'name' => $server->name,
        'enable_whiteboard' => false,
    ]);

    $response->assertRedirect();
    expect($server->fresh()->enable_whiteboard)->toBeFalse();

    // Toggle back to true
    $response = $this->post("/settings/server/{$server->slug}", [
        'name' => $server->name,
        'enable_whiteboard' => true,
    ]);

    $response->assertRedirect();
    expect($server->fresh()->enable_whiteboard)->toBeTrue();
});
