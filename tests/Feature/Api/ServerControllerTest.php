<?php

use App\Models\Server;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

//TODO: Add testing for icons


test('user can create server', function () {
    // Create a user
    $user = User::factory()->create();

    // Act as the created user
    $this->actingAs($user);

    // Define the server data
    $serverData = [
        'name' => 'Test Server',
        'description' => 'This is a test server.',
    ];

    // Send a POST request to create a server
    $response = $this->postJson(route('server.create'), $serverData);

//    $response->assertInertia(fn (Assert $page) => $page
//        ->component('Home')
//    );
    // Assert the server was created in the database
    $this->assertDatabaseHas('servers', [
        'id' => $response->json('id'),
        'name' => 'Test Server',
        'description' => 'This is a test server.',
    ]);
//
//    // Assert the user is attached to the server through the pivot table
    $server = Server::find($response->json('id'));
    $this->assertTrue($server->users()->where('users.id', $user->id)->exists());
});


test('fails if data missing', function () {
    // Create a user
    $user = User::factory()->create();

    // Act as the created user
    $this->actingAs($user);

    // Send a POST request with missing name
    $response = $this->postJson(route('server.create'), [
        'description' => 'This is a test server.',
        'icon' => 'test-icon.png',
    ]);

    // Assert the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Assert the error message for the name field
    $response->assertJsonValidationErrors(['name']);


});

test('user can be added to a server', function () {
    // Create a user and a server
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $this->actingAs($user1);

    // Create a server
    $serverData = [
        'name' => 'Test Server',
        'description' => 'This is a test server.',
    ];
    $response = $this->postJson(route('server.create'), $serverData);
    $serverId = $response->json('id');

    // Act as the user who will be added
    $this->actingAs($user1);

    // Send a POST request to add user2 to the server
    $response = $this->postJson(route('server.addUser', $serverId), [
        'user_id' => $user2->id,
    ]);

    // Assert the response status is 200 (OK)
    $response->assertStatus(200);
    $response->assertJson(['message' => 'User added to server successfully.']);

    // Assert that user2 is now attached to the server
    $server = Server::find($serverId);
    $this->assertTrue($server->users()->where('users.id', $user2->id)->exists());
});

test('user can be removed from a server', function () {
    // Create a user and a server
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $this->actingAs($user1);

    // Create a server
    $serverData = [
        'name' => 'Test Server',
        'description' => 'This is a test server.',
    ];
    $response = $this->postJson(route('server.create'), $serverData);
    $serverId = $response->json('id');

    // Add user2 to the server
    $this->postJson(route('server.addUser', $serverId), [
        'user_id' => $user2->id,
    ]);


    $response = $this->deleteJson(route('server.removeUser', $serverId), [
        'user_id' => $user2->id,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'User removed from server successfully.']);

    // Assert that user2 is no longer attached to the server
    $server = Server::find($serverId);
    $this->assertFalse($server->users()->where('users.id', $user2->id)->exists());
});

test('user can edit server', function () {
    // Create a user
    $user = User::factory()->create();

    // Act as the created user
    $this->actingAs($user);

    // Create a server
    $serverData = [
        'name' => 'Test Server',
        'description' => 'This is a test server.',
    ];
    $response = $this->postJson(route('server.create'), $serverData);
    $serverId = $response->json('id');

    // Define the updated server data
    $updatedServerData = [
        'name' => 'Updated Server Name',
        'description' => 'This is an updated description.',
    ];

    // Send a PATCH request to update the server
    $response = $this->patchJson(route('server.edit', $serverId), $updatedServerData);

    // Assert the response status is 200 (OK)
    $response->assertStatus(200);
    $response->assertJson(['message' => 'Server updated successfully.']);

    // Assert the server was updated in the database
    $this->assertDatabaseHas('servers', [
        'id' => $serverId,
        'name' => 'Updated Server Name',
        'description' => 'This is an updated description.',
    ]);
});
