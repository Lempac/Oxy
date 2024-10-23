<?php

// use App\Models\Server;
// use App\Models\User;

// // Test for creating a server
// test('user can create server', function () {
//     // Create a user
//     $user = User::factory()->create();

//     // Act as the created user
//     $this->actingAs($user);

//     // Define the server data
//     $serverData = [
//         'name' => 'Test Server',
//         'description' => 'This is a test server.',
//     ];

//     // Send a POST request to create a server
//     $response = $this->postJson(route('server.create'), $serverData);

//     // Assert the server was created in the database
//     $this->assertDatabaseHas('servers', [
//         'id' => $response->json('id'),
//         'name' => 'Test Server',
//         'description' => 'This is a test server.',
//     ]);

//     // Assert the user is attached to the server through the pivot table
//     $server = Server::find($response->json('id'));
//     $this->assertTrue($server->users()->where('users.id', $user->id)->exists());
// });

// test('fails if data missing', function () {
//     // Create a user
//     $user = User::factory()->create();

//     // Act as the created user
//     $this->actingAs($user);

//     // Send a POST request with missing name
//     $response = $this->postJson(route('server.create'), [
//         'description' => 'This is a test server.',
//         'icon' => 'test-icon.png',
//     ]);

//     // Assert the response status is 422 (Unprocessable Entity)
//     $response->assertStatus(422);

//     // Assert the error message for the name field
//     $response->assertJsonValidationErrors(['name']);



// });

// // Test for user can be added to a server
// test('user can be added to a server', function () {
//     // Create a user
//     $user = User::factory()->create();

//     // Act as the created user
//     $this->actingAs($user);

//     // Create a server
//     $serverData = [
//         'name' => 'Test Server',
//         'description' => 'This is a test server.',
//     ];
//     $response = $this->postJson(route('server.create'), $serverData);
//     $serverId = $response->json('id');

//     // Add the user to the server
//     $response = $this->postJson(route('server.addUser', $serverId), [
//         'user_id' => $user->id,
//     ]);

//     // Assert the user was added to the server
//     $this->assertTrue(Server::find($serverId)->users()->where('users.id', $user->id)->exists());
//     $response->assertStatus(200);
//     $response->assertJson(['message' => 'User added to server successfully.']);
// });

// // Test for user can be removed from a server
// // Test for user can be removed from a server
// test('user can be removed from a server', function () {
//     // Create a user
//     $user = User::factory()->create();

//     // Act as the created user
//     $this->actingAs($user);

//     // Create a server
//     $serverData = [
//         'name' => 'Test Server',
//         'description' => 'This is a test server.',
//     ];
//     $response = $this->postJson(route('server.create'), $serverData);
//     $serverId = $response->json('id');

//     // Add the user to the server
//     $response = $this->postJson(route('server.addUser', $serverId), [
//         'user_id' => $user->id,
//     ]);

//     // Assert the user was added to the server
//     $this->assertTrue(Server::find($serverId)->users()->where('users.id', $user->id)->exists(), 'User should exist in the server after being added.');

//     // Now remove the user from the server
//     $response = $this->postJson(route('server.removeUser', $serverId), [
//         'user_id' => $user->id,
//     ]);

//     // Assert the user was removed from the server
//     $this->assertFalse(Server::find($serverId)->users()->where('users.id', $user->id)->exists(), 'User should not exist in the server after removal.');
//     $response->assertStatus(200);
//     $response->assertJson(['message' => 'User removed from server successfully.']);
// });
