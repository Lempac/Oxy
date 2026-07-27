<?php

namespace App\Enums;

enum UserStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Idle = 'idle';
    case Invisible = 'invisible';
    case DoNotDisturb = 'do_not_disturb';

    /**
     * Get allowed next statuses for state transitions.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Offline => [self::Online, self::Invisible],
            self::Online => [self::Idle, self::DoNotDisturb, self::Invisible, self::Offline],
            self::Idle => [self::Online, self::DoNotDisturb, self::Invisible, self::Offline],
            self::DoNotDisturb => [self::Online, self::Idle, self::Invisible, self::Offline],
            self::Invisible => [self::Online, self::Idle, self::DoNotDisturb, self::Offline],
        };
    }

    /**
     * Check if transition to target status is valid.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
