<?php

namespace App\Models;

use App\Enums\PermsType;
use App\Events\Roles\RoleCreated;
use App\Events\Roles\RoleDeleted;
use App\Events\Roles\RoleEdited;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function server(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'role_server_user')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function hasPerms(array|PermsType|int $permsType): bool
    {
        return ($this->perms & (is_array($permsType) ? array_reduce(array_column($permsType, 'value'), fn (int $a, int $b) => $a | $b) : (is_numeric($permsType) ? $permsType : $permsType->value))) === $permsType;
    }

    public function hasAnyPerms(array|PermsType|int $permsType): bool
    {
        return ($this->perms & (is_array($permsType) ? array_reduce(array_column($permsType, 'value'), fn (int $a, int $b) => $a | $b) : (is_numeric($permsType) ? $permsType : $permsType->value))) !== 0;
    }

    public function addPerms(array|PermsType|int $permsType): void
    {
        $this->perms |= $this->perms & (is_array($permsType) ? array_reduce(array_column($permsType, 'value'), fn (int $a, int $b) => $a | $b) : (is_numeric($permsType) ? $permsType : $permsType->value));
        $this->save();
    }

    public function removePerms(array|PermsType|int $permsType): void
    {
        $this->perms &= $this->perms & (is_array($permsType) ? ~array_reduce(array_column($permsType, 'value'), fn (int $a, int $b) => $a | $b) : (is_numeric($permsType) ? ~$permsType : ~$permsType->value));
        $this->save();
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // Cast the specific field to string
        if (isset($array['perms'])) {
            $array['perms'] = (string) $array['perms'];
        }

        return $array;
    }
}
