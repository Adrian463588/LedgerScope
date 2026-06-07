<?php

declare(strict_types=1);

namespace App\Enums\Common;

enum EvidenceStatus: string
{
    case Pending = 'pending';
    case Received = 'received';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
