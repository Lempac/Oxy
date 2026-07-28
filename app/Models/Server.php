<?php

namespace App\Models;

use App\Events\Servers\ServerCreated;
use App\Events\Servers\ServerEdited;
use Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class Server extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'slug',
        'default_role_id',
        'enable_whiteboard',
    ];

    protected $casts = [
        'enable_whiteboard' => 'boolean',
    ];

    protected $attributes = [
        'enable_whiteboard' => true,
    ];

    protected $dispatchesEvents = [
        'created' => ServerCreated::class,
        'updated' => ServerEdited::class,
    ];

    protected $appends = ['route_key'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($server) {
            if (empty($server->slug)) {
                $slug = Str::slug($server->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug.'-'.$count;
                    $count++;
                }
                $server->slug = $slug;
            }
        });
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

    public function getRouteKey()
    {
        return $this->slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteKeyAttribute()
    {
        return $this->slug;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)->orWhere('id', $value)->firstOrFail();
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

    public function defaultRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'default_role_id');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(ServerInvite::class);
    }

    public function getInviteCode(): string
    {
        $invite = $this->invites()
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')->orWhereColumn('uses', '<', 'max_uses');
            })
            ->latest()
            ->first();

        if (! $invite) {
            $invite = $this->invites()->create([
                'code' => ServerInvite::generateCode(),
            ]);
        }

        return $invite->code;
    }

    public function assignDefaultRole(User $user): void
    {
        if ($this->default_role_id) {
            $defaultRole = Role::where('id', $this->default_role_id)
                ->where('server_id', $this->id)
                ->first();
            if ($defaultRole) {
                setPermissionsTeamId($this->id);
                $user->assignRole($defaultRole);
            }
        }
    }
}
