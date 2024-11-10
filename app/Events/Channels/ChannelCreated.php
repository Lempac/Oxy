<?php

namespace App\Events\Channels;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $serverId,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channels.'.$this->serverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChannelCreated';
    }
}
