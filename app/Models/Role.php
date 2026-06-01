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

    private function getPermsMask(array|PermsType|int $permsType): int
    {
        return is_array($permsType)
            ? array_reduce(array_column($permsType, 'value'), fn (?int $a, int $b) => ($a ?? 0) | $b, 0)
            : (is_numeric($permsType) ? (int) $permsType : $permsType->value);
    }

    public function hasPerms(array|PermsType|int $permsType): bool
    {
        $mask = $this->getPermsMask($permsType);

        return ($this->perms & $mask) === $mask;
    }

    public function hasAnyPerms(array|PermsType|int $permsType): bool
    {
        return ($this->perms & $this->getPermsMask($permsType)) !== 0;
    }

    public function addPerms(array|PermsType|int $permsType): void
    {
        $this->perms |= $this->getPermsMask($permsType);
        $this->save();
    }

    public function removePerms(array|PermsType|int $permsType): void
    {
        $this->perms &= ~$this->getPermsMask($permsType);
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
