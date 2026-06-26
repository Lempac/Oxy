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
use Illuminate\Support\Str;

class Channel extends Model
{
    use HasFactory;

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

    protected $appends = ['route_key'];

    public function getRouteKey()
    {
        return $this->slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getRouteKeyAttribute()
    {
        return $this->slug;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $serverParam = request()->route('server');
        
        $serverId = null;
        if ($serverParam instanceof \App\Models\Server) {
            $serverId = $serverParam->id;
        } elseif (is_string($serverParam)) {
            $server = \App\Models\Server::where('slug', $serverParam)->first();
            if ($server) {
                $serverId = $server->id;
            }
        }

        return $this->where('slug', $value)
            ->when($serverId, function ($query, $serverId) {
                return $query->where('server_id', $serverId);
            })
            ->firstOrFail();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($channel) {
            if (empty($channel->slug)) {
                $slug = Str::slug($channel->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('server_id', $channel->server_id)->where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $channel->slug = $slug;
            }
        });
    }

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
