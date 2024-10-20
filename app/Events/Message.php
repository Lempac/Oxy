<?php



namespace App\Events;

use App\Models\Message as MessageModel;
use App\Models\User;
use App\Models\Channel as ChannelModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Message implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $text,
        public int $userId,
        public int $channelId
    ) {

        $user = User::find($userId);
        if (!$user) {
            throw new ModelNotFoundException("User with ID {$userId} not found.");
        }


        $channel = ChannelModel::find($channelId); 
        if (!$channel) {
            throw new ModelNotFoundException("Channel with ID {$channelId} not found.");
        }

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
