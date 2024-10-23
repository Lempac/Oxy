<?php
namespace App\Events\Servers;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class ServerCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $icon
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
            new PrivateChannel('servers.' . $this->id),
        ];
    }
}
