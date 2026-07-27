<?php

namespace App\Events\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public UserStatus $oldStatus,
        public UserStatus $newStatus
    ) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('presence');
    }

    public function broadcastAs(): string
    {
        return 'UserStatusUpdated';
    }
}
