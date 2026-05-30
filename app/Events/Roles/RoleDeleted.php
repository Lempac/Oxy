<?php

namespace App\Events\Roles;

use App\Models\Role;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class RoleDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Role $role
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->role->server as $server) {
            $channels[] = new PrivateChannel('roles.'.$server->id);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'RoleDeleted';
    }
}
