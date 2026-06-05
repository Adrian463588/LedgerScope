<?php

declare(strict_types=1);

namespace App\Enums\Audit;

enum FindingSeverity: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Info = 'informational';
}
