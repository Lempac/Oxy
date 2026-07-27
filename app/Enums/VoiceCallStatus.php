<?php

namespace App\Enums;

enum VoiceCallStatus: string
{
    case Idle = 'idle';
    case Ringing = 'ringing';
    case Connecting = 'connecting';
    case Active = 'active';
    case Disconnected = 'disconnected';
    case Ended = 'ended';

    /**
     * Get allowed target statuses from current status.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Idle => [self::Ringing, self::Connecting, self::Ended],
            self::Ringing => [self::Connecting, self::Disconnected, self::Ended],
            self::Connecting => [self::Active, self::Disconnected, self::Ended],
            self::Active => [self::Disconnected, self::Ended],
            self::Disconnected => [self::Connecting, self::Ended, self::Idle],
            self::Ended => [self::Idle, self::Connecting],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
