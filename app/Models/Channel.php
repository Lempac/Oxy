<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rules\Enum;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function messages() : HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function calls() : HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
