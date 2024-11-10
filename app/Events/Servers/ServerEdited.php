<?php

namespace App\Events\Servers;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerEdited implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $id,
        public ?string $name,
        public ?string $description,
        public ?string $icon
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('servers.'.$this->name);
    }

    public function broadcastAs(): string
    {
        return 'ServerEdited';
    }
}
