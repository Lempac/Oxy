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
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'slug',
    ];

    protected $dispatchesEvents = [
        'created' => ServerCreated::class,
        'updated' => ServerEdited::class,
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
        return $this->where('slug', $value)->firstOrFail();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($server) {
            if (empty($server->slug)) {
                $slug = Str::slug($server->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $server->slug = $slug;
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'server_user')
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

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    protected static function newFactory(): ServerFactory
    {
        return ServerFactory::new()->afterCreating(function ($server) {
            $role = Role::create([
                'name' => 'Owner',
                'color' => '#ffffff',
                'importance' => 0,
                'server_id' => $server->id,
                'guard_name' => 'web',
            ]);

            $permissions = Permission::pluck('name')->toArray();
            $role->syncPermissions($permissions);
        });
    }
}
