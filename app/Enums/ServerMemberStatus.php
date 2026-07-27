<?php

namespace App\Enums;

enum ServerMemberStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Muted = 'muted';
    case Suspended = 'suspended';
    case Left = 'left';

    /**
     * Get allowed target member statuses.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Invited => [self::Active, self::Left],
            self::Active => [self::Muted, self::Suspended, self::Left],
            self::Muted => [self::Active, self::Suspended, self::Left],
            self::Suspended => [self::Active, self::Left],
            self::Left => [self::Active, self::Invited],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
