<?php

namespace App\Models;

use App\Events\Roles\RoleCreated;
use App\Events\Roles\RoleDeleted;
use App\Events\Roles\RoleEdited;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'perms',
        'importance',
    ];

    protected $dispatchesEvents = [
        'created' => RoleCreated::class,
        'updated' => RoleEdited::class,
        'deleted' => RoleDeleted::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_server_user')
            ->withPivot('server_id')
            ->withTimestamps();
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'role_server_user')
            ->withPivot('user_id')
            ->withTimestamps();
    }
}
