<?php

declare(strict_types=1);

namespace App\Enums\Audit;

enum ReviewNoteStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case Reopened = 'reopened';
    case Closed = 'closed';
}
