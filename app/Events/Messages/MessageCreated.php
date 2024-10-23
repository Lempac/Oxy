<?php


namespace App\Events\Messages;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $text,
        public int $userId,
        public int $channelId
    ) {}

    public function broadcastOn()
    {
        return new Channel('messages.'.$this->channelId);
    }

    public function broadcastAs(): string
    {
        return 'MessageCreated';
    }
}
