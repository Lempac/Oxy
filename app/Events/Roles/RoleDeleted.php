<?php

namespace App\Events\Roles;

use App\Models\Role;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Role $role
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('roles');
    }

    public function broadcastAs(): string
    {
        return 'RoleDeleted';
    }
}
