<?php

namespace App\Events\Whiteboards;

use App\Models\Whiteboard;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardStateUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Whiteboard $whiteboard
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('whiteboards.'.$this->whiteboard->channel_id);
    }

    public function broadcastAs(): string
    {
        return 'WhiteboardStateUpdated';
    }
}
