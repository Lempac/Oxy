<?php

namespace App\Models;

use App\Enums\Theme;
use App\Enums\UserStatus;
use App\Events\Users\UserStatusUpdated;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname',
        'icon',
        'password',
        'status',
        'light_theme',
        'dark_theme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'server_user')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function allRoles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotRole
        );
    }

    public function connections(): HasMany
    {
        return $this->hasMany(UserAuthConnection::class);
    }

    /**
     * Transition the user to a new status using the UserStatus state machine.
     */
    public function transitionStatusTo(UserStatus $newStatus): bool
    {
        $currentStatus = $this->status ?? UserStatus::Offline;

        if ($currentStatus === $newStatus) {
            return false;
        }

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Invalid status transition from '{$currentStatus->value}' to '{$newStatus->value}'."
            );
        }

        $oldStatus = $currentStatus;
        $this->status = $newStatus;

        if ($this->exists) {
            $this->save();
        }

        UserStatusUpdated::dispatch($this, $oldStatus, $newStatus);

        return true;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => UserStatus::class,
            'light_theme' => Theme::class,
            'dark_theme' => Theme::class,
        ];
    }
}
