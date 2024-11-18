<?php

namespace App\Events\Channels;

use App\Models\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Channel $channel
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channels.'.$this->channel->server_id);
    }

    public function broadcastAs(): string
    {
        return 'ChannelCreated';
    }
}
