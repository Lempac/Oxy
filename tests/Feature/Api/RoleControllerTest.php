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
