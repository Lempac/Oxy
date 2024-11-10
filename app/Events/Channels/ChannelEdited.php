<?php

namespace App\Events\Channels;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelEdited implements ShouldBroadcast
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
    public function broadcastOn(): array|Channel|string
    {
        return [
            new PrivateChannel('channels.'.$this->serverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChannelEdited';
    }
}
