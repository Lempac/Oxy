<?php

namespace App\Enums;

enum MessageStatus: string
{
    case Sending = 'sending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Edited = 'edited';
    case Deleted = 'deleted';
    case Failed = 'failed';

    /**
     * Get allowed target message statuses.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Sending => [self::Sent, self::Failed],
            self::Sent => [self::Delivered, self::Edited, self::Deleted],
            self::Delivered => [self::Edited, self::Deleted],
            self::Edited => [self::Deleted],
            self::Failed => [self::Sending, self::Deleted],
            self::Deleted => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
