<?php

namespace App\Models;

use App\Events\Channels\ChannelCreated;
use App\Events\Channels\ChannelDeleted;
use App\Events\Channels\ChannelEdited;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'server_id',
    ];

    protected $dispatchesEvents = [
        'created' => ChannelCreated::class,
        'updated' => ChannelEdited::class,
        'deleted' => ChannelDeleted::class,
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
