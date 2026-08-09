<?php

namespace App\Events\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public UserStatus $oldStatus,
        public UserStatus $newStatus
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PresenceChannel('presence')];
        foreach ($this->user->servers as $server) {
            $channels[] = new PrivateChannel('servers.'.$server->id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'nickname' => $this->user->nickname,
                'status' => $this->newStatus->value,
            ],
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserStatusUpdated';
    }
}
