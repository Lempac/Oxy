<?php

namespace App\Events\Roles;

use App\Models\Role;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Role $role
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('roles.'.$this->role->server->id);
    }

    public function broadcastAs(): string
    {
        return 'RoleDeleted';
    }
}
