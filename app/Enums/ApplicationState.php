<?php

namespace App\Enums;

enum ApplicationState: string
{
    case Initializing = 'initializing';
    case Unauthenticated = 'unauthenticated';
    case Authenticating = 'authenticating';
    case Ready = 'ready';
    case Reconnecting = 'reconnecting';
    case Error = 'error';

    /**
     * Get allowed target application states.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Initializing => [self::Unauthenticated, self::Authenticating, self::Ready, self::Error],
            self::Unauthenticated => [self::Authenticating, self::Error],
            self::Authenticating => [self::Ready, self::Unauthenticated, self::Error],
            self::Ready => [self::Reconnecting, self::Unauthenticated, self::Error],
            self::Reconnecting => [self::Ready, self::Unauthenticated, self::Error],
            self::Error => [self::Initializing, self::Authenticating, self::Unauthenticated],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
