<?php

namespace App\Events\Voices;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $serverId,
        public string $channelId,
        public array $user,
        public string $action
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channels.'.$this->serverId);
    }

    public function broadcastAs(): string
    {
        return 'VoiceStateChanged';
    }
}
