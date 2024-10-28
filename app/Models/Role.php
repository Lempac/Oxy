<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'perms',
        'importance',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_server_user')
            ->withPivot('server_id')
            ->withTimestamps();
    }

    public function servers(): BelongsTo
    {
        return $this->belongsToMany(Server::class, 'role_server_user')
            ->withPivot('user_id')
            ->withTimestamps();
    }

}



