<?php

use App\Models\Server;
use App\Models\User;

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
    $response = $this->post(route('server.create'), $serverData);

    // Assert the response status is 302 (Created)
    $response->assertRedirect(route('home'));

    // Assert the server was created in the database
//    $this->assertDatabaseHas('servers', [
//        'id' => $response->id,
//        'name' => 'Test Server',
//        'description' => 'This is a test server.',
//        'icon' => 'test-icon.png',
//    ]);

    // Assert the user is attached to the server through the pivot table
//    $server = Server::find($response->json('id'));
//    $this->assertTrue($server->users()->where('users.id', $user->id)->exists());
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
