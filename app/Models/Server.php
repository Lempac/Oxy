<?php

namespace App\Models;

use App\Events\Servers\ServerCreated;
use App\Events\Servers\ServerEdited;
use Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
    ];

    protected $dispatchesEvents = [
        'created' => ServerCreated::class,
        'updated' => ServerEdited::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_server_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function board(): HasOne
    {
        return $this->hasOne(Board::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_server_user')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    protected static function newFactory(): ServerFactory
    {
        return ServerFactory::new()->hasRoles(1, [
            'name' => 'Owner',
            'color' => '#ffffff',
            'perms' => PHP_INT_MAX,
            'importance' => 0,
        ]);
    }
}
