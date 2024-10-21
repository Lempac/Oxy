<?php

namespace App\Events\Servers;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class ServerEdited implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $name;
    public ?string $description;
    public ?string $icon;

    public function __construct(string $name,
    string $description,
    string $icon
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
            new PrivateChannel('servers.' . $this->name),
        ];
    }
}
