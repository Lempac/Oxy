<?php

namespace App\Events\Messages;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    public int $messageId;
    public int $channelId;
    public int $serverId;
    public int $userId;

    public function __construct(
        int $messageId,
        int $channelId,
        int $serverId,
        int $userId
        )
    {}

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
