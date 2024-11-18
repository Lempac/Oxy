<?php

namespace App\Events\Servers;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerLeft implements ShouldBroadcast
{
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
        return 'ServerLeft';
    }
}
