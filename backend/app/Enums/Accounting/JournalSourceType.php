<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum JournalSourceType: string
{
    case Manual = 'manual';
    case Import = 'import';
    case Recurring = 'recurring';
    case Reversal = 'reversal';
    case System = 'system';
}
