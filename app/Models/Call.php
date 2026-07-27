<?php

namespace App\Models;

use App\Enums\VoiceCallStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => VoiceCallStatus::class,
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Transition the call status using the VoiceCallStatus state machine.
     */
    public function transitionStatusTo(VoiceCallStatus $newStatus): bool
    {
        $currentStatus = $this->status ?? VoiceCallStatus::Idle;

        if ($currentStatus === $newStatus) {
            return false;
        }

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Invalid call status transition from '{$currentStatus->value}' to '{$newStatus->value}'."
            );
        }

        $this->status = $newStatus;
        if ($this->exists) {
            $this->save();
        }

        return true;
    }
}
