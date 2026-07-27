<?php

namespace Tests\Feature;

use App\Enums\ChannelType;
use App\Enums\WhiteboardSyncState;
use App\Events\Whiteboards\WhiteboardStateUpdated;
use App\Models\Server;
use App\Models\User;
use App\Models\Whiteboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WhiteboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_whiteboard_channel_can_be_accessed(): void
    {
        $user = User::factory()->create();
        $server = Server::factory()->create(['slug' => 'test-server-access']);
        $server->users()->attach($user);
        $channel = $server->channels()->create([
            'name' => 'General Whiteboard',
            'slug' => 'general-whiteboard-1',
            'type' => ChannelType::Whiteboard,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/home/{$server->slug}/whiteboard/{$channel->slug}");

        $response->assertOk();
    }

    public function test_whiteboard_state_can_be_saved(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $server = Server::factory()->create(['slug' => 'test-server-save']);
        $server->users()->attach($user);
        $channel = $server->channels()->create([
            'name' => 'General Whiteboard',
            'slug' => 'general-whiteboard-2',
            'type' => ChannelType::Whiteboard,
        ]);
        $whiteboard = Whiteboard::create([
            'channel_id' => $channel->id,
            'sync_status' => WhiteboardSyncState::Dirty,
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/whiteboard/{$whiteboard->id}/save", [
                'state' => '{"shapes": {}}',
            ]);

        $response->assertJson([
            'success' => true,
            'sync_status' => 'synced',
        ]);
        $this->assertEquals('{"shapes": {}}', $whiteboard->fresh()->state);
        $this->assertEquals(WhiteboardSyncState::Synced, $whiteboard->fresh()->sync_status);

        Event::assertDispatched(WhiteboardStateUpdated::class, function ($event) use ($whiteboard) {
            return $event->whiteboard->id === $whiteboard->id;
        });
    }

    public function test_non_member_cannot_save_whiteboard_state(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $server = Server::factory()->create(['slug' => 'test-server-non-member']);
        $server->users()->attach($user); // only $user is a member
        $channel = $server->channels()->create([
            'name' => 'General Whiteboard',
            'slug' => 'general-whiteboard-3',
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
