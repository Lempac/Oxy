<?php

namespace App\Enums;

enum WhiteboardSyncState: string
{
    case Uninitialized = 'uninitialized';
    case Synced = 'synced';
    case Dirty = 'dirty';
    case Saving = 'saving';
    case SaveFailed = 'save_failed';

    /**
     * Get allowed target whiteboard sync states.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Uninitialized => [self::Synced, self::Dirty],
            self::Synced => [self::Dirty],
            self::Dirty => [self::Saving],
            self::Saving => [self::Synced, self::SaveFailed],
            self::SaveFailed => [self::Saving, self::Dirty],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
