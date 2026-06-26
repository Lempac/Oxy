<?php

namespace App\Models;

use App\Events\Roles\RoleCreated;
use App\Events\Roles\RoleDeleted;
use App\Events\Roles\RoleEdited;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'importance',
        'server_id',
        'guard_name',
    ];

    protected $dispatchesEvents = [
        'created' => RoleCreated::class,
        'updated' => RoleEdited::class,
        'deleted' => RoleDeleted::class,
    ];

    protected $appends = [
        'perms',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function getPermsAttribute()
    {
        return $this->permissions->pluck('name')->toArray();
    }
}
