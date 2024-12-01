<?php

namespace App\Events\Voices;

use App\Models\Channel as ModelChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Status
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ModelChannel $channel, public $audio) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('voices.'.$this->channel->id);
    }

    public function broadcastAs(): string
    {
        return 'Status';
    }
}
