<?php

declare(strict_types=1);

namespace App\Enums\Common;

enum InvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Expired => 'Expired',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
