<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): MorphToMany
    {
        return $this->morphToMany(User::class, "users");
    }

    public function channels() : HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function board() : HasOne
    {
        return $this->hasOne(Board::class);
    }
}
