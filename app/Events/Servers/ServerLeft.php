<?php

namespace App\Events\Servers;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class ServerLeft implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $userId;
    public $serverId;

    public function __construct(
        int $userId,
         int $serverId
         )
    {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('servers.' . $this->serverId),
        ];
    }
}
