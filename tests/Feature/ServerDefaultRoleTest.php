<?php

use App\Models\Role;
use App\Models\Server;
use App\Models\ServerInvite;
use App\Models\User;

test('user with CAN_EDIT_SERVER permission can update default role', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    $ownerRole = Role::factory()->create(['server_id' => $server->id, 'importance' => 0]);
    $ownerRole->syncPermissions(['CAN_EDIT_SERVER']);

    $defaultRole = Role::factory()->create(['server_id' => $server->id, 'name' => 'MemberRole', 'importance' => 2]);

    $server->users()->attach($user->id);
    setPermissionsTeamId($server->id);
    $user->assignRole($ownerRole);

    $this->actingAs($user);

    $response = $this->post("/settings/server/{$server->slug}", [
        'name' => $server->name,
        'description' => $server->description,
        'default_role_id' => $defaultRole->id,
    ]);

    $response->assertRedirect();
    expect($server->fresh()->default_role_id)->toBe($defaultRole->id);
});

test('new member receives default role upon joining server', function () {
    $server = Server::factory()->create();
    $defaultRole = Role::factory()->create(['server_id' => $server->id, 'name' => 'DefaultRole', 'importance' => 2]);
    $server->update(['default_role_id' => $defaultRole->id]);

    $invite = ServerInvite::create([
        'server_id' => $server->id,
        'code' => 'test-default-role-code',
    ]);

    $newUser = User::factory()->create();
    $this->actingAs($newUser);

    $response = $this->post('/api/server/add-user', [
        'code' => $invite->code,
    ]);

    $response->assertStatus(200);

    setPermissionsTeamId($server->id);
    expect($newUser->fresh()->hasRole($defaultRole))->toBeTrue();
});

test('deleting a role set as default role clears default_role_id on server', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $role = Role::factory()->create(['server_id' => $server->id, 'importance' => 1]);
    $server->update(['default_role_id' => $role->id]);

    $ownerRole = Role::factory()->create(['server_id' => $server->id, 'importance' => 0]);
    $ownerRole->syncPermissions(['CAN_DELETE_ROLE']);

    $server->users()->attach($user->id);
    setPermissionsTeamId($server->id);
    $user->assignRole($ownerRole);

    $this->actingAs($user);

    $response = $this->delete("/api/roles/{$role->id}");
    $response->assertStatus(200);

    expect($server->fresh()->default_role_id)->toBeNull();
});

test('default role is optional when joining server or setting to null', function () {
    $server = Server::factory()->create(['default_role_id' => null]);

    $invite = ServerInvite::create([
        'server_id' => $server->id,
        'code' => 'test-optional-default-role',
    ]);

    $newUser = User::factory()->create();
    $this->actingAs($newUser);

    $response = $this->post('/api/server/add-user', [
        'code' => $invite->code,
    ]);

    $response->assertStatus(200);
    setPermissionsTeamId($server->id);
    expect($newUser->fresh()->roles()->where('roles.server_id', $server->id)->count())->toBe(0);
});
