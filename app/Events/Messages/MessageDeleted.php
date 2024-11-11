<?php

namespace App\Events\Messages;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageDeleted implements ShouldBroadcast
{
    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('messages.'.$this->message->channel_id);
    }

    public function broadcastAs(): string
    {
        return 'MessageDeleted';
    }
}
