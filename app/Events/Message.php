<?php



namespace App\Events;

use App\Models\Message as MessageModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Message implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $text,
        public int $userId,
        public int $channelId
    ) {
        
        MessageModel::create([
            'type' => 'text',
            'data' => $text,
            'user_id' => $userId,
            'channel_id' => $channelId,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ChannelName'),
        ];
    }
}
