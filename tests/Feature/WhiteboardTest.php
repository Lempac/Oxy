<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Whiteboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhiteboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_whiteboard_index_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/whiteboard');

        $response->assertOk();
    }

    public function test_whiteboard_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/whiteboard', [
                'name' => 'Test Whiteboard',
            ]);

        $response->assertRedirect('/whiteboard');
        $this->assertDatabaseHas('whiteboards', [
            'name' => 'Test Whiteboard',
        ]);
    }

    public function test_whiteboard_can_be_updated(): void
    {
        $user = User::factory()->create();
        $whiteboard = Whiteboard::create(['name' => 'Old Name']);

        $response = $this
            ->actingAs($user)
            ->put("/whiteboard/{$whiteboard->id}", [
                'name' => 'New Name',
            ]);

        $response->assertRedirect('/whiteboard');
        $this->assertEquals('New Name', $whiteboard->fresh()->name);
    }

    public function test_whiteboard_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $whiteboard = Whiteboard::create(['name' => 'To Delete']);

        $response = $this
            ->actingAs($user)
            ->delete("/whiteboard/{$whiteboard->id}");

        $response->assertRedirect('/whiteboard');
        $this->assertDatabaseMissing('whiteboards', [
            'id' => $whiteboard->id,
        ]);
    }

    public function test_whiteboard_state_can_be_saved(): void
    {
        $user = User::factory()->create();
        $whiteboard = Whiteboard::create(['name' => 'Test Whiteboard']);

        $response = $this
            ->actingAs($user)
            ->post("/whiteboard/{$whiteboard->id}/save", [
                'state' => '{"lines": []}',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals('{"lines": []}', $whiteboard->fresh()->state);
    }
}
