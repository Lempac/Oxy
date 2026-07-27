<?php

namespace App\Enums;

enum VoiceParticipantState: string
{
    case Disconnected = 'disconnected';
    case Joining = 'joining';
    case Connected = 'connected';
    case Muted = 'muted';
    case Deafened = 'deafened';
    case Leaving = 'leaving';

    /**
     * Get allowed target states.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Disconnected => [self::Joining],
            self::Joining => [self::Connected, self::Disconnected],
            self::Connected => [self::Muted, self::Deafened, self::Leaving, self::Disconnected],
            self::Muted => [self::Connected, self::Deafened, self::Leaving, self::Disconnected],
            self::Deafened => [self::Connected, self::Muted, self::Leaving, self::Disconnected],
            self::Leaving => [self::Disconnected],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
