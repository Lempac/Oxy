<?php

namespace App\Events\Roles;

use App\Models\Role;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Role $role
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        $this->role->server = $this->role->load('server');

        return new PrivateChannel('roles.'.$this->role->server->id);
    }

    public function broadcastAs(): string
    {
        return 'RoleCreated';
    }
}
