<?php

namespace App\Events\Messages;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

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
