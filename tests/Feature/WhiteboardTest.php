<?php

namespace Tests\Feature;

use App\Enums\ChannelType;
use App\Models\Server;
use App\Models\User;
use App\Models\Whiteboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhiteboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_whiteboard_channel_can_be_accessed(): void
    {
        $user = User::factory()->create();
        $server = Server::factory()->create();
        $server->users()->attach($user);
        $channel = $server->channels()->create([
            'name' => 'General Whiteboard',
            'type' => ChannelType::Whiteboard,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/home/{$server->id}/whiteboard/{$channel->id}");

        $response->assertOk();
    }

    public function test_whiteboard_state_can_be_saved(): void
    {
        $user = User::factory()->create();
        $server = Server::factory()->create();
        $server->users()->attach($user);
        $channel = $server->channels()->create([
            'name' => 'General Whiteboard',
            'type' => ChannelType::Whiteboard,
        ]);
        $whiteboard = Whiteboard::create(['channel_id' => $channel->id]);

        $response = $this
            ->actingAs($user)
            ->post("/whiteboard/{$whiteboard->id}/save", [
                'state' => '{"shapes": {}}',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals('{"shapes": {}}', $whiteboard->fresh()->state);
    }

    public function test_non_member_cannot_save_whiteboard_state(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $server = Server::factory()->create();
        $server->users()->attach($user); // only $user is a member
        $channel = $server->channels()->create([
            'name' => 'General Whiteboard',
            'type' => ChannelType::Whiteboard,
        ]);
        $whiteboard = Whiteboard::create(['channel_id' => $channel->id]);

        $response = $this
            ->actingAs($otherUser)
            ->post("/whiteboard/{$whiteboard->id}/save", [
                'state' => '{"shapes": {}}',
            ]);

        $response->assertStatus(403);
    }
}
