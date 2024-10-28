<?php

namespace App\Events\Messages;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    public function __construct(
        public int $messageId,
        public int $channelId,
        public int $serverId,
        public int $userId
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function broadcastOn(): array|Channel|string
    {
        return [
            new PrivateChannel('messages.' . $this->channelId),
        ];
    }
}
