<?php

namespace App\Models;

use App\Events\Channels\ChannelCreated;
use App\Events\Channels\ChannelDeleted;
use App\Events\Channels\ChannelEdited;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Channel extends Model
{
    public function getRouteKeyName() { return 'slug'; }
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($channel) {
            if (!$channel->slug) {
                $channel->slug = \Illuminate\Support\Str::slug($channel->name) . '-' . $channel->id;
                $channel->save();
            }
        });
    }


    protected $fillable = [
        'name',
        'type',
        'server_id',
        'slug',
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

    public function whiteboard(): HasOne
    {
        return $this->hasOne(Whiteboard::class);
    }
}
