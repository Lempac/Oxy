<?php

namespace App\Models;

use App\Enums\WhiteboardSyncState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Whiteboard extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'state', 'sync_status'];

    protected function casts(): array
    {
        return [
            'sync_status' => WhiteboardSyncState::class,
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Transition whiteboard sync status using the WhiteboardSyncState state machine.
     */
    public function transitionSyncStatusTo(WhiteboardSyncState $newStatus): bool
    {
        $currentStatus = $this->sync_status ?? WhiteboardSyncState::Synced;

        if ($currentStatus === $newStatus) {
            return false;
        }

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Invalid whiteboard sync status transition from '{$currentStatus->value}' to '{$newStatus->value}'."
            );
        }

        $this->sync_status = $newStatus;
        if ($this->exists) {
            $this->save();
        }

        return true;
    }
}
