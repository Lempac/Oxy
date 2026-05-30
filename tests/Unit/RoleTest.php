<?php

use App\Models\Role;
use App\Enums\PermsType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('hasPerms correctly checks single Enum permission', function () {
    $role = new Role();
    $role->perms = PermsType::CAN_DELETE_SERVER->value;

    expect($role->hasPerms(PermsType::CAN_DELETE_SERVER))->toBeTrue();
    expect($role->hasPerms(PermsType::CAN_EDIT_SERVER))->toBeFalse();
});

test('hasPerms correctly checks array of Enum permissions', function () {
    $role = new Role();
    $role->perms = PermsType::CAN_DELETE_SERVER->value | PermsType::CAN_EDIT_SERVER->value;

    expect($role->hasPerms([PermsType::CAN_DELETE_SERVER, PermsType::CAN_EDIT_SERVER]))->toBeTrue();
    expect($role->hasPerms([PermsType::CAN_DELETE_SERVER, PermsType::CAN_CREATE_CHANNEL]))->toBeFalse();
});

test('hasPerms correctly checks single int permission', function () {
    $role = new Role();
    $role->perms = PermsType::CAN_DELETE_SERVER->value | PermsType::CAN_EDIT_SERVER->value;

    expect($role->hasPerms(PermsType::CAN_DELETE_SERVER->value))->toBeTrue();
    expect($role->hasPerms(PermsType::CAN_CREATE_CHANNEL->value))->toBeFalse();
});

test('hasAnyPerms correctly checks if role has at least one permission', function () {
    $role = new Role();
    $role->perms = PermsType::CAN_DELETE_SERVER->value;

    expect($role->hasAnyPerms([PermsType::CAN_DELETE_SERVER, PermsType::CAN_EDIT_SERVER]))->toBeTrue();
    expect($role->hasAnyPerms([PermsType::CAN_CREATE_CHANNEL, PermsType::CAN_EDIT_SERVER]))->toBeFalse();
    expect($role->hasAnyPerms(PermsType::CAN_DELETE_SERVER))->toBeTrue();
    expect($role->hasAnyPerms(PermsType::CAN_CREATE_CHANNEL))->toBeFalse();
});

test('addPerms correctly adds single Enum permission and saves', function () {
    $role = Role::factory()->create([
        'perms' => 0,
    ]);

    $role->addPerms(PermsType::CAN_DELETE_SERVER);

    expect($role->perms)->toBe(PermsType::CAN_DELETE_SERVER->value);
    $role->refresh();
    expect($role->perms)->toBe(PermsType::CAN_DELETE_SERVER->value);
});

test('addPerms correctly adds array of Enum permissions', function () {
    $role = Role::factory()->create([
        'perms' => 0,
    ]);

    $role->addPerms([PermsType::CAN_DELETE_SERVER, PermsType::CAN_EDIT_SERVER]);

    expect($role->perms)->toBe(PermsType::CAN_DELETE_SERVER->value | PermsType::CAN_EDIT_SERVER->value);
});

test('addPerms does not alter existing permissions when adding new ones', function () {
    $role = Role::factory()->create([
        'perms' => PermsType::CAN_DELETE_SERVER->value,
    ]);

    $role->addPerms(PermsType::CAN_EDIT_SERVER);

    expect($role->perms)->toBe(PermsType::CAN_DELETE_SERVER->value | PermsType::CAN_EDIT_SERVER->value);
});

test('removePerms correctly removes single Enum permission', function () {
    $role = Role::factory()->create([
        'perms' => PermsType::CAN_DELETE_SERVER->value | PermsType::CAN_EDIT_SERVER->value,
    ]);

    $role->removePerms(PermsType::CAN_DELETE_SERVER);

    expect($role->perms)->toBe(PermsType::CAN_EDIT_SERVER->value);
    $role->refresh();
    expect($role->perms)->toBe(PermsType::CAN_EDIT_SERVER->value);
});

test('removePerms correctly removes array of Enum permissions', function () {
    $role = Role::factory()->create([
        'perms' => PermsType::CAN_DELETE_SERVER->value | PermsType::CAN_EDIT_SERVER->value | PermsType::CAN_CREATE_CHANNEL->value,
    ]);

    $role->removePerms([PermsType::CAN_DELETE_SERVER, PermsType::CAN_EDIT_SERVER]);

    expect($role->perms)->toBe(PermsType::CAN_CREATE_CHANNEL->value);
});

test('removePerms does not affect non-existent permissions', function () {
    $role = Role::factory()->create([
        'perms' => PermsType::CAN_DELETE_SERVER->value,
    ]);

    $role->removePerms(PermsType::CAN_EDIT_SERVER);

    expect($role->perms)->toBe(PermsType::CAN_DELETE_SERVER->value);
});
