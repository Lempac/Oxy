<?php

use App\Models\Role;
use App\Models\Server;
use App\Models\User;

test('user without permissions cannot add user to role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([

    ]);

    $targetRole = Role::factory()->create([

    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->save($role);
    $server->roles()->save($targetRole);

    $role->syncPermissions([]);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);

    $this->actingAs($user);

    $this->withExceptionHandling();
    $response = $this->post("/api/roles/{$targetRole->id}/add-user/{$targetUser->id}");

    $response->assertStatus(403);
});

test('user with permissions can add user to role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([

    ]);

    $targetRole = Role::factory()->create([

    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->save($role);
    $server->roles()->save($targetRole);

    $role->syncPermissions(['CAN_EDIT_MEMBER_ROLES']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);

    $this->actingAs($user);

    $response = $this->post("/api/roles/{$targetRole->id}/add-user/{$targetUser->id}");

    $response->assertStatus(302);
});

test('user without permissions cannot remove user from role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([

    ]);

    $targetRole = Role::factory()->create([

    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->save($role);
    $server->roles()->save($targetRole);

    $role->syncPermissions([]);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    setPermissionsTeamId($server->id);
    $targetUser->assignRole($targetRole);

    $this->actingAs($user);

    $this->withExceptionHandling();
    $response = $this->delete("/api/roles/{$targetRole->id}/remove-user/{$targetUser->id}");

    $response->assertStatus(403);
});

test('user with permissions can remove user from role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    $server = Server::factory()->create();

    $role = Role::factory()->create([

    ]);

    $targetRole = Role::factory()->create([

    ]);

    $server->users()->attach($user->id);
    $server->users()->attach($targetUser->id);

    $server->roles()->save($role);
    $server->roles()->save($targetRole);

    $role->syncPermissions(['CAN_EDIT_MEMBER_ROLES']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);
    setPermissionsTeamId($server->id);
    $targetUser->assignRole($targetRole);

    $this->actingAs($user);

    $response = $this->delete("/api/roles/{$targetRole->id}/remove-user/{$targetUser->id}");

    $response->assertStatus(302);
});

test('user with CAN_EDIT_ROLE permission can update role with string UUID', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $role = Role::factory()->create(['server_id' => $server->id]);
    $targetRole = Role::factory()->create(['server_id' => $server->id]);

    $server->users()->attach($user->id);
    $role->syncPermissions(['CAN_EDIT_ROLE']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);

    $response = $this->actingAs($user)->patchJson("/api/roles/{$targetRole->id}", [
        'name' => 'Updated Role Name',
        'color' => '#123456',
        'importance' => 5,
    ]);

    $response->assertStatus(200);
    expect($targetRole->fresh()->name)->toBe('Updated Role Name')
        ->and($targetRole->fresh()->color)->toBe('#123456')
        ->and($targetRole->fresh()->importance)->toBe(5);
});

test('user with CAN_DELETE_ROLE permission can delete role with string UUID', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();
    $role = Role::factory()->create(['server_id' => $server->id]);
    $targetRole = Role::factory()->create(['server_id' => $server->id]);

    $server->users()->attach($user->id);
    $role->syncPermissions(['CAN_DELETE_ROLE']);
    setPermissionsTeamId($server->id);
    $user->assignRole($role);

    $response = $this->actingAs($user)->deleteJson("/api/roles/{$targetRole->id}");

    $response->assertStatus(200);
    expect(Role::find($targetRole->id))->toBeNull();
});
