<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Events\Messages\MessageCreated;
use App\Events\Messages\MessageDeleted;
use App\Events\Messages\MessageEdited;
use Closure;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Message extends Model
{
    use HasFactory, HasUuids;

    /**
     * @returns User
     */
    public ?Closure $sender;

    protected $fillable = [
        'status',
        'content',
        'user_id',
        'channel_id',
    ];

    protected $with = [
        'attachments',
    ];

    protected $dispatchesEvents = [
        'created' => MessageCreated::class,
        'updated' => MessageEdited::class,
        'deleted' => MessageDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'status' => MessageStatus::class,
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Transition message status using the MessageStatus state machine.
     */
    public function transitionStatusTo(MessageStatus $newStatus): bool
    {
        $currentStatus = $this->status ?? MessageStatus::Sent;

        if ($currentStatus === $newStatus) {
            return false;
        }

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Invalid message status transition from '{$currentStatus->value}' to '{$newStatus->value}'."
            );
        }

        $this->status = $newStatus;
        if ($this->exists) {
            $this->save();
        }

        return true;
    }

    protected static function booted(): void
    {
        static::deleting(function (Message $message) {
            $message->attachments->each->delete();
        });
    }
}
