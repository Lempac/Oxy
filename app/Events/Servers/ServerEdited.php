<?php

namespace App\Events\Servers;

use App\Models\Server;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerEdited implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Server $server
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('servers.'.$this->server->id);
    }

    public function broadcastAs(): string
    {
        return 'ServerEdited';
    }
}
