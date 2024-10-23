<?php



namespace App\Events\Messages;

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

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('messages.'.$this->channelId),
        ];
    }
}
