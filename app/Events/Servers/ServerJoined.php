<?php

namespace App\Events\Servers;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public int $serverId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('servers.'.$this->serverId);
    }

    public function broadcastAs(): string
    {
        return 'ServerJoined';
    }
}
