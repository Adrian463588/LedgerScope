<?php

declare(strict_types=1);

namespace App\Enums\Audit;

enum EngagementStatus: string
{
    case Planning = 'planning';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
