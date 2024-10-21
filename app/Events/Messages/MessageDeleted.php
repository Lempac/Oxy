<?php

namespace App\Events\Messages;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $messageId,
        public int $channelId
    ) {}

    /**
     * @inheritDoc
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('messages.'.$this->channelId),
        ];
    }
}
