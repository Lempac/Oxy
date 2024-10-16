<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ServerControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_server()
{
    // Create a user
    $user = User::factory()->create();

    // Act as the created user
    $this->actingAs($user);

    // Define the server data
    $serverData = [
        'name' => 'Test Server',
        'description' => 'This is a test server.',
        'icon' => 'test-icon.png',
    ];

    // Send a POST request to create a server
    $response = $this->postJson(route('server.create'), $serverData);

    // Assert the response status is 201 (Created)
    $response->assertStatus(201);

    // Assert the server was created in the database
    $this->assertDatabaseHas('servers', [
        'id' => $response->json('id'),
        'name' => 'Test Server',
        'description' => 'This is a test server.',
        'icon' => 'test-icon.png',
    ]);

    // Assert the user is attached to the server through the pivot table
    $server = Server::find($response->json('id'));
    $this->assertTrue($server->users()->where('users.id', $user->id)->exists());
}


    /** @test */
    public function it_requires_a_name_to_create_a_server()
    {
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
    }
}
